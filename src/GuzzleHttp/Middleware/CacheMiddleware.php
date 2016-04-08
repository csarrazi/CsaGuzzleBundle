<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Csa\Bundle\GuzzleBundle\GuzzleHttp\Middleware;

use Csa\Bundle\GuzzleBundle\Cache\StorageAdapterInterface;
use GuzzleHttp\Promise\FulfilledPromise;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Cache Middleware.
 *
 * @author Charles Sarrazin <charles@sarraz.in>
 */
class CacheMiddleware
{
    const DEBUG_HEADER = 'X-Guzzle-Cache';
    const DEBUG_HEADER_HIT = 'HIT';
    const DEBUG_HEADER_MISS = 'MISS';

    protected $adapter;
    protected $debug;

    public function __construct(StorageAdapterInterface $adapter, $debug = false)
    {
        $this->adapter = $adapter;
        $this->debug = $debug;
    }

    public function __invoke(callable $handler)
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            if (!$response = $this->adapter->fetch($request)) {
                return $this->handleSave($handler, $request, $options);
            }

            $response = $this->addDebugHeader($response, static::DEBUG_HEADER_HIT);

            return new FulfilledPromise($response);
        };
    }

    protected function handleSave(callable $handler, RequestInterface $request, array $options)
    {
        return $handler($request, $options)->then(
            function (ResponseInterface $response) use ($request) {
                $this->adapter->save($request, $response);

                return $this->addDebugHeader($response, static::DEBUG_HEADER_MISS);
            }
        );
    }

    protected function addDebugHeader(ResponseInterface $response, $value)
    {
        if (!$this->debug) {
            return $response;
        }

        return $response->withHeader(static::DEBUG_HEADER, $value);
    }
}
