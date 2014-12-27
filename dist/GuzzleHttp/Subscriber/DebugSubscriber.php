<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Csa\Bundle\GuzzleBundle\GuzzleHttp\Subscriber;

use GuzzleHttp\Event\AbstractRetryableEvent;
use GuzzleHttp\Event\BeforeEvent;
use GuzzleHttp\Event\RequestEvents;
use GuzzleHttp\Event\SubscriberInterface;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\ResponseInterface;

/**
 * Csa Guzzle Stopwatch integration
 *
 * @author Charles Sarrazin <charles@sarraz.in>
 */
class DebugSubscriber implements SubscriberInterface, \IteratorAggregate, \Countable
{
    /**
     * @var array Requests and responses that have passed through the plugin
     */
    private $transactions = [];

    public function getEvents()
    {
        return [
            'before'   => ['onBefore', RequestEvents::EARLY],
            'complete' => ['onFinish', RequestEvents::EARLY],
            'error'    => ['onFinish', RequestEvents::EARLY],
        ];
    }

    public function onBefore(BeforeEvent $event)
    {
        $event->getRequest()->getConfig()->set('profile_start', microtime(true));
    }

    public function onFinish(AbstractRetryableEvent $event)
    {
        $this->add($event->getRequest(), $event->getResponse());
    }

    /**
     * Returns an Iterator that yields associative array values where each
     * associative array contains a 'request' and 'response' key.
     *
     * @return \Iterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->transactions);
    }

    /**
     * Get the number of requests in the history
     *
     * @return int
     */
    public function count()
    {
        return count($this->transactions);
    }

    /**
     * Add a request to the history
     *
     * @param RequestInterface  $request  Request to add
     * @param ResponseInterface $response Response of the request
     */
    private function add(
        RequestInterface $request,
        ResponseInterface $response = null
    ) {
        $duration = microtime(true) - $request->getConfig()->get('profile_start');
        $this->transactions[] = ['request' => $request, 'response' => $response, 'duration' => $duration];
    }
}
