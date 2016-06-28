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

use GuzzleHttp\Promise\RejectedPromise;
use Psr\Http\Message\RequestInterface;

/**
 * History Middleware.
 *
 * @author Charles Sarrazin <charles@sarraz.in>
 */
class HistoryMiddleware
{
    private $container;

    public function __construct(\ArrayObject $container)
    {
        $this->container = $container;
    }

    public function __invoke(callable $handler)
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            $correlationId = uniqid();
            $request = $request->withAddedHeader('csa-guzzle-correlation-id', $correlationId);
            $this->container[$correlationId] = [
                'request' => $request,
                'response' => null,
                'options' => $options,
                'error' => null,
                'info' => [],
            ];

            return $handler($request, $options)->then(
                function ($value) use ($correlationId) {
                    $this->container[$correlationId]['response'] = $value;

                    return $value;
                },
                function ($reason) use ($correlationId) {
                    $this->container[$correlationId]['response'] = $reason;

                    return new RejectedPromise($reason);
                }
            );
        };
    }
}
