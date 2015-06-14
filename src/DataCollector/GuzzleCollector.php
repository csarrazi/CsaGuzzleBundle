<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Csa\Bundle\GuzzleBundle\DataCollector;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Csa Guzzle Collector
 *
 * @author Charles Sarrazin <charles@sarraz.in>
 */
class GuzzleCollector extends DataCollector
{
    const MAX_BODY_SIZE = 0x10000;

    private $maxBodySize;
    private $history;

    /**
     * Constructor
     *
     * @param int $maxBodySize The max body size to store in the profiler storage
     */
    public function __construct($maxBodySize = self::MAX_BODY_SIZE)
    {
        $this->maxBodySize = $maxBodySize;
        $this->history = [];
        $this->data = [];
    }

    /**
     * {@inheritDoc}
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $data = array_map(function ($transaction) {
            /** @var RequestInterface $request */
            $request = $transaction['request'];
            /** @var ResponseInterface $response */
            $response = $transaction['response'];
            $error = $transaction['error'];
            $info = [];

            $req = [
                'request' => [
                    'method'  => $request->getMethod(),
                    'version' => $request->getProtocolVersion(),
                    'headers' => $request->getHeaders(),
                    'body'    => $this->cropContent($request->getBody()),
                ],
                'info' => $info,
                'uri'     => $request->getUri(),
            ];

            if ($response) {
                $req['response'] = [
                    'reasonPhrase' => $response->getReasonPhrase(),
                    'headers'      => $response->getHeaders(),
                    'body'         => $this->cropContent($response->getBody()),
                ];
            }

            $req['httpCode'] = $response ? $response->getStatusCode() : 0;

            if ($error) {
                $req['error'] = [
                    'message' => $error->getMessage(),
                    'line'    => $error->getLine(),
                    'file'    => $error->getFile(),
                    'code'    => $error->getCode(),
                    'trace'   => $error->getTraceAsString(),
                ];
            }

            if ($response->hasHeader('X-Guzzle-Cache')) {
                $req['cache'] = $response->getHeaderLine('X-Guzzle-Cache');
            }

            return $req;
        }, $this->history);

        $this->data = $data;
    }

    private function cropContent(StreamInterface $stream = null)
    {
        if (null === $stream) {
            return '';
        }

        if ($stream->getSize() <= $this->maxBodySize) {
            return (string) $stream;
        }

        $stream->seek(0);

        return '(partial content)' . $stream->read($this->maxBodySize) . '(...)';
    }

    public function getErrors()
    {
        return array_filter($this->data, function ($call) {
            return isset($call['httpCode']) && $call['httpCode'] >= 400;
        });
    }

    public function getCalls()
    {
        return $this->data;
    }

    public function &getHistory()
    {
        return $this->history;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'guzzle';
    }
}
