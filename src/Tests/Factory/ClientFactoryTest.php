<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Csa\Bundle\GuzzleBundle\Tests\Factory;

use Csa\Bundle\GuzzleBundle\Factory\ClientFactory;

/**
 * ClientTest.
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
