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

use Psr\Http\Message\RequestInterface;
use Tolerance\Bridge\Guzzle\ToleranceMiddleware as RealToleranceMiddleware;
use Tolerance\Operation\ExceptionCatcher\ThrowableCatcherVoter;

/**
 * Tolerance Middleware.
 *
 * @author Luc Vieillescazes <luc@vieillescazes.net>
 */
class ToleranceMiddleware
{
    /**
     * @var callable
     */
    private $waiterFactory;

    /**
     * @var ThrowableCatcherVoter
     */
    private $errorVoter;

    public function __construct(callable $waiterFactory, ThrowableCatcherVoter $errorVoter)
    {
        $this->waiterFactory = $waiterFactory;
        $this->errorVoter = $errorVoter;
    }

    public function __invoke(callable $nextHandler)
    {
        $middleware = new RealToleranceMiddleware($nextHandler, $this->waiterFactory, $this->errorVoter);

        return function (RequestInterface $request, array $options) use ($middleware) {
            return $middleware($request, $options);
        };
    }
}
