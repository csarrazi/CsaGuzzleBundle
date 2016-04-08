<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Csa\Bundle\GuzzleBundle\Cache;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface StorageAdapterInterface
{
    /**
     * @param RequestInterface $request
     *
     * @return null|ResponseInterface
     */
    public function fetch(RequestInterface $request);

    /**
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     */
    public function save(RequestInterface $request, ResponseInterface $response);
}
