<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Csa\Bundle\GuzzleBundle\Tests\Serializer;

use Csa\Bundle\GuzzleBundle\Serializer\JMSSerializerAdapter;
use JMS\Serializer\SerializationContext as JMSSerializationContext;
use JMS\Serializer\DeserializationContext as JMSDeserializationContext;

class JMSSerializerAdapterTest extends \PHPUnit_Framework_TestCase
{
    public function testNormalSerialization()
    {
        $serializer = $this->getMock('JMS\Serializer\SerializerInterface');
        $serializer
            ->expects($this->once())
            ->method('serialize')
            ->with(
                $this->equalTo(['test', 'test']),
                $this->equalTo('json'),
                $this->equalTo(JMSSerializationContext::create())
            )
            ->willReturn('["test","test"]')
        ;

        $adapter = new JMSSerializerAdapter($serializer);
        $this->assertSame('["test","test"]', $adapter->serialize(['test', 'test'], 'json'));
    }

    public function testWrongContextualizedSerialization()
    {
        $this->setExpectedException(
            '\LogicException',
            'Serialization context must be an instance of JMS\Serializer\SerializationContext.'
        );

        $serializer = $this->getMock('JMS\Serializer\SerializerInterface');
        $adapter = new JMSSerializerAdapter($serializer);
        $adapter->serialize(['test', 'test'], 'json', ['false' => 'context']);
    }

    public function testNormalDeserialization()
    {
        $serializer = $this->getMock('JMS\Serializer\SerializerInterface');
        $serializer
            ->expects($this->once())
            ->method('deserialize')
            ->with(
                $this->equalTo('["test","test"]'),
                $this->equalTo('array'),
                $this->equalTo('json'),
                $this->equalTo(JMSDeserializationContext::create())
            )
            ->willReturn(['test', 'test'])
        ;

        $adapter = new JMSSerializerAdapter($serializer);
        $this->assertSame(['test', 'test'], $adapter->deserialize('["test","test"]', 'array', 'json'));
    }

    public function testWrongContextualizedDeserialization()
    {
        $this->setExpectedException(
            '\LogicException',
            'Deserialization context must be an instance of JMS\Serializer\DeserializationContext.'
        );

        $serializer = $this->getMock('JMS\Serializer\SerializerInterface');
        $adapter = new JMSSerializerAdapter($serializer);
        $adapter->deserialize(['test', 'test'], 'array', 'json', 'false context');
    }
}
