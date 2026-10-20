<?php

namespace SmartGroup\BuildInfoBundle\Twig;

use SmartGroup\BuildInfoBundle\Service\BuildInfo;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

final class BuildInfoExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(private readonly BuildInfo $buildInfo)
    {
    }

    public function getGlobals(): array
    {
        return [
            'build_info' => $this->buildInfo->get(),
        ];
    }
}
