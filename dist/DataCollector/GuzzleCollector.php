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

use Csa\Bundle\GuzzleBundle\GuzzleHttp\Subscriber\DebugSubscriber;
use GuzzleHttp\Stream\StreamInterface;
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

    private $history;
    private $maxBodySize;

    /**
     * Constructor
     *
     * @param DebugSubscriber $history the request history subscriber
     */
    public function __construct(DebugSubscriber $history, $maxBodySize = self::MAX_BODY_SIZE)
    {
        $this->history = $history;
        $this->maxBodySize = $maxBodySize;
        $this->data = [];
    }

    /**
     * {@inheritDoc}
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $data = [];

        foreach ($this->history as $transaction) {
            $request = $transaction['request'];
            $response = $transaction['response'];
            $error = $transaction['exception'];

            $req = [
                'request' => [
                    'method'  => $request->getMethod(),
                    'version' => $request->getProtocolVersion(),
                    'url'     => (string) $request->getUrl(),
                    'headers' => $request->getHeaders(),
                    'body'    => $this->cropContent($request->getBody()),
                ],
                'duration' => floor($transaction['duration'] * 1000),
            ];

            if ($response) {
                $req['response'] = [
                    'statusCode'   => $response->getStatusCode(),
                    'reasonPhrase' => $response->getReasonPhrase(),
                    'url'          => $response->getEffectiveUrl(),
                    'headers'      => $response->getHeaders(),
                    'body'         => $this->cropContent($response->getBody()),
                ];
            }

            if ($error) {
                $req['error'] = [
                    'message' => $error->getMessage(),
                    'line'    => $error->getLine(),
                    'file'    => $error->getFile(),
                    'code'    => $error->getCode(),
                    'trace'   => $error->getTraceAsString(),
                ];
            }


            if ($cache = $request->getConfig()->get('cache_lookup')) {
                $req['cache'] = $cache;
            }

            $data[] = $req;
        }

        $this->data = $data;
    }

    private function cropContent(StreamInterface $stream = null)
    {
        return (null === $stream || $stream->getSize() < $this->maxBodySize)
                ? (string) $stream
                : '(partial content)' . $stream->read($this->maxBodySize) . '(...)';
    }

    public function getErrors()
    {
        return array_filter($this->data, function ($call) {
            return !isset($call['response']) || $call['response']['statusCode'] >= 400;
        });
    }

    public function getCalls()
    {
        return $this->data;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'guzzle';
    }
}
