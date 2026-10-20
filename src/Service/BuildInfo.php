<?php

namespace SmartGroup\BuildInfoBundle\Service;

final class BuildInfo
{
    private const UNKNOWN = [
        'version' => 'unknown',
        'commit' => 'unknown',
        'committed_at' => 'unknown',
    ];

    public function __construct(private readonly string $projectDir)
    {
    }

    /**
     * @return array{version: string, commit: string, committed_at: string}
     */
    public function get(): array
    {
        $contents = @file_get_contents($this->projectDir.'/var/build-info.json');
        if (false === $contents) {
            return self::UNKNOWN;
        }

        try {
            $data = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return self::UNKNOWN;
        }

        if (!is_array($data)) {
            return self::UNKNOWN;
        }

        foreach (array_keys(self::UNKNOWN) as $key) {
            if (!isset($data[$key]) || !is_string($data[$key]) || '' === $data[$key]) {
                return self::UNKNOWN;
            }
        }

        return [
            'version' => $data['version'],
            'commit' => $data['commit'],
            'committed_at' => $data['committed_at'],
        ];
    }
}
