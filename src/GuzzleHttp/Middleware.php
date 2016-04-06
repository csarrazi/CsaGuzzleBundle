<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Csa\Bundle\GuzzleBundle\GuzzleHttp;

use Csa\Bundle\GuzzleBundle\GuzzleHttp\Cache\StorageAdapterInterface;
use Csa\Bundle\GuzzleBundle\GuzzleHttp\Middleware\CacheMiddleware;
use Csa\Bundle\GuzzleBundle\GuzzleHttp\Middleware\HistoryMiddleware;
use Csa\Bundle\GuzzleBundle\GuzzleHttp\Middleware\MockMiddleware;
use Csa\Bundle\GuzzleBundle\GuzzleHttp\Middleware\StopwatchMiddleware;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Csa Guzzle Middleware.
 *
 * @author Charles Sarrazin <charles@sarraz.in>
 */
class Middleware
{
    public static function stopwatch(Stopwatch $stopwatch)
    {
        return new StopwatchMiddleware($stopwatch);
    }

    public static function cache(StorageAdapterInterface $adapter, $debug = false)
    {
        return new CacheMiddleware($adapter, $debug);
    }

    public static function history(\SplObjectStorage $container)
    {
        return new HistoryMiddleware($container);
    }

    public static function mock(StorageAdapterInterface $storage, $mode, $debug = false)
    {
        return new MockMiddleware($storage, $mode, $debug);
    }
}
