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

use Csa\Bundle\GuzzleBundle\GuzzleHttp\Cache\DoctrineAdapter;
use Csa\Bundle\GuzzleBundle\Tests\Cache\DoctrineAdapterTest as BaseTest;

class DoctrineAdapterTest extends BaseTest
{
    protected $class = DoctrineAdapter::class;
}
