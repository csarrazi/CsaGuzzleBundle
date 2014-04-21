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
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\ResponseInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Stopwatch\StopwatchEvent;

/**
 * Csa Guzzle Stopwatch integration
 *
 * @author Charles Sarrazin <charles@sarraz.in>
 */
class DebugSubscriber implements SubscriberInterface, \IteratorAggregate, \Countable
{
    private $stopwatch;

    /** @var int The maximum number of requests to maintain in the history */
    private $limit;

    /** @var array Requests and responses that have passed through the plugin */
    private $transactions = [];

    public function __construct(Stopwatch $stopwatch, $limit = 10)
    {
        $this->stopwatch = $stopwatch;
        $this->limit = $limit;
    }

    public function getEvents()
    {
        return [
            'before'   => ['onBefore', RequestEvents::EARLY],
            'complete' => ['onComplete', RequestEvents::EARLY],
            'error'    => ['onError', RequestEvents::EARLY],
        ];
    }

    public function onBefore(BeforeEvent $event)
    {
        $this->stopwatch->start($event->getRequest()->getUrl(), 'guzzle');
    }

    public function onComplete(CompleteEvent $event)
    {
        $request = $event->getRequest();
        $stopwatchEvent = $this->stopwatch->stop($request->getUrl(), 'guzzle');
        $this->add($request, $event->getResponse(), $stopwatchEvent);
    }

    public function onError(ErrorEvent $event)
    {
        $request = $event->getRequest();
        $url = $request->getUrl();

        if (!$this->stopwatch->isStarted($url)) {
            return;
        }

        $stopwatchEvent = $this->stopwatch->stop($url, 'guzzle');

        $this->add($request, $event->getResponse(), $stopwatchEvent);
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
     * Get the last request sent
     *
     * @return RequestInterface
     */
    public function getLastRequest()
    {
        return end($this->transactions)['request'];
    }

    /**
     * Get the last response in the history
     *
     * @return ResponseInterface|null
     */
    public function getLastResponse()
    {
        return end($this->transactions)['response'];
    }

    /**
     * Get the last duration in the history
     *
     * @return integer
     */
    public function getLastDuration()
    {
        return end($this->transactions)['duration'];
    }

    /**
     * Clears the history
     */
    public function clear()
    {
        $this->transactions = array();
    }

    /**
     * Add a request to the history
     *
     * @param RequestInterface  $request  Request to add
     * @param ResponseInterface $response Response of the request
     * @param integer           $duration Request duration, in ms
     */
    private function add(
        RequestInterface $request,
        ResponseInterface $response = null,
        StopwatchEvent $event = null
    ) {
        $this->transactions[] = ['request' => $request, 'response' => $response, 'duration' => $event ? $event->getDuration() : 0];

        if (count($this->transactions) > $this->limit) {
            array_shift($this->transactions);
        }
    }
}
