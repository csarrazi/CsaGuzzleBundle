<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Csa\Bundle\GuzzleBundle\HttpFoundation;

use Doctrine\Common\Cache\Cache;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

class StreamResponse extends Response
{
    const BUFFER_SIZE = 4096;

    private $bufferSize;

    public function __construct(ResponseInterface $response, $bufferSize = self::BUFFER_SIZE)
    {
        parent::__construct(null, $response->getStatusCode(), $response->getHeaders());

        $this->content = $response->getBody();
        $this->bufferSize = $bufferSize;
    }

    public function sendContent()
    {
        $chunked = $this->headers->has('Transfer-Encoding');
        $this->content->seek(0);

        while (!$this->content->eof()) {
            $chunk = $this->content->read($this->bufferSize);

            if ($chunked) {
                echo sprintf("%x\r\n", strlen($chunk));
            }

            echo $chunk;

            if ($chunked) {
                echo "\r\n";
            }

            flush();
        }

        if ($chunked) {
            echo sprintf("%x\r\n\r\n", 0);
            flush();
        }
    }

    public function getContent()
    {
        return false;
    }
}
