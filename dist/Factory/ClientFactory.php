<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Csa\Bundle\GuzzleBundle\Factory;

use GuzzleHttp\Event\HasEmitterInterface;
use GuzzleHttp\Event\SubscriberInterface;

/**
 * Csa Guzzle client compiler pass
 *
 * @author Charles Sarrazin <charles@sarraz.in>
 */
class ClientFactory
{
    private $class;
    private $subscribers;
    private $clientOptions;

    /**
     * @param string $class The client's class
     */
    public function __construct($class)
    {
        $this->class = $class;
        $this->subscribers = [];
        $this->clientOptions = [];
    }

    public function create(array $options = [], array $subscribers = [])
    {
        $client = new $this->class($options);

        if ($client instanceof HasEmitterInterface) {
            foreach ($this->subscribers as $name => $subscriber) {
                if (!$subscribers || (isset($subscribers[$name]) && $subscribers[$name])) {
                    $client->getEmitter()->attach($subscriber);
                }
            }
        }

        return $client;
    }

    public function createNamed($alias)
    {
        if (!isset($this->clientOptions[$alias])) {
            throw new \InvalidArgumentException(sprintf('Could not find configuration for client "%s"', $alias));
        }

        $clientOptions = $this->clientOptions[$alias];

        $client = new $this->class($clientOptions['options']);

        if (!$client instanceof HasEmitterInterface) {
            return;
        }

        foreach ($clientOptions['subscribers'] as $subscriber => $enabled) {
            if (!isset($this->subscribers[$subscriber])) {
                throw new \LogicException(sprintf('Invalid subscriber "%s" in configuration for client "%s"', $subscriber, $alias));
            }

            if ($enabled) {
                $client->getEmitter()->attach($this->subscribers[$subscriber]);
            }
        }

        return $client;
    }

    public function registerSubscriber($name, SubscriberInterface $subscriber)
    {
        $this->subscribers[$name] = $subscriber;
    }

    public function registerClientConfiguration($alias, array $options = [], array $subscribers = [])
    {
        $this->clientOptions[$alias] = [
            'options' => $options,
            'subscribers' => $subscribers,
        ];
    }
}
