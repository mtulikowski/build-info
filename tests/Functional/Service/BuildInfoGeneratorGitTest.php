<?php

declare(strict_types=1);

namespace SmartGroup\BuildInfoBundle\Tests\Functional\Service;

use PHPUnit\Framework\TestCase;
use SmartGroup\BuildInfoBundle\Service\BuildInfoGenerator;
use Symfony\Component\Process\Process;

final class BuildInfoGeneratorGitTest extends TestCase
{
    private string $directory;

    private string $path;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'build-info-bundle-'.bin2hex(random_bytes(8));
        self::assertTrue(mkdir($this->directory));
        $this->path = $this->directory.DIRECTORY_SEPARATOR.'build-info.json';

        $git = new Process(['git', '--version']);
        $git->run();
        if (!$git->isSuccessful()) {
            self::markTestSkipped('Git is not available.');
        }
    }

    protected function tearDown(): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());

                continue;
            }

            chmod($item->getPathname(), 0666);
            unlink($item->getPathname());
        }

        rmdir($this->directory);
    }

    public function testGeneratesVersionFromAnExactGitTag(): void
    {
        $this->createRepository();
        $this->runGit(['tag', 'v1.2.3']);

        $info = (new BuildInfoGenerator($this->path, $this->directory))->generate();

        self::assertSame('1.2.3', $info->version);
        self::assertMatchesRegularExpression('/^[0-9a-f]{7,}$/', $info->revision);
    }

    public function testGeneratesDevelopmentVersionWhenNoGitTagExists(): void
    {
        $this->createRepository();

        $info = (new BuildInfoGenerator($this->path, $this->directory))->generate();

        self::assertSame('0.0.0-dev', $info->version);
        self::assertMatchesRegularExpression('/^[0-9a-f]{7,}$/', $info->revision);
    }

    private function createRepository(): void
    {
        $this->runGit(['init']);
        self::assertNotFalse(file_put_contents($this->directory.DIRECTORY_SEPARATOR.'metadata.txt', 'metadata'));
        $this->runGit(['add', 'metadata.txt']);
        $this->runGit([
            '-c',
            'user.email=tests@example.invalid',
            '-c',
            'user.name=Build Info Bundle Tests',
            'commit',
            '-m',
            'Initial build metadata commit',
        ]);
    }

    /**
     * @param list<string> $arguments
     */
    private function runGit(array $arguments): void
    {
        (new Process(['git', ...$arguments], $this->directory))->mustRun();
    }
}
