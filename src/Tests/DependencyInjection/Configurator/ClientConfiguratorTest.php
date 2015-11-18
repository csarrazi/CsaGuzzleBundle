<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Csa\Bundle\GuzzleBundle\Tests\DependencyInjection\Configurator;

use Csa\Bundle\GuzzleBundle\DependencyInjection\Configurator\ClientConfigurator;
use GuzzleHttp\ClientInterface;

class ClientConfiguratorTest extends \PHPUnit_Framework_TestCase
{
    public function testSubscribersAttachedToEmitter()
    {
        $subscriber = $this->getMockSubscriber();

        $emitter = $this->getMockEmitter();
        $emitter
            ->expects($this->once())
            ->method('attach')
            ->with($this->identicalTo($subscriber))
        ;

        $client = $this->getMockClient();
        $client->method('getEmitter')->willReturn($emitter);

        $configurator = new ClientConfigurator([$subscriber]);
        $configurator->configure($client);
    }

    public function testParentConfiguratorCalled()
    {
        $parentCalled = false;
        $parent = function (ClientInterface $client) use (&$parentCalled) {
            $parentCalled = true;
        };

        $configurator = new ClientConfigurator([], $parent);
        $configurator->configure($this->getMockClient());

        $this->assertTrue($parentCalled, 'Parent configuration must be called');
    }

    private function getMockClient()
    {
        return $this->getMock('GuzzleHttp\ClientInterface');
    }

    private function getMockEmitter()
    {
        return $this->getMock('GuzzleHttp\Event\EmitterInterface');
    }

    private function getMockSubscriber()
    {
        return $this->getMock('GuzzleHttp\Event\SubscriberInterface');
    }
}
