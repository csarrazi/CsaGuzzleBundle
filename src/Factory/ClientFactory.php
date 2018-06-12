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

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Event\HasEmitterInterface;
use GuzzleHttp\Event\SubscriberInterface;

/**
 * Csa Guzzle client compiler pass.
 *
 * @author Charles Sarrazin <charles@sarraz.in>
 *
 * @deprecated since version 1.3, to be removed in 2.0
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
    }

    /**
     * Creates a Guzzle client.
     *
     * @param array $options
     * @param array $subscribers
     *
     * @return ClientInterface
     */
    public function create(array $options = [], array $subscribers = [])
    {
        @trigger_error('The ClientFactory class is deprecated since version 1.3 and will be removed in 2.0. Use the \'csa_guzzle.client\' tag instead', E_USER_DEPRECATED);
        $client = new $this->class($options);

        if ($client instanceof HasEmitterInterface) {
            foreach ($this->subscribers as $name => $subscriber) {
                if (empty($subscribers) || (isset($subscribers[$name]) && $subscribers[$name])) {
                    $client->getEmitter()->attach($subscriber);
                }
            }
        }

        return $client;
    }

    public function registerSubscriber($name, SubscriberInterface $subscriber)
    {
        @trigger_error('The ClientFactory class is deprecated since version 1.3 and will be removed in 2.0. Use the \'csa_guzzle.client\' tag instead', E_USER_DEPRECATED);
        $this->subscribers[$name] = $subscriber;
    }
}
