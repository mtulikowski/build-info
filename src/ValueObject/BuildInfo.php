<?php

declare(strict_types=1);

namespace SmartGroup\BuildInfoBundle\ValueObject;

use SmartGroup\BuildInfoBundle\Exception\InvalidBuildInfoException;

final class BuildInfo
{
    private function __construct(
        public readonly string $version,
        public readonly string $revision,
        public readonly string $builtAt,
    ) {
    }

    public static function fromValues(string $version, string $revision, string $builtAt): self
    {
        if ('' === trim($version) || '' === trim($revision)) {
            throw new InvalidBuildInfoException('Build metadata values must not be empty.');
        }

        self::assertBuiltAt($builtAt);

        return new self($version, $revision, $builtAt);
    }

    public static function unknown(): self
    {
        return new self('unknown', 'unknown', 'unknown');
    }

    /**
     * @return array{version: string, revision: string, built_at: string}
     */
    public function toArray(): array
    {
        return [
            'version' => $this->version,
            'revision' => $this->revision,
            'built_at' => $this->builtAt,
        ];
    }

    private static function assertBuiltAt(string $builtAt): void
    {
        if (1 !== preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:Z|[+-]\d{2}:\d{2})$/D', $builtAt)) {
            throw new InvalidBuildInfoException('The build timestamp must be an ISO 8601 value with a timezone.');
        }

        try {
            $date = new \DateTimeImmutable($builtAt);
        } catch (\Exception $exception) {
            throw new InvalidBuildInfoException('The build timestamp is invalid.', 0, $exception);
        }

        if ($date->format('Y-m-d\TH:i:s') !== substr($builtAt, 0, 19)) {
            throw new InvalidBuildInfoException('The build timestamp is invalid.');
        }
    }
}
