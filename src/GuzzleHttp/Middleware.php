<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Csa\Bundle\GuzzleBundle\GuzzleHttp;

use Csa\Bundle\GuzzleBundle\GuzzleHttp\Cache\StorageAdapterInterface;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\RejectedPromise;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Csa Guzzle Middleware.
 *
 * @author Charles Sarrazin <charles@sarraz.in>
 */
class Middleware
{
    public static function stopwatch(Stopwatch $stopwatch)
    {
        return function (callable $handler) use ($stopwatch) {
            return function (RequestInterface $request, array $options) use ($handler, $stopwatch) {
                $uri = (string) $request->getUri();

                if (!$stopwatch->isStarted($uri)) {
                    $stopwatch->start($uri);
                }

                return $handler($request, $options)->then(
                    function (ResponseInterface $response) use ($stopwatch, $uri) {
                        $stopwatch->stop($uri);

                        return $response;
                    }
                );
            };
        };
    }

    public static function cache(StorageAdapterInterface $adapter, $debug = false)
    {
        return function (callable $handler) use ($adapter, $debug) {
            return function (RequestInterface $request, array $options) use ($handler, $adapter, $debug) {
                if (!$response = $adapter->fetch($request)) {
                    return $handler($request, $options)->then(
                        function (ResponseInterface $response) use ($adapter, $request, $debug) {
                            $adapter->save($request, $response);

                            if ($debug) {
                                $response = $response->withHeader('X-Guzzle-Cache', 'MISS');
                            }

                            return $response;
                        }
                    );
                }

                if ($debug) {
                    $response = $response->withHeader('X-Guzzle-Cache', 'HIT');
                }

                return new FulfilledPromise($response);
            };
        };
    }

    public static function history(\SplObjectStorage $container)
    {
        return function (callable $handler) use ($container) {
            return function ($request, array $options) use ($handler, $container) {
                return $handler($request, $options)->then(
                    function ($value) use ($request, $container, $options) {
                        $info = isset($container[$request]) ? $container[$request]['info'] : null;
                        $container->attach($request, [
                            'response' => $value,
                            'error' => null,
                            'options' => $options,
                            'info' => $info,
                        ]);

                        return $value;
                    },
                    function ($reason) use ($request, $container, $options) {
                        $info = isset($container[$request]) ? $container[$request]['info'] : null;
                        $container->attach($request, [
                            'response' => null,
                            'error' => $reason,
                            'options' => $options,
                            'info' => $info,
                        ]);

                        return new RejectedPromise($reason);
                    }
                );
            };
        };
    }

    public static function mock(StorageAdapterInterface $storage, $mode, $debug = false)
    {
        return function (callable $handler) use ($mode, $storage, $debug) {
            return function (RequestInterface $request, array $options) use ($handler, $mode, $storage, $debug) {
                if ('record' === $mode) {
                    return $handler($request, $options)->then(
                        function (ResponseInterface $response) use ($request, $storage, $debug) {
                            $storage->save($request, $response);

                            if ($debug) {
                                $response = $response->withHeader('X-Guzzle-Mock', 'RECORD');
                            }

                            return $response;
                        }
                    );
                }

                try {
                    $response = $storage->fetch($request);

                    if ($debug) {
                        $response = $response->withHeader('X-Guzzle-Mock', 'REPLAY');
                    }
                } catch (\RuntimeException $e) {
                    return new RejectedPromise($e);
                }

                return new FulfilledPromise($response);
            };
        };
    }
}
