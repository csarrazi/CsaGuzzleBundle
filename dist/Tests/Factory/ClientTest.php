<?php

namespace Csa\Bundle\GuzzleBundle\Tests\Factory;

use Csa\Bundle\GuzzleBundle\Factory\Client;

/**
 * ClientTest
 */
class ClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     */
    public function testAddSubscriber()
    {
        $client = new Client();

        $subscriber = $this
            ->getMockBuilder('GuzzleHttp\Event\SubscriberInterface')
            ->setMethods(['getEvents'])
            ->getMock()
        ;
        $subscriber
            ->expects($this->once())
            ->method('getEvents')
            ->will($this->returnValue([]))
        ;

        $client->addSubscriber($subscriber);
    }
}
