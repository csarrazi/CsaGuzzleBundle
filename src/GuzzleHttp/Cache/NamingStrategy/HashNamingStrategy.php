<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Csa\Bundle\GuzzleBundle\GuzzleHttp\Cache\NamingStrategy;

use Psr\Http\Message\RequestInterface;

class HashNamingStrategy implements NamingStrategyInterface
{
    /**
     * {@inheritdoc}
     */
    public function filename(RequestInterface $request)
    {
        return md5(serialize([
            'method' => $request->getMethod(),
            'uri' => $request->getUri(),
            'headers' => $request->getHeaders(),
        ]));
    }
}
