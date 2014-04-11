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

use GuzzleHttp\Client as BaseClient;
use GuzzleHttp\Event\SubscriberInterface;

/**
 * Client class to simplify adding event subscribers
 *
 * @author Charles Sarrazin <charles@sarraz.in>
 */
class Client extends BaseClient
{
    public function addSubscriber(SubscriberInterface $subscriber)
    {
        $this->getEmitter()->attach($subscriber);
    }
}
