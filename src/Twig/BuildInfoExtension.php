<?php

declare(strict_types=1);

namespace SmartGroup\BuildInfoBundle\Twig;

use SmartGroup\BuildInfoBundle\Service\BuildInfoReader;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class BuildInfoExtension extends AbstractExtension
{
    public function __construct(private readonly BuildInfoReader $reader)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('build_info', $this->getBuildInfo(...)),
        ];
    }

    /**
     * @return array{version: string, revision: string, built_at: string}
     */
    public function getBuildInfo(): array
    {
        return $this->reader->read()->toArray();
    }
}
