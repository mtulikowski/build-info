<?php

declare(strict_types=1);

namespace SmartGroup\BuildInfoBundle\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use SmartGroup\BuildInfoBundle\Service\BuildInfoReader;

final class BuildInfoReaderTest extends TestCase
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

    public function testReadsValidMetadata(): void
    {
        $this->write('{"version":"1.2.0","revision":"a1b2c3d","built_at":"2026-10-20T12:34:56+00:00"}');

        self::assertSame([
            'version' => '1.2.0',
            'revision' => 'a1b2c3d',
            'built_at' => '2026-10-20T12:34:56+00:00',
        ], (new BuildInfoReader($this->path))->read()->toArray());
    }

    public function testReturnsUnknownMetadataWhenFileIsMissing(): void
    {
        self::assertSame($this->unknownMetadata(), (new BuildInfoReader($this->path))->read()->toArray());
    }

    public function testReturnsUnknownMetadataForInvalidJson(): void
    {
        $this->write('{');

        self::assertSame($this->unknownMetadata(), (new BuildInfoReader($this->path))->read()->toArray());
    }

    public function testReturnsUnknownMetadataWhenARequiredFieldIsMissing(): void
    {
        $this->write('{"version":"1.2.0","revision":"a1b2c3d"}');

        self::assertSame($this->unknownMetadata(), (new BuildInfoReader($this->path))->read()->toArray());
    }

    public function testReturnsUnknownMetadataWhenAFieldHasAnInvalidType(): void
    {
        $this->write('{"version":"1.2.0","revision":123,"built_at":"2026-10-20T12:34:56+00:00"}');

        self::assertSame($this->unknownMetadata(), (new BuildInfoReader($this->path))->read()->toArray());
    }

    public function testReturnsUnknownMetadataWhenTheTimestampIsInvalid(): void
    {
        $this->write('{"version":"1.2.0","revision":"a1b2c3d","built_at":"not-a-date"}');

        self::assertSame($this->unknownMetadata(), (new BuildInfoReader($this->path))->read()->toArray());
    }

    private function write(string $contents): void
    {
        self::assertNotFalse(file_put_contents($this->path, $contents));
    }

    /**
     * @return array{version: string, revision: string, built_at: string}
     */
    private function unknownMetadata(): array
    {
        return [
            'version' => 'unknown',
            'revision' => 'unknown',
            'built_at' => 'unknown',
        ];
    }
}
