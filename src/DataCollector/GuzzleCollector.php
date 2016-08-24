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

use Csa\Bundle\GuzzleBundle\GuzzleHttp\Middleware\HistoryMiddleware;
use GuzzleHttp\TransferStats;
use Namshi\Cuzzle\Formatter\CurlFormatter;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Csa Guzzle Collector.
 *
 * @author Charles Sarrazin <charles@sarraz.in>
 */
class GuzzleCollector extends DataCollector
{
    const MAX_BODY_SIZE = 0x10000;

    private $maxBodySize;
    private $history;
    private $curlFormatter;

    /**
     * Constructor.
     *
     * @param int $maxBodySize The max body size to store in the profiler storage
     */
    public function __construct($maxBodySize = self::MAX_BODY_SIZE)
    {
        $this->maxBodySize = $maxBodySize;
        $this->history = new \ArrayObject();
        $this->curlFormatter = new CurlFormatter();
        $this->data = [];
    }

    public function addStats(TransferStats $stats)
    {
        $request = $stats->getRequest();
        if (!$request->hasHeader(HistoryMiddleware::CORRELATION_ID_HEADER)) {
            $correlationId = uniqid();
        } else {
            $correlationId = $request->getHeader(HistoryMiddleware::CORRELATION_ID_HEADER)[0];
        }

        if (!isset($this->history[$correlationId])) {
            $this->history[$correlationId] = [
                'request' => $request,
                'response' => $stats->getResponse(),
                'options' => null,
                'error' => $stats->getHandlerErrorData(),
                'info' => $stats->getHandlerStats(),
            ];

            return;
        }

        $this->history[$correlationId]['request'] = $request;
        $this->history[$correlationId]['info'] = $stats->getHandlerStats();
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $data = [];

        foreach ($this->history as $transaction) {
            /* @var \Psr\Http\Message\RequestInterface $request */
            $request = $transaction['request'];
            /* @var \Psr\Http\Message\ResponseInterface $response */
            $response = $transaction['response'];
            /* @var \Exception $error */
            $error = $transaction['error'];
            /* @var array $info */
            $info = $transaction['info'];

            $req = [
                'request' => [
                    'method' => $request->getMethod(),
                    'version' => $request->getProtocolVersion(),
                    'headers' => $request->getHeaders(),
                    'body' => $this->cropContent($request->getBody()),
                ],
                'info' => $info,
                'uri' => urldecode($request->getUri()),
                'curl' => $this->curlFormatter->format($request),
                'httpCode' => 0,
            ];

            if ($response) {
                $req['response'] = [
                    'reasonPhrase' => $response->getReasonPhrase(),
                    'headers' => $response->getHeaders(),
                    'body' => $this->cropContent($response->getBody()),
                ];

                $req['httpCode'] = $response->getStatusCode();

                if ($response->hasHeader('X-Guzzle-Cache')) {
                    $req['cache'] = $response->getHeaderLine('X-Guzzle-Cache');
                }

                if ($response->hasHeader('X-Guzzle-Mock')) {
                    $req['mock'] = $response->getHeaderLine('X-Guzzle-Mock');
                }
            }

            if ($error) {
                $req['error'] = [
                    'message' => $error->getMessage(),
                    'line' => $error->getLine(),
                    'file' => $error->getFile(),
                    'code' => $error->getCode(),
                    'trace' => $error->getTraceAsString(),
                ];
            }

            $data[] = $req;
        }

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

        return '(partial content)'.$stream->read($this->maxBodySize).'(...)';
    }

    public function getErrors()
    {
        return array_filter($this->data, function ($call) {
            return isset($call['httpCode']) && $call['httpCode'] >= 400;
        });
    }

    public function getTotalTime()
    {
        return array_sum(
            array_map(
                function ($call) {
                    return isset($call['info']['total_time']) ? $call['info']['total_time'] : 0;
                },
                $this->data
            )
        );
    }

    public function getCalls()
    {
        return $this->data;
    }

    public function getHistory()
    {
        return $this->history;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'guzzle';
    }
}
