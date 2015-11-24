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

use Doctrine\Common\Cache\Cache;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class DoctrineAdapter implements StorageAdapterInterface
{
    private $cache;
    private $ttl;

    public function __construct(Cache $cache, $ttl = 0)
    {
        $this->cache = $cache;
        $this->ttl = $ttl;
    }

    public function fetch(RequestInterface $request)
    {
        $key = $this->getKey($request);

        if ($this->cache->contains($key)) {
            $data = $this->cache->fetch($key);

            return new Response($data['status'], $data['headers'], $data['body'], $data['version'], $data['reason']);
        }
    }

    public function save(RequestInterface $request, ResponseInterface $response)
    {
        $data = [
            'status' => $response->getStatusCode(),
            'headers' => $response->getHeaders(),
            'body' => (string) $response->getBody(),
            'version' => $response->getProtocolVersion(),
            'reason' => $response->getReasonPhrase(),
        ];

        $this->cache->save($this->getKey($request), $data, $this->ttl);
    }

    private function getKey(RequestInterface $request)
    {
        return md5(serialize([
            'method' => $request->getMethod(),
            'uri' => $request->getUri(),
            'headers' => $request->getHeaders(),
        ]));
    }
}
