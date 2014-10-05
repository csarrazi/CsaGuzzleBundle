<?php

namespace Csa\Bundle\GuzzleBundle\Tests\DataCollector;

use Csa\Bundle\GuzzleBundle\DataCollector\GuzzleCollector;
use Symfony\Component\HttpFoundation\Request;

class DataCollectorTest extends \PHPUnit_Framework_TestCase
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
            ->getMock();
        $request  = $this
            ->getMockBuilder('GuzzleHttp\Message\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $response = $this
            ->getMockBuilder('GuzzleHttp\Message\Response')
            ->disableOriginalConstructor()
            ->getMock();

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
