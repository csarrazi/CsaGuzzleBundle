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
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Tolerance\Operation\Operation;

/**
 * Csa Guzzle Extension.
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
        $dataCollector->replaceArgument(0, $config['profiler']['max_body_size']);

        if (!$config['profiler']['enabled']) {
            $container->removeDefinition('csa_guzzle.middleware.history');
            $container->removeDefinition('csa_guzzle.middleware.stopwatch');
            $container->removeDefinition('csa_guzzle.data_collector.guzzle');
            $container->removeDefinition('csa_guzzle.twig.extension');
        }

        $this->processLoggerConfiguration($config['logger'], $container);

        $this->processMockConfiguration($config['mock'], $container, $loader, $config['profiler']['enabled']);

        $this->processCacheConfiguration($config['cache'], $container, $config['profiler']['enabled']);

        $this->processToleranceConfiguration($config['tolerance'], $container);

        $this->processClientsConfiguration($config, $container, $config['profiler']['enabled']);
    }

    private function processLoggerConfiguration(array $config, ContainerBuilder $container)
    {
        if (!$config['enabled']) {
            $container->removeDefinition('csa_guzzle.middleware.logger');
            $container->removeDefinition('csa_guzzle.logger.message_formatter');

            return;
        }

        $loggerDefinition = $container->getDefinition('csa_guzzle.middleware.logger');

        if ($config['service']) {
            $loggerDefinition->replaceArgument(0, new Reference($config['service']));
        }

        if ($config['format']) {
            $formatterDefinition = $container->getDefinition('csa_guzzle.logger.message_formatter');
            $formatterDefinition->replaceArgument(0, $config['format']);
        }

        if ($config['level']) {
            $loggerDefinition->replaceArgument(2, $config['level']);
        }
    }

    private function processMockConfiguration(array $config, ContainerBuilder $container, LoaderInterface $loader, $debug)
    {
        if (!$config['enabled']) {
            return;
        }

        $loader->load('mock.xml');

        $storage = $container->getDefinition('csa_guzzle.mock.storage');
        $storage->setArguments([
            $config['storage_path'],
            $config['request_headers_blacklist'],
            $config['response_headers_blacklist'],
        ]);

        $middleware = $container->getDefinition('csa_guzzle.middleware.mock');
        $middleware->replaceArgument(1, $config['mode']);

        $middleware->replaceArgument(2, $debug);
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

    private function processToleranceConfiguration(array $config, ContainerBuilder $container)
    {
        if (!$config['enabled']) {
            $container->removeDefinition('csa_guzzle.middleware.tolerance');
            $container->removeDefinition('csa_guzzle.tolerance.waiter_factory');
            $container->removeDefinition('csa_guzzle.tolerance.error_voter');

            return;
        }

        if (!interface_exists(Operation::class)) {
            throw new \LogicException('You must install package "tolerance/tolerance" to use the "tolerance" middleware.');
        }

        if (null !== $config['waiter_factory']) {
            $container->getDefinition('csa_guzzle.middleware.tolerance')->replaceArgument(0, new Reference($config['waiter_factory']));
            $container->removeDefinition('csa_guzzle.tolerance.waiter_factory');
        }

        if (null !== $config['error_voter']) {
            $container->getDefinition('csa_guzzle.middleware.tolerance')->replaceArgument(1, new Reference($config['error_voter']));
            $container->removeDefinition('csa_guzzle.tolerance.error_voter');
        }
    }

    private function processClientsConfiguration(array $config, ContainerBuilder $container, $debug)
    {
        foreach ($config['clients'] as $name => $options) {
            $client = new Definition($options['class']);
            $client->setLazy($options['lazy']);

            if (isset($options['config'])) {
                if (!is_array($options['config'])) {
                    throw new InvalidArgumentException(sprintf(
                        'Config for "csa_guzzle.client.%s" should be an array, but got %s',
                        $name,
                        gettype($options['config'])
                    ));
                }
                $client->addArgument($this->buildGuzzleConfig($options['config'], $debug));
            }

            $attributes = [];

            if (!empty($options['middleware'])) {
                if ($debug) {
                    $options['middleware'][] = 'stopwatch';
                    $options['middleware'][] = 'history';
                    $options['middleware'][] = 'logger';
                }

                $attributes['middleware'] = implode(' ', array_unique($options['middleware']));
            }

            $client->addTag(MiddlewarePass::CLIENT_TAG, $attributes);

            $clientServiceId = sprintf('csa_guzzle.client.%s', $name);
            $container->setDefinition($clientServiceId, $client);

            if (isset($options['alias'])) {
                $container->setAlias($options['alias'], $clientServiceId);
            }
        }
    }

    private function buildGuzzleConfig(array $config, $debug)
    {
        if (isset($config['handler'])) {
            $config['handler'] = new Reference($config['handler']);
        }

        if ($debug && function_exists('curl_init')) {
            $config['on_stats'] = [new Reference('csa_guzzle.data_collector.history_bag'), 'addStats'];
        }

        return $config;
    }
}
