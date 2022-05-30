<?php


namespace App\DependencyInjection;


use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class DynamicFormConfiguration implements ConfigurationInterface
{

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('dynamic_form');
        $rootNode = $treeBuilder->getRootNode();
        $this->buildDynamicFormNode($rootNode);

        return $treeBuilder;
    }

    public function buildDynamicFormNode(ArrayNodeDefinition $node): void
    {
        $node
            ->useAttributeAsKey('name')
              ->arrayPrototype()
                ->useAttributeAsKey('field')
                    ->arrayPrototype()
                        ->children()
                            ->booleanNode('enabled')->defaultTrue()->end()
                            ->scalarNode('type')->isRequired()->end()
                            ->variableNode('options')->end()
                            ->variableNode('transformer')->end()
                            ->variableNode('validation')->end()
                            ->scalarNode('data_provider')->end()
                            ->scalarNode('help_message_provider')->end()
                        ->end()
                    ->end()
              ->end()
        ;

/*        $node
            ->useAttributeAsKey('name')
                ->prototype('array')
                    ->useAttributeAsKey('field')
                        ->prototype('array')
                            ->children()
                                ->booleanNode('enabled')->defaultTrue()->end()
                                ->scalarNode('type')->isRequired()->end()
                                ->variableNode('options')->end()
                                ->variableNode('transformer')->end()
                                ->variableNode('validation')->end()
                                ->scalarNode('data_provider')->end()
                                ->scalarNode('help_message_provider')->end()
                            ->end()
                        ->end()
                ->end();*/
    }
}
