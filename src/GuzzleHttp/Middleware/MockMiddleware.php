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
use GuzzleHttp\Promise\RejectedPromise;
use Psr\Http\Message\RequestInterface;

/**
 * Mock Middleware.
 *
 * @author Charles Sarrazin <charles@sarraz.in>
 */
class MockMiddleware extends CacheMiddleware
{
    const DEBUG_HEADER = 'X-Guzzle-Mock';
    const DEBUG_HEADER_HIT = 'REPLAY';
    const DEBUG_HEADER_MISS = 'RECORD';

    private $mode;

    public function __construct(StorageAdapterInterface $adapter, $mode, $debug = false)
    {
        parent::__construct($adapter, $debug);

        $this->mode = $mode;
    }

    public function __invoke(callable $handler)
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            if ('record' === $this->mode) {
                return $this->handleSave($handler, $request, $options);
            }

            try {
                if (null === $response = $this->adapter->fetch($request)) {
                    throw new \RuntimeException('Record not found.');
                }

                $response = $this->addDebugHeader($response, 'REPLAY');
            } catch (\RuntimeException $e) {
                return new RejectedPromise($e);
            }

            return new FulfilledPromise($response);
        };
    }
}
