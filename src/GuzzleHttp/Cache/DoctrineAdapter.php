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
use GuzzleHttp\Message\MessageFactory;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\ResponseInterface;

class DoctrineAdapter implements StorageAdapterInterface
{
    private $cache;
    private $ttl;
    private $messageFactory;

    public function __construct(Cache $cache, $ttl = 0)
    {
        $this->cache = $cache;
        $this->ttl = $ttl;
    }

    public function fetch(RequestInterface $request)
    {
        $key = $this->getKey($request);

        if ($this->cache->contains($key)) {
            return $this->getMessageFactory()->fromMessage($this->cache->fetch($key));
        }
    }

    public function save(RequestInterface $request, ResponseInterface $response)
    {
        $this->cache->save($this->getKey($request), (string) $response, $this->ttl);
    }

    private function getMessageFactory() {
        if (null === $this->messageFactory) {
            $this->messageFactory = new MessageFactory();
        }

        return $this->messageFactory;
    }
    private function getKey(RequestInterface $request)
    {
        return md5(serialize([
            'method'  => $request->getMethod(),
            'uri'     => $request->getUrl(),
            'headers' => $request->getHeaders(),
            'body'    => (string) $request->getBody(),
        ]));
    }
}
