<?php

declare(strict_types=1);

namespace SmartGroup\BuildInfoBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('build_info');

        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();
        $children = $rootNode->children();

        $children
            ->scalarNode('path')
            ->defaultValue('%kernel.project_dir%/var/build-info.json')
            ->cannotBeEmpty();

        $twigNode = $children
            ->arrayNode('twig')
            ->addDefaultsIfNotSet();

        $twigNode
            ->children()
            ->booleanNode('enabled')
            ->defaultFalse();

        return $treeBuilder;
    }
}
