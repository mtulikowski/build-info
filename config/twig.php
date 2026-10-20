<?php

declare(strict_types=1);

use SmartGroup\BuildInfoBundle\Twig\BuildInfoExtension;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set(BuildInfoExtension::class)
        ->autowire()
        ->tag('twig.extension');
};
