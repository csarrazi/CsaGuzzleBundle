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
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Csa Guzzle Logger integration
 *
 * @author Charles Sarrazin <charles@sarraz.in>
 */
class LoggerSubscriber implements SubscriberInterface
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger ?: new NullLogger();
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
        $this->logger->debug('Starting Guzzle request', ['uri' => $event->getRequest()->getUrl()]);
    }

    public function onComplete(CompleteEvent $event)
    {
        $this->logger->debug('Completed Guzzle request', ['uri' => $event->getRequest()->getUrl()]);
    }

    public function onError(ErrorEvent $event)
    {
        $this->logger->error(sprintf('Error during Guzzle request: "%s"', $event->getException()->getMessage()), [
            'uri' => $event->getRequest()->getUrl(),
            'exception' => $event->getException(),
        ]);
    }
}
