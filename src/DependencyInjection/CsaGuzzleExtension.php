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

use Csa\Bundle\GuzzleBundle\DependencyInjection\CompilerPass\MiddlewarePass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Csa Guzzle Extension
 *
 * @author Charles Sarrazin <charles@sarraz.in>
 */
class CsaGuzzleExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('middleware.xml');
        $loader->load('collector.xml');
        $loader->load('twig.xml');

        $dataCollector = $container->getDefinition('csa_guzzle.data_collector.guzzle');
        $dataCollector->addArgument($config['profiler']['max_body_size']);

        if (!$config['profiler']['enabled']) {
            $container->removeDefinition('csa_guzzle.middleware.history');
            $container->removeDefinition('csa_guzzle.middleware.stopwatch');
            $container->removeDefinition('csa_guzzle.data_collector.guzzle');
            $container->removeDefinition('csa_guzzle.twig.extension');
        }

        $this->processLoggerConfiguration($config['logger'], $container);

        $this->processCacheConfiguration($config['cache'], $container, $config['profiler']['enabled']);

        $this->processClientsConfiguration($config, $container);
    }

    private function processLoggerConfiguration(array $config, ContainerBuilder $container)
    {
        $loggerDefinition = $container->getDefinition('csa_guzzle.middleware.logger');

        if ($config['service']) {
            $loggerDefinition->replaceArgument(0, new Reference($config['service']));
        }

        if ($config['format']) {
            $formatterDefinition = $container->getDefinition('csa_guzzle.logger.message_formatter');
            $formatterDefinition->replaceArgument(0, $config['format']);
        }

        if (!$config['enabled']) {
            $container->removeDefinition('csa_guzzle.middleware.logger');
            $container->removeDefinition('csa_guzzle.logger.message_formatter');
        }
    }

    private function processCacheConfiguration(array $config, ContainerBuilder $container, $debug)
    {
        if (!$config['enabled']) {
            $container->removeDefinition('csa_guzzle.middleware.cache');

            return;
        }

        $container->getDefinition('csa_guzzle.middleware.cache')->addArgument($debug);

        $container->setAlias('csa_guzzle.cache_adapter', $config['adapter']);
    }

    private function processClientsConfiguration(array $config, ContainerBuilder $container)
    {
        foreach ($config['clients'] as $name => $options) {
            $client = new Definition($options['class']);
            $client->addArgument(isset($options['config']) ? $options['config'] : null);

            $attributes = [];

            if (!empty($options['middleware'])) {
                $attributes['middleware'] = implode(' ', $options['middleware']);
            }

            $client->addTag(MiddlewarePass::CLIENT_TAG, $attributes);

            $clientServiceId = sprintf('csa_guzzle.client.%s', $name);
            $container->setDefinition($clientServiceId, $client);
        }
    }
}
