<?php

namespace Csa\Bundle\GuzzleBundle\Tests\Factory;

use Csa\Bundle\GuzzleBundle\Factory\ClientFactory;

/**
 * ClientTest
 */
class ClientFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     */
    public function testCreateClient()
    {
        $factory = new ClientFactory('Csa\Bundle\GuzzleBundle\Factory\Client');
        $this->assertInstanceOf('Csa\Bundle\GuzzleBundle\Factory\Client', $factory->create());
    }
}
