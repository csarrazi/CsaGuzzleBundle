<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Csa\Bundle\GuzzleBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This class contains the configuration information for the bundle
 *
 * This information is solely responsible for how the different configuration
 * sections are normalized, and merged.
 *
 * @author Charles Sarrazin <charles@sarraz.in>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('csa_guzzle');

        $rootNode
            ->fixXmlConfig('client')
            ->children()
                ->booleanNode('profiler')
                    ->info('Whether or not to enable the profiler')
                    ->example('%kernel.debug%')
                    ->defaultFalse()
                ->end()
                ->booleanNode('logger')
                    ->info('Whether or not to enable the logger')
                    ->example('%kernel.debug%')
                    ->defaultFalse()
                ->end()
                ->arrayNode('clients')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->arrayNode('config')->end()
                            ->arrayNode('subscribers')
                                ->useAttributeAsKey('subscriber_name')
                                ->prototype('boolean')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('factory_class')->defaultValue('GuzzleHttp\Client')->end()
                ->arrayNode('cache')
                    ->canBeEnabled()
                    ->children()
                        ->scalarNode('type')
                            ->defaultValue('doctrine')
                            ->validate()
                                ->ifNotInArray(['doctrine'])
                                ->thenInvalid('Invalid cache adapter')
                            ->end()
                        ->end()
                        ->scalarNode('service')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
