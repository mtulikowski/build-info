<?php

declare(strict_types=1);

namespace SmartGroup\BuildInfoBundle;

use SmartGroup\BuildInfoBundle\DependencyInjection\SmartGroupBuildInfoExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class SmartGroupBuildInfoBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new SmartGroupBuildInfoExtension();
    }
}
