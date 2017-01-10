<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Csa\Bundle\GuzzleBundle\Tests\GuzzleHttp\Middleware;

use Csa\Bundle\GuzzleBundle\GuzzleHttp\Middleware\ToleranceMiddleware;
use Csa\Bundle\GuzzleBundle\Tolerance\WaiterFactory;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Tolerance\Operation\ExceptionCatcher\ThrowableCatcherVoter;
use Tolerance\Waiter\Waiter;
use Tolerance\Waiter\WaiterException;

class ToleranceMiddlewareTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Tolerance\Operation\Exception\PromiseException
     */
    public function testMiddlewareFails()
    {
        $response = new Response(500);
        $mocks = array_fill(0, 4, $response);
        $mock = new MockHandler($mocks);
        $handler = HandlerStack::create($mock);

        $waiter = $this->getMock(Waiter::class);
        $waiter
            ->expects($this->at(3))
            ->method('wait')
            ->willThrowException(new WaiterException())
        ;

        $waiterFactory = $this->getMock(WaiterFactory::class, [], [3]);
        $waiterFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($waiter)
        ;

        $errorVoter = $this->getMock(ThrowableCatcherVoter::class);
        $errorVoter
            ->expects($this->exactly(4))
            ->method('shouldCatchThrowable')
            ->willReturn(true)
        ;

        $handler->push(new ToleranceMiddleware($waiterFactory, $errorVoter));

        $client = new Client(['handler' => $handler]);
        $client->get('http://foo.bar');
    }

    public function testMiddlewareSuccessAfterRetry()
    {
        $response = new Response(500);
        $mocks = array_fill(0, 3, $response);
        $mocks[] = new Response(200, [], 'great !');
        $mock = new MockHandler($mocks);
        $handler = HandlerStack::create($mock);

        $waiter = $this->getMock(Waiter::class);
        $waiter
            ->expects($this->exactly(3))
            ->method('wait')
        ;

        $waiterFactory = $this->getMock(WaiterFactory::class, [], [3]);
        $waiterFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($waiter)
        ;

        $errorVoter = $this->getMock(ThrowableCatcherVoter::class);
        $errorVoter
            ->expects($this->exactly(4))
            ->method('shouldCatchThrowable')
            ->will($this->onConsecutiveCalls(true, true, true, false))
        ;

        $handler->push(new ToleranceMiddleware($waiterFactory, $errorVoter));

        $client = new Client(['handler' => $handler]);
        $res = $client->get('http://foo.bar');
        $this->assertEquals('great !', (string) $res->getBody());
    }
}
