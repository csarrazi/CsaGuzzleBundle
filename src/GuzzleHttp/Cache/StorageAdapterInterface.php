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

use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\ResponseInterface;

interface StorageAdapterInterface
{
    public function fetch(RequestInterface $request);

    public function save(RequestInterface $request, ResponseInterface $response);
}
