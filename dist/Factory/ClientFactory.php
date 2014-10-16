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
     * @param string                $class       The client's class
     * @param SubscriberInterface[] $subscribers A list of subscribers to attach to each client
     */
    public function __construct($class, array $subscribers = [])
    {
        $this->class = $class;
        $this->subscribers = $subscribers;
    }

    public function create(array $options = [])
    {
        $client = new $this->class($options);

        if ($client instanceof HasEmitterInterface) {
            foreach ($this->subscribers as $subscriber) {
                $client->getEmitter()->attach($subscriber);
            }
        }

        return $client;
    }
}
