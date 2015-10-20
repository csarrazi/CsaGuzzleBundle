<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Csa\Bundle\GuzzleBundle\GuzzleHttp;

use GuzzleHttp\Client;
use Psr\Http\Message\RequestInterface;

class ClientSerializerAdapter extends Client
{
    /**
     * @var string
     */
    private $jsonRegex = '(text/javascript)|(application/([^+]*\+){0,1}json)';

    /**
     * @var string
     */
    private $xmlRegex = 'application/([^+]*\+){0,1}xml';

    /**
     * @param RequestInterface $request
     * @param array $options
     *
     * @return mixed
     */
    public function sendAsync(RequestInterface $request, array $options = [])
    {
        $options = $this->prepareDefaults($options);
        $response = parent::sendAsync($request, $options)->wait();

        return $this->processResponse($response, $options);
    }

    /**
     * @param string $method
     * @param null|string $uri
     * @param array $options
     *
     * @return mixed
     */
    public function requestAsync($method, $uri = null, array $options = [])
    {
        $options = $this->prepareDefaults($options);
        $response = parent::requestAsync($method, $uri, $options)->wait();

        return $this->processResponse($response, $options);
    }

    /**
     * Hook into the defaults options that cannot be changed by middleware.
     *
     * @param array $options
     *
     * @return array
     */
    private function prepareDefaults(array $options = [])
    {
        $serializerAdapter = $this->getConfig('serializer_adapter');

        if ($serializerAdapter) {
            $format = false;
            $contentType = '';
            if (isset($options['json'])) {
                $format = 'json';
                $contentType = 'application/json';
            } elseif (isset($options['xml'])) {
                $format = 'xml';
                $contentType = 'application/xml';
            }

            if ($format) {
                $content = $serializerAdapter->serialize(
                    $options[$format],
                    $format,
                    (!empty($options['serialization']['serialize_context'])) ?
                        $options['serialization']['serialize_context'] : null
                );
                if (function_exists('\GuzzleHttp\Psr7\stream_for')) {
                    $content = \GuzzleHttp\Psr7\stream_for($content);
                }
                $options['body'] = $content;
                $options['_conditional']['Content-Type'] = $contentType;
                unset($options[$format]);
            }
        }

        return $options;
    }

    /**
     * Try to deserialize the response.
     *
     * @param mixed $response
     * @param array $options
     *
     * @return mixed
     */
    private function processResponse($response, $options = [])
    {
        $serializerAdapter = $this->getConfig('serializer_adapter');

        if ($response->hasHeader('Content-Type') && $serializerAdapter) {
            $contentType = $response->getHeader('Content-Type')[0];

            $format = false;
            if (preg_match('#'.$this->jsonRegex.'#', $contentType)) {
                $format = 'json';
            } elseif (preg_match('#'.$this->xmlRegex.'#', $contentType)) {
                $format = 'xml';
            }

            if ($format) {
                if (empty($options['serialization']['type'])) {
                    throw new \LogicException(
                        'You must provide the client option "serialization" to define the '
                        . 'targeted type and context for deserialization.'
                    );
                }

                return $serializerAdapter->deserialize(
                    $response->getBody(),
                    $options['serialization']['type'],
                    $format,
                    (!empty($options['serialization']['deserialize_context'])) ?
                        $options['serialization']['deserialize_context'] : null
                );
            }
        }

        return $response;
    }
}
