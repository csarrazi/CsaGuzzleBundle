<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Csa\Bundle\GuzzleBundle\GuzzleHttp\Cache;

use Csa\Bundle\GuzzleBundle\Cache\StorageAdapterInterface as BaseAdapterInterface;

/**
 * Legacy doctrine adapter.
 *
 * @deprecated This interface is deprecated since version 2.1. It will be removed in version 3.0
 */
interface StorageAdapterInterface extends BaseAdapterInterface
{
}
