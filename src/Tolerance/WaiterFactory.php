<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Csa\Bundle\GuzzleBundle\Tolerance;

use Tolerance\Waiter\CountLimited;
use Tolerance\Waiter\ExponentialBackOff;
use Tolerance\Waiter\SleepWaiter;

/**
 * Waiter Factory.
 *
 * @author Luc Vieillescazes <luc@vieillescazes.net>
 */
class WaiterFactory
{
    private $retry;

    public function __construct($retry)
    {
        $this->retry = (int) $retry;
    }

    public function create()
    {
        return new CountLimited(
            new ExponentialBackOff(
                new SleepWaiter(),
                1
            ),
            $this->retry
        );
    }
}
