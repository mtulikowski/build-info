<?php

declare(strict_types=1);

namespace SmartGroup\BuildInfoBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class SmartGroupBuildInfoExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $container->setParameter('smart_group_build_info.path', $config['path']);
        $container->setParameter('smart_group_build_info.twig.enabled', $config['twig']['enabled']);

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        $loader->load('services.php');

        if ($config['twig']['enabled'] && class_exists(\Symfony\Bundle\TwigBundle\TwigBundle::class)) {
            $loader->load('twig.php');
        }
    }

    public function getAlias(): string
    {
        return 'build_info';
    }
}
