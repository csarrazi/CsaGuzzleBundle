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

use Csa\Bundle\GuzzleBundle\Tolerance\WaiterFactory;
use Psr\Http\Message\RequestInterface;
use Tolerance\Operation\ExceptionCatcher\ThrowableCatcherVoter;
use Tolerance\Operation\PromiseOperation;
use Tolerance\Operation\Runner\RetryPromiseOperationRunner;

/**
 * Tolerance Middleware.
 *
 * @author Luc Vieillescazes <luc@vieillescazes.net>
 */
class ToleranceMiddleware
{
    /**
     * @var WaiterFactory
     */
    private $waiterFactory;

    /**
     * @var ThrowableCatcherVoter
     */
    private $errorVoter;

    public function __construct(WaiterFactory $waiterFactory, ThrowableCatcherVoter $errorVoter)
    {
        $this->waiterFactory = $waiterFactory;
        $this->errorVoter = $errorVoter;
    }

    public function __invoke(callable $nextHandler)
    {
        return function (RequestInterface $request, array $options) use ($nextHandler) {
            $operation = new PromiseOperation(function () use ($nextHandler, $request, $options) {
                return $nextHandler($request, $options);
            });
            $runner = new RetryPromiseOperationRunner($this->waiterFactory->create(), $this->errorVoter);

            return $runner->run($operation);
        };
    }
}
