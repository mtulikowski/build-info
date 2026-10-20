<?php

namespace SmartGroup\BuildInfoBundle\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

final class BuildInfoGenerator
{
    public function __construct(private readonly string $projectDir)
    {
    }

    /**
     * @return array{version: string, commit: string, committed_at: string}
     */
    public function generate(): array
    {
        $commit = $this->git(['rev-parse', '--short', 'HEAD']);
        $committedAt = new \DateTimeImmutable($this->git(['show', '-s', '--format=%cI', 'HEAD']));

        $exactTag = $this->gitOptional([
            'describe',
            '--tags',
            '--exact-match',
            '--match',
            'v[0-9]*',
            'HEAD',
        ]);

        if (null !== $exactTag) {
            $version = $this->normalizeTag($exactTag);
        } else {
            $baseTag = $this->gitOptional([
                'describe',
                '--tags',
                '--abbrev=0',
                '--match',
                'v[0-9]*',
                'HEAD',
            ]);
            $version = null === $baseTag
                ? '0.0.0-dev'
                : $this->normalizeTag($baseTag).'-dev';
        }

        $info = [
            'version' => $version,
            'commit' => $commit,
            'committed_at' => $committedAt->format('Y.m.d-His'),
        ];

        (new Filesystem())->dumpFile(
            $this->projectDir.'/var/build-info.json',
            json_encode(
                $info,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
            ).PHP_EOL
        );

        return $info;
    }

    private function normalizeTag(string $tag): string
    {
        return str_starts_with($tag, 'v') ? substr($tag, 1) : $tag;
    }

    /**
     * @param list<string> $arguments
     */
    private function git(array $arguments): string
    {
        $process = new Process(['git', ...$arguments], $this->projectDir);
        $process->mustRun();

        return trim($process->getOutput());
    }

    /**
     * @param list<string> $arguments
     */
    private function gitOptional(array $arguments): ?string
    {
        $process = new Process(['git', ...$arguments], $this->projectDir);
        $process->run();
        $output = trim($process->getOutput());

        return $process->isSuccessful() && '' !== $output ? $output : null;
    }
}
