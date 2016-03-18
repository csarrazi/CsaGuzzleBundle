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

use GuzzleHttp\Psr7;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class MockStorageAdapter implements StorageAdapterInterface
{
    /**
     * @var string
     */
    private $storagePath;

    /**
     * @var array
     */
    private $requestHeadersBlacklist = [
        'User-Agent',
        'Host',
        'X-Guzzle-Cache',
    ];

    /**
     * @var array
     */
    private $responseHeadersBlacklist = [
        'X-Guzzle-Cache',
    ];

    /**
     * @param $storagePath
     * @param null|array $headersBlacklist
     */
    public function __construct($storagePath, array $requestHeadersBlacklist = [], array $responseHeadersBlacklist = [])
    {
        $this->storagePath = $storagePath;

        if (!empty($requestHeadersBlacklist)) {
            $this->requestHeadersBlacklist = $requestHeadersBlacklist;
        }

        if (!empty($responseHeadersBlacklist)) {
            $this->responseHeadersBlacklist = $responseHeadersBlacklist;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(RequestInterface $request)
    {
        $path = $this->getPath($request);
        if (!file_exists($path)) {
            throw new \RuntimeException('Record not found.');
        }

        return Psr7\parse_response(file_get_contents($path));
    }

    /**
     * {@inheritdoc}
     */
    public function save(RequestInterface $request, ResponseInterface $response)
    {
        foreach ($this->responseHeadersBlacklist as $header) {
            $response = $response->withoutHeader($header);
        }

        file_put_contents($this->getPath($request), Psr7\str($response));

        $response->getBody()->seek(0);
    }

    /**
     * Create a fingerprint for each request.
     *
     * As it is for mocking (and not for real caching), ignore some
     * characteristics like the 'User-Agent' to avoid stale cache
     * when updating PHP or Guzzle.
     *
     * @param RequestInterface $request
     * @param bool             $withHost
     *
     * @return string The path to the mock file
     */
    public function getPath(RequestInterface $request, $withHost = true)
    {
        $headers = $request->getHeaders();
        foreach ($headers as $name => $values) {
            if (in_array($name, $this->requestHeadersBlacklist)) {
                unset($headers[$name]);
            }
        }

        $fingerprint = md5(serialize([
            'method' => $request->getMethod(),
            'path' => $request->getUri()->getPath(),
            'query' => $request->getUri()->getQuery(),
            'user_info' => $request->getUri()->getUserInfo(),
            'port' => $request->getUri()->getPort(),
            'scheme' => $request->getUri()->getScheme(),
            'headers' => $headers,
        ]));

        if (true === $withHost) {
            $checksum = md5(
                $request->getUri()->getHost().ltrim($request->getUri()->getPath(), '/').'?'.$request->getUri()->getQuery()
            );
        } else {
            $checksum = md5(
                ltrim($request->getUri()->getPath(), '/').'?'.$request->getUri()->getQuery()
            );
        }

        $filename = sprintf('%s-%s.txt', substr($checksum, 0, 7), substr($fingerprint, 0, 7));

        return $this->storagePath.'/'.$filename;
    }
}
