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

    public function registerSubscriber($name, SubscriberInterface $subscriber)
    {
        $this->subscribers[$name] = $subscriber;
    }
}
