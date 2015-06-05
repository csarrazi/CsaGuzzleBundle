<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Csa\Bundle\GuzzleBundle\Tests\GuzzleHttp\Subscriber;

use Csa\Bundle\GuzzleBundle\GuzzleHttp\Subscriber\CacheSubscriber;
use GuzzleHttp\Client;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Subscriber\Mock;

class DoctrineAdapterTest extends \PHPUnit_Framework_TestCase
{
    public function testFetch()
    {
        $response = new Response(204);
        $mocks = array_fill(0, 2, $response);

        $mockSubscriber = new Mock($mocks);
        $adapter = $this->getMock('Csa\Bundle\GuzzleBundle\GuzzleHttp\Cache\StorageAdapterInterface');
        $adapter
            ->expects($this->at(0))
            ->method('fetch')
            ->with($this->isInstanceOf('GuzzleHttp\Message\Request'))
            ->willReturn(false)
        ;
        $adapter
            ->expects($this->at(1))
            ->method('save')
            ->with(
                $this->isInstanceOf('GuzzleHttp\Message\RequestInterface'),
                $this->equalTo($response)
            )
        ;
        $adapter
            ->expects($this->at(2))
            ->method('fetch')
            ->with($this->isInstanceOf('GuzzleHttp\Message\RequestInterface'))
            ->willReturn($response)
        ;
        $cacheSubscriber = new CacheSubscriber($adapter);

        $client = new Client();
        $client->getEmitter()->attach($mockSubscriber);
        $client->getEmitter()->attach($cacheSubscriber);

        $client->get('http://foo.bar');

        $client->get('http://foo.bar');
    }
}
