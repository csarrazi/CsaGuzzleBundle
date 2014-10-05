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
            ->fixXmlConfig('subscriber')
            ->children()
                ->booleanNode('profiler')
                    ->info('Whether or not to enable the profiler')
                    ->example('%kernel.debug%')
                    ->defaultFalse()
                ->end()
                ->arrayNode('subscribers')
                    ->useAttributeAsKey('name')
                    ->prototype('boolean')->end()
                ->end()
                ->arrayNode('factory')
                    ->children()
                        ->scalarNode('class')->defaultValue('Csa\Bundle\GuzzleBundle\Factory\Client')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
