<?php

declare(strict_types=1);

namespace SmartGroup\BuildInfoBundle\Tests\Integration\DependencyInjection;

use PHPUnit\Framework\TestCase;
use SmartGroup\BuildInfoBundle\Command\GenerateBuildInfoCommand;
use SmartGroup\BuildInfoBundle\DependencyInjection\SmartGroupBuildInfoExtension;
use SmartGroup\BuildInfoBundle\Service\BuildInfoGenerator;
use SmartGroup\BuildInfoBundle\Service\BuildInfoReader;
use SmartGroup\BuildInfoBundle\Twig\BuildInfoExtension;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Process\Process;

final class SmartGroupBuildInfoExtensionTest extends TestCase
{
    public function testLoadsConfiguredPathAndCoreServices(): void
    {
        $container = $this->loadContainer([
            'path' => '/application/var/build-info.json',
        ]);

        self::assertSame('/application/var/build-info.json', $container->getParameter('smart_group_build_info.path'));
        self::assertTrue($container->hasDefinition(BuildInfoReader::class));
        self::assertTrue($container->hasDefinition(BuildInfoGenerator::class));
        self::assertTrue($container->hasDefinition(GenerateBuildInfoCommand::class));
    }

    public function testDoesNotRegisterTwigExtensionWhenTwigIsDisabled(): void
    {
        $container = $this->loadContainer([
            'twig' => ['enabled' => false],
        ]);

        self::assertFalse($container->hasDefinition(BuildInfoExtension::class));
    }

    public function testRegistersTwigExtensionWhenTwigIsEnabledAndAvailable(): void
    {
        self::assertTrue(class_exists(TwigBundle::class));

        $container = $this->loadContainer([
            'twig' => ['enabled' => true],
        ]);

        self::assertTrue($container->hasDefinition(BuildInfoExtension::class));
    }

    public function testDoesNotRegisterTwigExtensionWhenTwigBundleIsUnavailable(): void
    {
        $script = <<<'PHP'
$loader = require $argv[1].'/vendor/autoload.php';
$loader->setPsr4('Symfony\\Bundle\\TwigBundle\\', []);

$container = new \Symfony\Component\DependencyInjection\ContainerBuilder();
$container->setParameter('kernel.project_dir', '/application');
(new \SmartGroup\BuildInfoBundle\DependencyInjection\SmartGroupBuildInfoExtension())->load([
    ['twig' => ['enabled' => true]],
], $container);

exit($container->hasDefinition(\SmartGroup\BuildInfoBundle\Twig\BuildInfoExtension::class) ? 1 : 0);
PHP;

        $process = new Process([PHP_BINARY, '-r', $script, dirname(__DIR__, 3)]);
        $process->run();

        self::assertTrue($process->isSuccessful(), $process->getErrorOutput());
    }

    /**
     * @param array<string, mixed> $config
     */
    private function loadContainer(array $config): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.project_dir', '/application');

        (new SmartGroupBuildInfoExtension())->load([$config], $container);

        return $container;
    }
}
