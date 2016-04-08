<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Csa\Bundle\GuzzleBundle\Cache\NamingStrategy;

use Psr\Http\Message\RequestInterface;

interface NamingStrategyInterface
{
    public function filename(RequestInterface $request);
}
