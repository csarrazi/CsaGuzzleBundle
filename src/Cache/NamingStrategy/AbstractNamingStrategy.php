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

use Csa\Bundle\GuzzleBundle\GuzzleHttp\Middleware\CacheMiddleware;
use Psr\Http\Message\RequestInterface;

abstract class AbstractNamingStrategy implements NamingStrategyInterface
{
    private $blacklist = [
        'User-Agent',
        'Host',
        CacheMiddleware::DEBUG_HEADER,
    ];

    public function __construct(array $blacklist = [])
    {
        if (!empty($blacklist)) {
            $this->blacklist = $blacklist;
        }
    }

    /**
     * Generates a fingerprint from a given request.
     *
     * @param RequestInterface $request
     *
     * @return string
     */
    protected function getFingerprint(RequestInterface $request)
    {
        return md5(serialize([
            'method' => $request->getMethod(),
            'path' => $request->getUri()->getPath(),
            'query' => $request->getUri()->getQuery(),
            'user_info' => $request->getUri()->getUserInfo(),
            'port' => $request->getUri()->getPort(),
            'scheme' => $request->getUri()->getScheme(),
            'headers' => array_diff_key($request->getHeaders(), array_flip($this->blacklist)),
        ]));
    }
}
