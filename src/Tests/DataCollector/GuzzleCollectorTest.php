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
use Csa\Bundle\GuzzleBundle\GuzzleHttp\Subscriber\DebugSubscriber;
use GuzzleHttp\Client;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Subscriber\Mock;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \Csa\Bundle\GuzzleBundle\DataCollector\GuzzleCollector
 */
class GuzzleCollectorTest extends \PHPUnit_Framework_TestCase
{
    public function testCollect()
    {
        $mocks = array_fill(0, 3, new Response(204));

        $mockSubscriber = new Mock($mocks);
        $client = new Client();
        $client->getEmitter()->attach($mockSubscriber);
        $debugSubscriber = new DebugSubscriber();
        $client->getEmitter()->attach($debugSubscriber);
        $collector = new GuzzleCollector($debugSubscriber);

        $request = Request::createFromGlobals();
        $response = $this->getMock('Symfony\Component\HttpFoundation\Response');
        $collector->collect($request, $response, new \Exception());
        $this->assertCount(0, $collector->getCalls());

        $client->get('http://foo.bar');
        $collector->collect($request, $response, new \Exception());
        $this->assertCount(1, $collector->getCalls());

        $client->get('http://foo.bar');
        $collector->collect($request, $response, new \Exception());
        $this->assertCount(2, $collector->getCalls());
    }
}
