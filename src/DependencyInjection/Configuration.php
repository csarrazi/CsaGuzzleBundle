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

use Csa\Bundle\GuzzleBundle\DataCollector\GuzzleCollector;
use GuzzleHttp\Subscriber\Log\Formatter;
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
                ->arrayNode('profiler')
                    ->canBeEnabled()
                    ->children()
                        ->integerNode('max_body_size')
                            ->info('The maximum size of the body which should be stored in the profiler (in bytes)')
                            ->example('65536')
                            ->defaultValue(GuzzleCollector::MAX_BODY_SIZE)
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('logger')
                    ->canBeEnabled()
                    ->children()
                        ->scalarNode('service')->defaultNull()->end()
                        ->scalarNode('format')
                            ->beforeNormalization()
                                ->ifInArray(['clf', 'debug', 'short'])
                                ->then(function ($v) {
                                    return constant('GuzzleHttp\Subscriber\Log\Formatter::'.strtoupper($v));
                                })
                            ->end()
                            ->defaultValue(Formatter::CLF)
                        ->end()
                    ->end()
                ->end()
                ->append($this->createClientsNode())
                ->scalarNode('factory_class')->defaultValue('GuzzleHttp\Client')->end()
                ->append($this->createCacheNode())
            ->end()
        ;

        return $treeBuilder;
    }

    private function createClientsNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('clients');

        $node
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->children()
                    ->variableNode('config')->end()
                    ->arrayNode('subscribers')
                        ->useAttributeAsKey('subscriber_name')
                        ->prototype('boolean')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    private function createCacheNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('cache');

        $node
            ->canBeEnabled()
            ->children()
                ->arrayNode('adapter')
                    ->addDefaultsIfNotSet(['type' => 'doctrine'])
                    ->validate()
                        ->ifTrue(function ($v) {
                            return 'custom' === $v['type'] && null === $v['service'];
                        })
                        ->thenInvalid('The "service" node is mandatory when using a custom adapter')
                    ->end()
                    ->children()
                        ->scalarNode('type')
                            ->defaultValue('doctrine')
                            ->validate()
                                ->ifNotInArray(['doctrine', 'custom'])
                                ->thenInvalid('Invalid cache adapter')
                            ->end()
                        ->end()
                        ->scalarNode('service')->end()
                    ->end()
                ->end()
                ->scalarNode('service')->isRequired()->end()
            ->end()
        ;

        return $node;
    }
}
