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

use Csa\Bundle\GuzzleBundle\GuzzleHttp\Middleware\HistoryMiddleware;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class HistoryMiddlewareTest extends \PHPUnit_Framework_TestCase
{
    public function testMiddleware()
    {
        $response = new Response(204);
        $mocks = array_fill(0, 2, $response);
        $mock = new MockHandler($mocks);
        $handler = HandlerStack::create($mock);

        $storage = new \ArrayObject();

        $handler->push(new HistoryMiddleware($storage));

        $client = new Client(['handler' => $handler]);

        $client->get('http://foo.bar');
        $client->get('http://foo.bar');

        $storage = array_values((array) $storage);

        $this->assertCount(2, $storage);
        $this->assertArrayHasKey('request', $storage[0]);
        $this->assertArrayHasKey('response', $storage[0]);
        $this->assertArrayHasKey('options', $storage[0]);
        $this->assertArrayHasKey('info', $storage[0]);
        $this->assertArrayHasKey('error', $storage[0]);
        $this->assertSame($response, $storage[0]['response']);
    }
}
