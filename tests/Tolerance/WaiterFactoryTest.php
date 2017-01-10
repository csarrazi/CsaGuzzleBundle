<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Csa\Bundle\GuzzleBundle\Tests\Tolerance;

use Csa\Bundle\GuzzleBundle\Tolerance\WaiterFactory;
use Tolerance\Waiter\CountLimited;

class WaiterFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testWaiterFactory()
    {
        $waiterFactory = new WaiterFactory(2);
        $waiter = $waiterFactory->create();

        $this->assertInstanceOf(CountLimited::class, $waiter);
    }
}
