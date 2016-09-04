<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Csa\Bundle\GuzzleBundle\Tests\GuzzleHttp\Cache;

use Csa\Bundle\GuzzleBundle\GuzzleHttp\Cache\MockStorageAdapter;
use Csa\Bundle\GuzzleBundle\Tests\Cache\MockStorageAdapterTest as BaseTest;

class MockStorageAdapterTest extends BaseTest
{
    protected $class = MockStorageAdapter::class;
}
