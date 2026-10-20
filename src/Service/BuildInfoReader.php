<?php

declare(strict_types=1);

namespace SmartGroup\BuildInfoBundle\Service;

use SmartGroup\BuildInfoBundle\Exception\InvalidBuildInfoException;
use SmartGroup\BuildInfoBundle\ValueObject\BuildInfo;

final class BuildInfoReader
{
    public function __construct(private readonly string $path)
    {
    }

    public function read(): BuildInfo
    {
        if (!is_file($this->path) || !is_readable($this->path)) {
            return BuildInfo::unknown();
        }

        $contents = file_get_contents($this->path);
        if (false === $contents) {
            return BuildInfo::unknown();
        }

        try {
            $data = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return BuildInfo::unknown();
        }

        if (!is_array($data)
            || !isset($data['version'], $data['revision'], $data['built_at'])
            || !is_string($data['version'])
            || !is_string($data['revision'])
            || !is_string($data['built_at'])) {
            return BuildInfo::unknown();
        }

        try {
            return BuildInfo::fromValues($data['version'], $data['revision'], $data['built_at']);
        } catch (InvalidBuildInfoException) {
            return BuildInfo::unknown();
        }
    }
}
