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
use Csa\Bundle\GuzzleBundle\GuzzleHttp\Middleware;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request as Psr7Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\TransferStats;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers Csa\Bundle\GuzzleBundle\DataCollector\GuzzleCollector
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
        $response = $this->getMock('Symfony\Component\HttpFoundation\Response');
        $collector->collect($request, $response, new \Exception());
        $this->assertCount(0, $collector->getCalls());

        $client->get('http://foo.bar');
        $collector->collect($request, $response, new \Exception());
        $calls = $collector->getCalls();
        $this->assertCount(1, $calls);
        $this->assertStringStartsWith(sprintf(
            'curl %s -A',
            escapeshellarg('http://foo.bar')
            ), $calls[0]['curl']
        );

        $client->get('http://foo.bar');
        $collector->collect($request, $response, new \Exception());
        $this->assertCount(2, $collector->getCalls());
    }

    public function testAddStatsFromUncorrelatedRequest()
    {
        $request = new Psr7Request('GET', '/');
        $collector = new GuzzleCollector();

        $response = new Response();
        $info = [uniqid()];
        $stats = new TransferStats($request, $response, null, null, $info);
        $collector->addStats($stats);

        $history = array_values((array) $collector->getHistory());

        $this->assertCount(1, $history);
        $this->assertArraySubset(['info' => $info, 'response' => $response], $history[0]);
    }

    public function testAddStatsFromKnownRequest()
    {
        $request = new Psr7Request('GET', '/', ['csa-guzzle-correlation-id' => 'random_id']);
        $collector = new GuzzleCollector();
        $collector->getHistory()['random_id'] = ['info' => null];

        $info = [uniqid()];
        $collector->addStats(new TransferStats($request, null, null, null, $info));

        $history = array_values((array) $collector->getHistory());

        $this->assertCount(1, $history);
        $this->assertArraySubset(['info' => $info], $history[0]);
    }
}
