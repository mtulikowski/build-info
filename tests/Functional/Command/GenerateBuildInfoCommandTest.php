<?php

declare(strict_types=1);

namespace SmartGroup\BuildInfoBundle\Tests\Functional\Command;

use PHPUnit\Framework\TestCase;
use SmartGroup\BuildInfoBundle\Command\GenerateBuildInfoCommand;
use SmartGroup\BuildInfoBundle\Service\BuildInfoGenerator;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\ApplicationTester;
use Symfony\Component\Console\Tester\CommandTester;

final class GenerateBuildInfoCommandTest extends TestCase
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

    public function testGeneratesMetadataFromCommandOptions(): void
    {
        $tester = new CommandTester($this->command());

        self::assertSame(Command::SUCCESS, $tester->execute([
            '--build-version' => '1.2.0',
            '--revision' => 'a1b2c3d',
            '--built-at' => '2026-10-20T12:34:56+00:00',
        ]));
        self::assertStringContainsString('Generated build info: 1.2.0, a1b2c3d - 2026-10-20T12:34:56+00:00', $tester->getDisplay());
        self::assertSame([
            'version' => '1.2.0',
            'revision' => 'a1b2c3d',
            'built_at' => '2026-10-20T12:34:56+00:00',
        ], $this->readGeneratedMetadata());
    }

    public function testCanBeRunThroughAConsoleApplication(): void
    {
        $application = new Application();
        $application->setAutoExit(false);
        $application->add($this->command());
        $tester = new ApplicationTester($application);

        self::assertSame(Command::SUCCESS, $tester->run([
            'command' => 'build-info:generate',
            '--build-version' => '1.2.0',
            '--revision' => 'a1b2c3d',
            '--built-at' => '2026-10-20T12:34:56+00:00',
        ]));
    }

    public function testGeneratesMetadataFromCiEnvironmentVariables(): void
    {
        $variables = [
            'BUILD_INFO_VERSION' => '1.2.0-ci',
            'BUILD_INFO_REVISION' => 'c1d2e3f',
            'BUILD_INFO_BUILT_AT' => '2026-10-20T12:34:56+00:00',
        ];
        $originalValues = [];

        try {
            foreach ($variables as $name => $value) {
                $originalValues[$name] = getenv($name);
                putenv($name.'='.$value);
            }

            $tester = new CommandTester($this->command());

            self::assertSame(Command::SUCCESS, $tester->execute([]));
            self::assertSame([
                'version' => '1.2.0-ci',
                'revision' => 'c1d2e3f',
                'built_at' => '2026-10-20T12:34:56+00:00',
            ], $this->readGeneratedMetadata());
        } finally {
            foreach ($originalValues as $name => $value) {
                putenv(false === $value ? $name : $name.'='.$value);
            }
        }
    }

    public function testReturnsFailureForInvalidMetadataWithoutExposingThePath(): void
    {
        $tester = new CommandTester($this->command());

        self::assertSame(Command::FAILURE, $tester->execute([
            '--build-version' => '1.2.0',
            '--revision' => 'a1b2c3d',
            '--built-at' => 'invalid',
        ]));
        self::assertStringContainsString('Unable to generate build metadata.', $tester->getDisplay());
        self::assertStringNotContainsString($this->path, $tester->getDisplay());
    }

    private function command(): GenerateBuildInfoCommand
    {
        return new GenerateBuildInfoCommand(new BuildInfoGenerator($this->path, $this->directory));
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
