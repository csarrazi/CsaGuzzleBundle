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
            'complete' => ['onComplete', RequestEvents::LATE],
            'error'    => ['onError', RequestEvents::EARLY],
        ];
    }

    public function onComplete(CompleteEvent $event)
    {
        $this->add($event->getRequest(), $event->getTransferInfo(), $event->getResponse());
    }

    public function onError(ErrorEvent $event)
    {
        $this->add($event->getRequest(), $event->getTransferInfo(), $event->getResponse(), $event->getException());
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
     * @param array             $info      Transfer info.
     * @param ResponseInterface $response  Response of the request.
     * @param RequestException  $exception The exception thrown during the request, if any.
     */
    private function add(
        RequestInterface $request,
        array $info = [],
        ResponseInterface $response = null,
        RequestException $exception = null
    ) {
        if (isset($this->transactions[$hash = spl_object_hash($request) . spl_object_hash($response ?: $exception)])) {
            return;
        }

        $this->transactions[$hash] = [
            'request' => $request,
            'response' => $response,
            'info' => $info,
            'exception' => $exception,
        ];
    }
}
