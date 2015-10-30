<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Csa\Bundle\GuzzleBundle\Tests\GuzzleHttp;

use Csa\Bundle\GuzzleBundle\GuzzleHttp\ClientSerializerAdapter;

class ClientSerializerAdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getDefaultsData
     */
    public function testPrepareDefaults($data, $serialized, $format, $contentType)
    {
        $serializer = $this->getMock('Csa\Bundle\GuzzleBundle\Serializer\SerializerAdapterInterface');
        $serializer
            ->expects($this->once())
            ->method('serialize')
            ->with(
                $this->equalTo($data),
                $this->equalTo($format),
                null
            )
            ->willReturn($serialized)
        ;

        $options = $this->invokeClientMethod(
            ['serializer_adapter' => $serializer],
            'prepareDefaults',
            [[$format => $data]]
        );

        $this->assertArrayNotHasKey($format, $options);
        $this->assertArrayHasKey('body', $options);
        $this->assertArrayHasKey('_conditional', $options);
        $this->assertNotEmpty($options['body']);
        $this->assertNotEmpty($options['_conditional']);
        $this->assertArrayHasKey('Content-Type', $options['_conditional']);
        if (function_exists('\GuzzleHttp\Psr7\stream_for')) {
            $this->assertInstanceOf('GuzzleHttp\Psr7\Stream', $options['body']);
            $this->assertSame($serialized, (string) $options['body']);
        } else {
            $this->assertInternalType('string', $options['body']);
            $this->assertSame($serialized, $options['body']);
        }
        $this->assertSame($contentType, $options['_conditional']['Content-Type']);
    }

    /**
     * @dataProvider getDefaultsData
     */
    public function testPrepareDefaultsWithoutAdapter($data, $serialized, $format, $contentType)
    {
        $options = $this->invokeClientMethod(
            [],
            'prepareDefaults',
            [[$format => $data]]
        );

        $this->assertArrayNotHasKey('body', $options);
        $this->assertArrayHasKey($format, $options);
        $this->assertSame($data, $options[$format]);
    }

    /**
     * @dataProvider getResponseData
     */
    public function testProcessResponse($data, $deserialized, $type, $format, $contentType)
    {
        $response = $this->getMockResponse();
        $response
            ->expects($this->once())
            ->method('hasHeader')
            ->with(
                $this->equalTo('Content-Type')
            )
            ->willReturn(true)
        ;
        $response
            ->expects($this->once())
            ->method('getHeader')
            ->with(
                $this->equalTo('Content-Type')
            )
            ->willReturn([$contentType])
        ;
        $response
            ->expects($this->once())
            ->method('getBody')
            ->willReturn($data)
        ;

        $serializer = $this->getMock('Csa\Bundle\GuzzleBundle\Serializer\SerializerAdapterInterface');
        $serializer
            ->expects($this->once())
            ->method('deserialize')
            ->with(
                $this->equalTo($data),
                $this->equalTo($type),
                $this->equalTo($format),
                null
            )
            ->willReturn($deserialized)
        ;

        $deserializedData = $this->invokeClientMethod(
            ['serializer_adapter' => $serializer],
            'processResponse',
            [
                $response,
                ['serialization' => ['type' => $type]]
            ]
        );

        $this->assertSame($deserialized, $deserializedData);
    }

    /**
     * @param array $config
     * @param string $methodName
     * @param array $arguments
     *
     * @return mixed
     */
    private function invokeClientMethod(array $config, $methodName, $arguments = [])
    {
        $client = new ClientSerializerAdapter($config);
        $reflection = new \ReflectionClass(get_class($client));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($client, $arguments);
    }

    public function getDefaultsData()
    {
        return [
            ['toserializer', 'serialized', 'json', 'application/json'],
            ['toserializer', 'serialized', 'xml', 'application/xml'],
        ];
    }

    public function getResponseData()
    {
        return [
            ['todeserializer', 'deserialized', 'array', 'json', 'application/json'],
            ['todeserializer', 'deserialized', 'array', 'json', 'application/something+json'],
            ['todeserializer', 'deserialized', 'array', 'json', 'text/javascript'],
            ['todeserializer', 'deserialized', 'array', 'xml', 'application/xml'],
            ['todeserializer', 'deserialized', 'array', 'xml', 'application/something+xml'],
        ];
    }

    private function getMockResponse()
    {
        if (interface_exists('Psr\Http\Message\MessageInterface')) {
            $response = $this->getMock('Psr\Http\Message\MessageInterface');
        } else {
            $response = $this->getMock('GuzzleHttp\Message\MessageInterface');
        }

        return $response;
    }
}
