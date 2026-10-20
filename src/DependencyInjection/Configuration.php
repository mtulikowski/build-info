<?php

declare(strict_types=1);

namespace SmartGroup\BuildInfoBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('build_info');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('path')
                    ->defaultValue('%kernel.project_dir%/var/build-info.json')
                    ->cannotBeEmpty()
                ->end()
                ->arrayNode('twig')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultFalse()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
