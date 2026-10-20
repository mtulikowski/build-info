<?php

declare(strict_types=1);

use SmartGroup\BuildInfoBundle\Command\GenerateBuildInfoCommand;
use SmartGroup\BuildInfoBundle\Service\BuildInfoGenerator;
use SmartGroup\BuildInfoBundle\Service\BuildInfoReader;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->autowire()
            ->autoconfigure();

    $services->set(BuildInfoReader::class)
        ->arg('$path', '%smart_group_build_info.path%');

    $services->set(BuildInfoGenerator::class)
        ->arg('$path', '%smart_group_build_info.path%')
        ->arg('$projectDir', '%kernel.project_dir%');

    $services->set(GenerateBuildInfoCommand::class);
};
