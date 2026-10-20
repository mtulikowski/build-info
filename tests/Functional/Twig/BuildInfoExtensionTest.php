<?php

declare(strict_types=1);

namespace SmartGroup\BuildInfoBundle\Tests\Functional\Twig;

use PHPUnit\Framework\TestCase;
use SmartGroup\BuildInfoBundle\Service\BuildInfoReader;
use SmartGroup\BuildInfoBundle\Twig\BuildInfoExtension;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

final class BuildInfoExtensionTest extends TestCase
{
    private string $directory;

    private string $path;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'build-info-bundle-'.bin2hex(random_bytes(8));
        self::assertTrue(mkdir($this->directory));
        $this->path = $this->directory.DIRECTORY_SEPARATOR.'build-info.json';
    }

    protected function tearDown(): void
    {
        if (is_file($this->path)) {
            unlink($this->path);
        }

        if (is_dir($this->directory)) {
            rmdir($this->directory);
        }
    }

    public function testExposesRawBuildMetadataThroughTheBuildInfoFunction(): void
    {
        self::assertNotFalse(file_put_contents(
            $this->path,
            '{"version":"1.2.0","revision":"a1b2c3d","built_at":"2026-10-20T12:34:56+00:00"}',
        ));

        $twig = new Environment(new ArrayLoader([
            'metadata' => '{{ build_info().version }}|{{ build_info().revision }}|{{ build_info().built_at }}',
        ]));
        $twig->addExtension(new BuildInfoExtension(new BuildInfoReader($this->path)));

        self::assertSame('1.2.0|a1b2c3d|2026-10-20T12:34:56+00:00', $twig->render('metadata'));
    }
}
