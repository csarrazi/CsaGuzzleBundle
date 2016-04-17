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
    private $curlFormatter;

    /**
     * Constructor.
     *
     * @param int $maxBodySize The max body size to store in the profiler storage
     */
    public function __construct($maxBodySize = self::MAX_BODY_SIZE)
    {
        $this->maxBodySize = $maxBodySize;
        $this->history = new \SplObjectStorage();
        $this->curlFormatter = new CurlFormatter();
        $this->data = [];
    }

    public function addStats(TransferStats $stats)
    {
        $request = $stats->getRequest();
        $data = isset($this->history[$request]) ? $this->history[$request] : [];
        $data['info'] = $stats->getHandlerStats();
        $this->history->attach($request, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $data = [];

        /** @var RequestInterface $request */
        foreach ($this->history as $request) {
            $transaction = $this->history[$request];
            /** @var ResponseInterface $response */
            $response = $transaction['response'];
            $error = $transaction['error'];
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
            ];

            if ($response) {
                $req['response'] = [
                    'reasonPhrase' => $response->getReasonPhrase(),
                    'headers' => $response->getHeaders(),
                    'body' => $this->cropContent($response->getBody()),
                ];
            }

            $req['httpCode'] = $response ? $response->getStatusCode() : 0;

            if ($error) {
                $req['error'] = [
                    'message' => $error->getMessage(),
                    'line' => $error->getLine(),
                    'file' => $error->getFile(),
                    'code' => $error->getCode(),
                    'trace' => $error->getTraceAsString(),
                ];
            }

            if ($response && $response->hasHeader('X-Guzzle-Cache')) {
                $req['cache'] = $response->getHeaderLine('X-Guzzle-Cache');
            }

            if ($response && $response->hasHeader('X-Guzzle-Mock')) {
                $req['mock'] = $response->getHeaderLine('X-Guzzle-Mock');
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
