<?php

declare(strict_types=1);

namespace SmartGroup\BuildInfoBundle\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use SmartGroup\BuildInfoBundle\Service\BuildInfoGenerator;

final class BuildInfoGeneratorTest extends TestCase
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

    public function testGeneratesMetadataFromProvidedValues(): void
    {
        $info = (new BuildInfoGenerator($this->path, $this->directory))->generate(
            '1.2.0',
            'a1b2c3d',
            '2026-10-20T12:34:56+00:00',
        );

        self::assertSame([
            'version' => '1.2.0',
            'revision' => 'a1b2c3d',
            'built_at' => '2026-10-20T12:34:56+00:00',
        ], $info->toArray());
        self::assertSame($info->toArray(), $this->readGeneratedMetadata());
    }

    public function testGeneratesFallbackMetadataWhenGitMetadataIsUnavailable(): void
    {
        $info = (new BuildInfoGenerator($this->path, $this->directory))->generate();

        self::assertSame('0.0.0-dev', $info->version);
        self::assertSame('unknown', $info->revision);
        self::assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+00:00$/', $info->builtAt);
        self::assertSame($info->toArray(), $this->readGeneratedMetadata());
    }

    public function testGeneratesFallbackMetadataWhenGitIsNotInstalled(): void
    {
        $path = getenv('PATH');

        try {
            putenv('PATH=');

            $info = (new BuildInfoGenerator($this->path, $this->directory))->generate();

            self::assertSame('0.0.0-dev', $info->version);
            self::assertSame('unknown', $info->revision);
        } finally {
            putenv(false === $path ? 'PATH' : 'PATH='.$path);
        }
    }

    /**
     * @return array{version: string, revision: string, built_at: string}
     */
    private function readGeneratedMetadata(): array
    {
        $contents = file_get_contents($this->path);
        self::assertNotFalse($contents);

        /** @var array{version: string, revision: string, built_at: string} $metadata */
        $metadata = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);

        return $metadata;
    }
}
