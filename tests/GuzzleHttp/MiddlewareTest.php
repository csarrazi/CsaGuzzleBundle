<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Csa\Bundle\GuzzleBundle\Tests\GuzzleHttp;

use Csa\Bundle\GuzzleBundle\GuzzleHttp\Cache\StorageAdapterInterface;
use Csa\Bundle\GuzzleBundle\GuzzleHttp\History\History;
use Csa\Bundle\GuzzleBundle\GuzzleHttp\Middleware;
use Symfony\Component\Stopwatch\Stopwatch;
use Tolerance\Bridge\Guzzle\Waiter\WaiterFactory;
use Tolerance\Operation\ExceptionCatcher\ThrowableCatcherVoter;

class MiddlewareTest extends \PHPUnit_Framework_TestCase
{
    public function testCacheMiddleware()
    {
        $adapter = $this->getMock(StorageAdapterInterface::class);
        $this->assertInstanceOf(Middleware\CacheMiddleware::class, Middleware::cache($adapter));
    }

    public function testHistoryMiddleware()
    {
        $this->assertInstanceOf(Middleware\HistoryMiddleware::class, Middleware::history(new History()));
    }

    public function testStopwatchMiddleware()
    {
        $stopwatch = new Stopwatch();
        $this->assertInstanceOf(Middleware\StopwatchMiddleware::class, Middleware::stopwatch($stopwatch));
    }

    public function testMockMiddleware()
    {
        $adapter = $this->getMock(StorageAdapterInterface::class);
        $this->assertInstanceOf(Middleware\MockMiddleware::class, Middleware::mock($adapter, 'foo'));
    }

    public function testToleranceMiddleware()
    {
        $waiterFactory = $this->getMock(WaiterFactory::class, [], [2]);
        $errorVoter = $this->getMock(ThrowableCatcherVoter::class);
        $this->assertInstanceOf(Middleware\ToleranceMiddleware::class, Middleware::tolerance($waiterFactory, $errorVoter));
    }
}
