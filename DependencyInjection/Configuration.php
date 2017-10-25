<?php

namespace Oh\FormErrorLogBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('oh_form_error_log');

        $rootNode
            ->children()
                ->scalarNode('logger')->isRequired()->cannotBeEmpty()->end()
            ->end()
            ->children()
                ->scalarNode('db_entity_class')->defaultValue('Oh\FormErrorLogBundle\Entity\FormErrorLog')->cannotBeEmpty()->end()
            ->end();

        return $treeBuilder;
    }
}
