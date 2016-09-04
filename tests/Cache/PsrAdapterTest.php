<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Csa\Bundle\GuzzleBundle\Tests\Cache;

use Csa\Bundle\GuzzleBundle\Cache\PsrAdapter;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class PsrAdapterTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $cache = $this->getMock(CacheItemPoolInterface::class);
        new PsrAdapter($cache, 0);
    }

    public function testFetch()
    {
        $cache = $this->getMock(CacheItemPoolInterface::class);
        $item = $this->getMock(CacheItemInterface::class);

        $item
            ->expects($this->at(0))
            ->method('isHit')
            ->willReturn(false)
        ;
        $item
            ->expects($this->at(1))
            ->method('isHit')
            ->willReturn(true)
        ;
        $item
            ->expects($this->at(2))
            ->method('get')
            ->willReturn([
                'status' => 200,
                'headers' => [],
                'body' => 'Hello World',
                'version' => '1.1',
                'reason' => 'OK',
            ])
        ;
        $cache
            ->expects($this->exactly(2))
            ->method('getItem')
            ->willReturn($item)
        ;
        $adapter = new PsrAdapter($cache, 0);

        $request = $this->getRequestMock();

        $this->assertNull($adapter->fetch($request));
        $this->assertInstanceOf(ResponseInterface::class, $adapter->fetch($request));
    }

    public function testSave()
    {
        $cache = $this->getMock(CacheItemPoolInterface::class);
        $item = $this->getMock(CacheItemInterface::class);

        $item
            ->expects($this->at(0))
            ->method('expiresAfter')
            ->with(10)
        ;
        $item
            ->expects($this->at(1))
            ->method('set')
            ->with([
                'status' => 200,
                'headers' => [],
                'body' => 'Hello World',
                'version' => '1.1',
                'reason' => 'OK',
            ])
        ;
        $cache
            ->expects($this->at(0))
            ->method('getItem')
            ->willReturn($item)
        ;
        $cache
            ->expects($this->at(1))
            ->method('save')
            ->with($item)
        ;
        $adapter = new PsrAdapter($cache, 10);
        $adapter->save($this->getRequestMock(), new Response(200, [], 'Hello World'));
    }

    private function getRequestMock()
    {
        return new Request('GET', 'http://google.com/', ['Accept' => 'text/html']);
    }
}
