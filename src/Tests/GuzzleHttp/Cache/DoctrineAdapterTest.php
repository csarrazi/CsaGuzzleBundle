<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Csa\Bundle\GuzzleBundle\Tests\GuzzleHttp\Cache;

use Csa\Bundle\GuzzleBundle\GuzzleHttp\Cache\DoctrineAdapter;
use GuzzleHttp\Message\MessageParser;
use GuzzleHttp\Message\Request;

class DoctrineAdapterTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $cache = $this->getMock('Doctrine\Common\Cache\Cache');
        new DoctrineAdapter($cache, 0);
    }

    public function testFetch()
    {
        $httpResponse = file_get_contents(__DIR__.'/../../Fixtures/response.txt');
        $parser = new MessageParser();
        $cache = $this->getMock('Doctrine\Common\Cache\Cache');

        $cache
            ->expects($this->at(0))
            ->method('contains')
            ->willReturn(false)
        ;
        $cache
            ->expects($this->at(1))
            ->method('contains')
            ->willReturn(true)
        ;
        $cache
            ->expects($this->at(2))
            ->method('fetch')
            ->willReturn($httpResponse)
        ;
        $adapter = new DoctrineAdapter($cache, 0);

        $request = $this->getRequestMock();

        $this->assertNull($adapter->fetch($request));

        $response = $adapter->fetch($request);

        $data = $parser->parseResponse($httpResponse);

        $this->assertInstanceOf('GuzzleHttp\Message\ResponseInterface', $response);
        $this->assertEquals($data['code'], $response->getStatusCode());
        $this->assertSame($data['body'], (string) $response->getBody());

        foreach ($response->getHeaders() as $header => $value) {
            $this->assertSame($value[0], $data['headers'][$header]);
        }
    }

    public function testSave()
    {
        $cache = $this->getMock('Doctrine\Common\Cache\Cache');

        $cache
            ->expects($this->at(0))
            ->method('save')
            ->with(
                $this->isType('string'),
                $this->isType('string'),
                10
            );
        $adapter = new DoctrineAdapter($cache, 10);
        $this->assertNull($adapter->save($this->getRequestMock(), $this->getMock('GuzzleHttp\Message\ResponseInterface')));
    }

    private function getRequestMock()
    {
        return new Request('GET', 'http://google.com/', ['Accept' => 'text/html'], $this->getMock('GuzzleHttp\Stream\StreamInterface'));
    }
}
