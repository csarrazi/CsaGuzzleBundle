<?php

namespace Csa\Bundle\GuzzleBundle\Tests\Factory;

use Csa\Bundle\GuzzleBundle\Factory\ClientFactory;

/**
 * ClientTest
 *
 * @group legacy
 */
class ClientFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateClient()
    {
        $factory = new ClientFactory('GuzzleHttp\Client');
        $this->assertInstanceOf('GuzzleHttp\Client', $factory->create());
    }
}
