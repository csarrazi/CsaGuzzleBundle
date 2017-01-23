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
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
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
    private $curlFormatter = null;

    /**
     * Constructor.
     *
     * @param int $maxBodySize The max body size to store in the profiler storage
     */
    public function __construct($maxBodySize = self::MAX_BODY_SIZE, History $history = null)
    {
        $this->maxBodySize = $maxBodySize;
        $this->history = $history ?: new History();

        if (class_exists(\Namshi\Cuzzle\Formatter\CurlFormatter::class)) {
            $this->curlFormatter = new \Namshi\Cuzzle\Formatter\CurlFormatter();
        }

        $this->data = [];
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $data = [];

        foreach ($this->history as $request) {
            /* @var RequestInterface $request */
            $transaction = $this->history[$request];
            /* @var ResponseInterface $response */
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
                'httpCode' => 0,
                'error' => null,
            ];

            if ($this->curlFormatter) {
                $req['curl'] = $this->curlFormatter->format($request);
            }

            if ($response instanceof ResponseInterface) {
                $req['response'] = [
                    'reasonPhrase' => $response->getReasonPhrase(),
                    'headers' => $response->getHeaders(),
                    'body' => $this->cropContent($response->getBody()),
                ];

                $req['httpCode'] = $response->getStatusCode();

                if ($response->hasHeader(CacheMiddleware::DEBUG_HEADER)) {
                    $req['cache'] = $response->getHeaderLine(CacheMiddleware::DEBUG_HEADER);
                }

                if ($response->hasHeader(MockMiddleware::DEBUG_HEADER)) {
                    $req['mock'] = $response->getHeaderLine(MockMiddleware::DEBUG_HEADER);
                }
            }

            if ($error && $error instanceof RequestException) {
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
            return 0 === $call['httpCode'] || $call['httpCode'] >= 400;
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

    /**
     * @deprecated This method is deprecated since version 2.2. It will be removed in version 3.0
     */
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
