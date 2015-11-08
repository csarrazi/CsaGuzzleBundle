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
            ->beforeNormalization()
                ->ifTrue(function ($v) {
                    return isset($v['factory_class']);
                })
                ->then(function () {
                    trigger_error('The ClientFactory class is deprecated since version 1.3 and will be removed in 2.0. Use the \'csa_guzzle.client\' tag instead', E_USER_DEPRECATED);
                })
            ->end()
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
                    ->scalarNode('class')->defaultValue('GuzzleHttp\Client')->end()
                    ->variableNode('config')->end()
                    ->arrayNode('subscribers')
                        ->useAttributeAsKey('subscriber_name')
                        ->prototype('boolean')->end()
                    ->end()
                    ->scalarNode('description')
                        ->validate()
                            ->ifTrue(function ($path) {
                                return !is_readable($path) || is_dir($path);
                            })
                            ->thenInvalid('File "%s" is not readable description file')
                        ->end()
                        ->validate()
                            ->ifTrue(function () {
                                return !class_exists('GuzzleHttp\\Command\\Guzzle\\GuzzleClient');
                            })
                            ->thenInvalid('Class %s is missing. Did you forget to add guzzlehttp/services to your project\'s composer.json?')
                        ->end()
                    ->end()
                    ->scalarNode('alias')->defaultNull()->end()
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
            ->beforeNormalization()
                ->ifTrue(function ($v) {
                    return isset($v['service']);
                })
                ->then(function ($v) {
                    trigger_error('The csa_guzzle.cache.service configuration key is deprecated since version 1.3 and will be removed in 2.0. Please directly use csa_guzzle.cache.adapter instead', E_USER_DEPRECATED);

                    return $v;
                })
            ->end()
            ->validate()
                ->ifTrue(function ($v) {
                    return $v['enabled'] && null === $v['service'] && null === $v['adapter']['service'];
                })
                ->thenInvalid('The csa_guzzle.cache.adapter key should be configured.')
            ->end()
            ->canBeEnabled()
            ->children()
                ->arrayNode('adapter')
                    ->beforeNormalization()
                        ->ifTrue(function ($v) {
                            return is_array($v) && (isset($v['type']) || isset($v['service']));
                        })
                        ->then(function ($v) {
                            trigger_error('The csa_guzzle.cache.adapter.type and csa_guzzle.cache.adapter.service configuration keys are deprecated since version 1.3 and will be removed in 2.0. Please directly use csa_guzzle.cache.adapter instead', E_USER_DEPRECATED);

                            return $v;
                        })
                    ->end()
                    ->beforeNormalization()
                        ->ifString()
                        ->then(function ($v) {
                            return [
                                'type' => 'custom',
                                'service' => $v,
                            ];
                        })
                    ->end()
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
                        ->scalarNode('service')->defaultNull()->end()
                    ->end()
                ->end()
                ->scalarNode('service')->defaultNull()->end()
            ->end()
        ;

        return $node;
    }
}
