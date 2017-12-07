<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Csa\Bundle\GuzzleBundle\Tests\DataCollector;

use Csa\Bundle\GuzzleBundle\DataCollector\GuzzleCollector;
use Csa\Bundle\GuzzleBundle\GuzzleHttp\History\History;
use Csa\Bundle\GuzzleBundle\GuzzleHttp\Middleware;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \Csa\Bundle\GuzzleBundle\DataCollector\GuzzleCollector
 */
class GuzzleCollectorTest extends \PHPUnit_Framework_TestCase
{
    public function testCollect()
    {
        $mocks = array_fill(0, 3, new Response(204));

        $mock = new MockHandler($mocks);
        $handler = HandlerStack::create($mock);
        $collector = new GuzzleCollector();
        $handler->push(Middleware::history($collector->getHistory()));
        $client = new Client(['handler' => $handler]);

        $request = Request::createFromGlobals();
        $response = $this->createMock('Symfony\Component\HttpFoundation\Response');
        $collector->collect($request, $response, new \Exception());
        $this->assertCount(0, $collector->getCalls());

        $client->get('http://foo.bar');
        $collector->collect($request, $response, new \Exception());
        $calls = $collector->getCalls();
        $this->assertCount(1, $calls);

        $client->get('http://foo.bar');
        $collector->collect($request, $response, new \Exception());
        $this->assertCount(2, $collector->getCalls());
    }

    public function testCollectCurlData()
    {
        if (!class_exists(\Namshi\Cuzzle\Formatter\CurlFormatter::class)) {
            $this->markTestSkipped('namshi/cuzzle not installed');
        }
        $mocks = array_fill(0, 3, new Response(204));

        $mock = new MockHandler($mocks);
        $handler = HandlerStack::create($mock);
        $collector = new GuzzleCollector();
        $handler->push(Middleware::history($collector->getHistory()));
        $client = new Client(['handler' => $handler]);

        $request = Request::createFromGlobals();
        $response = $this->createMock('Symfony\Component\HttpFoundation\Response');

        $client->get('http://foo.bar');
        $collector->collect($request, $response, new \Exception());
        $calls = $collector->getCalls();
        $this->assertStringStartsWith(sprintf(
            'curl %s -A',
            escapeshellarg('http://foo.bar')
        ), $calls[0]['curl']
        );

        $client->post('http://foo.bar', ['body' => str_pad('', GuzzleCollector::MAX_BODY_SIZE + 1)]);
        $collector->collect($request, $response, new \Exception());
        $calls = $collector->getCalls();
        $this->assertArrayNotHasKey('curl', $calls[1], 'This request body size shouldn\'t be passed to CurlFormatter');
    }
}
