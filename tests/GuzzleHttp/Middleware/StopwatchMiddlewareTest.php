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

use Csa\Bundle\GuzzleBundle\GuzzleHttp\Middleware\StopwatchMiddleware;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise;
use GuzzleHttp\Psr7\Response;
use Symfony\Component\Stopwatch\Stopwatch;

class StopwatchMiddlewareTest extends \PHPUnit_Framework_TestCase
{
    public function testSynchronousRequest()
    {
        $response = new Response(204);
        $mocks = array_fill(0, 3, $response);
        $mock = new MockHandler($mocks);
        $handler = HandlerStack::create($mock);

        $stopwatch = new Stopwatch();

        $handler->push(new StopwatchMiddleware($stopwatch));

        $client = new Client(['handler' => $handler]);

        $client->get('http://foo.bar');
        $this->assertContains('GET http://foo.bar', array_keys($stopwatch->getSectionEvents('__root__')));
    }

    public function testSinglePromise()
    {
        $response = new Response(204);
        $mock = new MockHandler([$response]);
        $handler = HandlerStack::create($mock);

        $stopwatch = new Stopwatch();

        $handler->push(new StopwatchMiddleware($stopwatch));

        $client = new Client(['handler' => $handler]);

        $client->postAsync('http://foo.bar');

        $this->assertContains('POST http://foo.bar', array_keys($stopwatch->getSectionEvents('__root__')));
    }

    public function testMultiplePromises()
    {
        $response = new Response(204);
        $mocks = array_fill(0, 3, $response);
        $mock = new MockHandler($mocks);
        $handler = HandlerStack::create($mock);

        $stopwatch = new Stopwatch();

        $handler->push(new StopwatchMiddleware($stopwatch));

        $client = new Client(['handler' => $handler]);

        $promises = [
            'foo' => $client->getAsync('http://foo.bar'),
            'bar' => $client->getAsync('http://foo.bar'),
            'baz' => $client->getAsync('http://foo.bar'),
        ];

        Promise\unwrap($promises);

        for ($i = 1; $i <= 3; ++$i) {
            $this->assertContains(
                $i > 1 ? sprintf('GET http://foo.bar (%s)', $i) : 'GET http://foo.bar',
                array_keys($stopwatch->getSectionEvents('__root__'))
            );
        }
    }
}
