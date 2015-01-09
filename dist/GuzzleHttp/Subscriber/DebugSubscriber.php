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

use GuzzleHttp\Event\BeforeEvent;
use GuzzleHttp\Event\CompleteEvent;
use GuzzleHttp\Event\ErrorEvent;
use GuzzleHttp\Event\RequestEvents;
use GuzzleHttp\Event\SubscriberInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\ResponseInterface;

/**
 * Csa Guzzle Profiler integration
 *
 * @author Charles Sarrazin <charles@sarraz.in>
 */
class DebugSubscriber implements SubscriberInterface, \IteratorAggregate
{
    /**
     * @var array An array of guzzle transactions (requests and responses).
     */
    private $transactions = [];

    public function getEvents()
    {
        return [
            'before'   => ['onBefore', RequestEvents::EARLY],
            'complete' => ['onComplete', RequestEvents::LATE],
            'error'    => ['onError', RequestEvents::EARLY],
        ];
    }

    public function onBefore(BeforeEvent $event)
    {
        $event->getRequest()->getConfig()->set('profile_start', microtime(true));
    }

    public function onComplete(CompleteEvent $event)
    {
        $this->add($event->getRequest(), $event->getResponse());
    }

    public function onError(ErrorEvent $event)
    {
        $this->add($event->getRequest(), $event->getResponse(), $event->getException());
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
     * Add a request to the history
     *
     * @param RequestInterface  $request   Request to add.
     * @param ResponseInterface $response  Response of the request.
     * @param RequestException  $exception The exception thrown during the request, if any.
     */
    private function add(
        RequestInterface $request,
        ResponseInterface $response = null,
        RequestException $exception = null
    ) {
        $duration = microtime(true) - $request->getConfig()->get('profile_start');
        $this->transactions[] = [
            'request' => $request,
            'response' => $response,
            'duration' => $duration,
            'exception' => $exception,
        ];
    }
}
