<?php

declare(strict_types=1);

namespace SmartGroup\BuildInfoBundle\Service;

use SmartGroup\BuildInfoBundle\Exception\BuildInfoGenerationException;
use SmartGroup\BuildInfoBundle\ValueObject\BuildInfo;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

final class BuildInfoGenerator
{
    public function __construct(
        private readonly string $path,
        private readonly string $projectDir,
    ) {
    }

    public function generate(?string $version = null, ?string $revision = null, ?string $builtAt = null): BuildInfo
    {
        $info = BuildInfo::fromValues(
            $version ?? $this->deriveVersion(),
            $revision ?? $this->gitOptional(['rev-parse', '--short', 'HEAD']) ?? 'unknown',
            $builtAt ?? (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format(\DATE_ATOM),
        );

        try {
            $contents = json_encode(
                $info->toArray(),
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
            );
            (new Filesystem())->dumpFile($this->path, $contents.PHP_EOL);
        } catch (\JsonException | IOExceptionInterface $exception) {
            throw new BuildInfoGenerationException('Unable to write build metadata.', 0, $exception);
        }

        return $info;
    }

    private function deriveVersion(): string
    {
        $exactTag = $this->gitOptional([
            'describe',
            '--tags',
            '--exact-match',
            '--match',
            'v[0-9]*',
            'HEAD',
        ]);

        if (null !== $exactTag) {
            return $this->normalizeTag($exactTag);
        }

        $baseTag = $this->gitOptional([
            'describe',
            '--tags',
            '--abbrev=0',
            '--match',
            'v[0-9]*',
            'HEAD',
        ]);

        return null === $baseTag
            ? '0.0.0-dev'
            : $this->normalizeTag($baseTag).'-dev';
    }

    private function normalizeTag(string $tag): string
    {
        return str_starts_with($tag, 'v') ? substr($tag, 1) : $tag;
    }

    /**
     * @param list<string> $arguments
     */
    private function gitOptional(array $arguments): ?string
    {
        try {
            $process = new Process(['git', ...$arguments], $this->projectDir);
            $process->run();
        } catch (\RuntimeException) {
            return null;
        }

        $output = trim($process->getOutput());

        return $process->isSuccessful() && '' !== $output ? $output : null;
    }
}
