<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Csa\Bundle\GuzzleBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Csa Guzzle subscriber compiler pass
 *
 * @author Charles Sarrazin <charles@sarraz.in>
 */
class SubscriberPass implements CompilerPassInterface
{
    const FACTORY_SERVICE_ID = 'csa_guzzle.client_factory';
    const SUBSCRIBER_TAG = 'csa_guzzle.subscriber';
    const CLIENT_TAG = 'csa_guzzle.client';

    public function process(ContainerBuilder $container)
    {
        $subscribers = $container->findTaggedServiceIds(self::SUBSCRIBER_TAG);

        $this->addSubscribersToClients($container, $subscribers);

        if (!count($subscribers)) {
            return;
        }

        $factory = $container->findDefinition(self::FACTORY_SERVICE_ID);

        foreach ($subscribers as $subscriber => $options) {
            $factory->addMethodCall('registerSubscriber', [
                $options[0]['alias'],
                new Reference($subscriber),
            ]);
        }
    }

    /**
     * Creates configurator service for each client to add registered subscribers
     * to each client's emitter. Essentially it's converting the following XML
     *
     *     <service id="foo" class="GuzzleHttp\Client">
     *         <tag name="csa_guzzle.client" subscribers="foo, bar" />
     *     </service>
     *
     * to the  following code in the container (rough equivalent)
     *
     *     public function getFoo()
     *     {
     *         $client = new GuzzleHttp\Client();
     *         $client->getEmitter()->attach($this->get('foo'));
     *         $client->getEmitter()->attach($this->get('bar'));
     *
     *         return $client;
     *     }
     *
     * @param ContainerBuilder $container
     */
    private function addSubscribersToClients(ContainerBuilder $container, array $taggedSubscriberIds)
    {
        $subscriberIds = $this->mapSubscriberIdsByAlias($taggedSubscriberIds);

        foreach ($this->findSubscriberAliasesByClientId($container) as $clientId => $aliases) {
            $client = $container->findDefinition($clientId);

            $configurator = $this->createConfigurator(array_values($aliases
                ? array_intersect_key($subscriberIds, array_flip($aliases))
                : $subscriberIds
            ));

            // Wraps the previous configurator in case the client already had one.
            $configurator->addArgument($client->getConfigurator());

            $configuratorId = sprintf('csa_guzzle._configurator.%s', $clientId);
            $container->setDefinition($configuratorId, $configurator);

            $client->setConfigurator([new Reference($configuratorId), 'configure']);
        }
    }

    /**
     * Finds all tagged clients and lists their registered subscribers by client
     * ids. Empty arrays are returned for clients specifying no subscribers.
     *
     * @param ContainerBuilder $container
     *
     * @return array An array of subscriber ids with their consuming client as a key
     */
    private function findSubscriberAliasesByClientId(ContainerBuilder $container)
    {
        $tagsByClientId = $container->findTaggedServiceIds(self::CLIENT_TAG);
        $aliases = [];

        foreach ($tagsByClientId as $clientId => $tags) {
            $subscribers = [];

            foreach ($tags as $tag) {
                if (isset($tag['subscribers'])) {
                    $subscribers = array_merge(
                        $subscribers,
                        array_map('trim', explode(',', $tag['subscribers']))
                    );
                }
            }

            $aliases[$clientId] = $subscribers;
        }

        return $aliases;
    }

    /**
     * @param array $subscriberIds List of service ids pointing to subscribers
     *
     * @return Definition
     */
    private function createConfigurator(array $subscriberIds)
    {
        $subscriberRefs = array_map(function ($subscriberId) {
            return new Reference($subscriberId);
        }, $subscriberIds);

        $configurator = new Definition('Csa\Bundle\GuzzleBundle\DependencyInjection\Configurator\ClientConfigurator');
        $configurator->setPublic(false);
        $configurator->addArgument($subscriberRefs);

        return $configurator;
    }

    /**
     * @param array $subscriberIds
     *
     * @return array Subscriber service ids as values & their aliases as keys
     */
    private function mapSubscriberIdsByAlias(array $subscriberIds)
    {
        $mapped = [];

        foreach ($subscriberIds as $subscriberId => $tags) {
            foreach ($tags as $tag) {
                if (isset($tag['alias'])) {
                    $mapped[$tag['alias']] = $subscriberId;
                }
            }
        }

        return $mapped;
    }
}
