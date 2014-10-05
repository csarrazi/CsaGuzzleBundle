<?php

namespace Csa\Bundle\GuzzleBundle\Tests\DataCollector;

use Csa\Bundle\GuzzleBundle\DataCollector\GuzzleCollector;
use Symfony\Component\HttpFoundation\Request;

class GuzzleCollectorTest extends \PHPUnit_Framework_TestCase
{
    public function testCollect()
    {
        $history = $this->getHistoryMock();
        $collector = new GuzzleCollector($history);

        $request = Request::createFromGlobals();
        $response = $this->getMock('Symfony\Component\HttpFoundation\Response');
        $collector->collect($request, $response, new \Exception());
        $this->assertCount(1, $collector->getCalls());
    }

    private function getHistoryMock()
    {
        $history  = $this
            ->getMockBuilder('Csa\Bundle\GuzzleBundle\GuzzleHttp\Subscriber\DebugSubscriber')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $request  = $this
            ->getMockBuilder('GuzzleHttp\Message\Request')
            ->disableOriginalConstructor()
            ->setMethods(['getMethod', 'getProtocolVersion', 'getUrl', 'getHeaders', 'getBody'])
            ->getMock()
        ;

        $request->expects($this->once())->method('getMethod');
        $request->expects($this->once())->method('getProtocolVersion');
        $request->expects($this->once())->method('getUrl');
        $request->expects($this->once())->method('getHeaders');
        $request->expects($this->once())->method('getBody');

        $response = $this
            ->getMockBuilder('GuzzleHttp\Message\Response')
            ->disableOriginalConstructor()
            ->setMethods(['getStatusCode', 'getReasonPhrase', 'getEffectiveUrl', 'getHeaders', 'getBody'])
            ->getMock()
        ;

        $response->expects($this->once())->method('getStatusCode');
        $response->expects($this->once())->method('getReasonPhrase');
        $response->expects($this->once())->method('getEffectiveUrl');
        $response->expects($this->once())->method('getHeaders');
        $response->expects($this->once())->method('getBody');

        $history
            ->expects($this->once())
            ->method('getIterator')
            ->will($this->returnValue(new \ArrayIterator([
                [
                    'request'  => $request,
                    'response' => $response,
                    'duration' => 100,
                ]
            ])))
        ;

        return $history;
    }
}
