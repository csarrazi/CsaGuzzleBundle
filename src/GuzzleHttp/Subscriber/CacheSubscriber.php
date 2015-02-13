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

use Csa\Bundle\GuzzleBundle\GuzzleHttp\Cache\StorageAdapterInterface;
use GuzzleHttp\Event\BeforeEvent;
use GuzzleHttp\Event\CompleteEvent;
use GuzzleHttp\Event\RequestEvents;
use GuzzleHttp\Event\SubscriberInterface;

/**
 * Csa Guzzle Cache integration
 *
 * @author Charles Sarrazin <charles@sarraz.in>
 */
class CacheSubscriber implements SubscriberInterface
{
    private $storage;

    public function __construct(StorageAdapterInterface $storage)
    {
        $this->storage = $storage;
    }

    public function getEvents()
    {
        return [
            'before'   => ['onBefore', RequestEvents::LATE],
            'complete' => ['onComplete', RequestEvents::EARLY],
        ];
    }

    public function onBefore(BeforeEvent $event)
    {
        $request = $event->getRequest();

        if (!$response = $this->storage->fetch($request)) {
            $request->getConfig()->set('cache_lookup', 'MISS');

            return;
        }

        $request->getConfig()->set('cache_lookup', 'HIT');
        $request->getConfig()->set('cache_hit', true);

        $event->intercept($response);
    }

    public function onComplete(CompleteEvent $event)
    {
        $this->storage->save($event->getRequest(), $event->getResponse());
    }
}
