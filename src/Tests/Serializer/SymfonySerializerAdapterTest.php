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

use Csa\Bundle\GuzzleBundle\Serializer\SymfonySerializerAdapter;

class SymfonySerializerAdapterTest extends \PHPUnit_Framework_TestCase
{
    public function testNormalSerialization()
    {
        $serializer = $this->getMock('Symfony\Component\Serializer\SerializerInterface');
        $serializer
            ->expects($this->once())
            ->method('serialize')
            ->with(
                $this->equalTo(['test', 'test']),
                $this->equalTo('json'),
                $this->equalTo([])
            )
            ->willReturn('["test","test"]')
        ;

        $adapter = new SymfonySerializerAdapter($serializer);
        $this->assertSame('["test","test"]', $adapter->serialize(['test', 'test'], 'json'));
    }

    public function testWrongContextualizedSerialization()
    {
        $this->setExpectedException(
            '\LogicException',
            'Serialization context must be an array.'
        );

        $serializer = $this->getMock('Symfony\Component\Serializer\SerializerInterface');
        $adapter = new SymfonySerializerAdapter($serializer);
        $adapter->serialize(['test', 'test'], 'json', new \StdClass());
    }

    public function testNormalDeserialization()
    {
        $serializer = $this->getMock('Symfony\Component\Serializer\SerializerInterface');
        $serializer
            ->expects($this->once())
            ->method('deserialize')
            ->with(
                $this->equalTo('["test","test"]'),
                $this->equalTo('array'),
                $this->equalTo('json'),
                $this->equalTo([])
            )
            ->willReturn(['test', 'test'])
        ;

        $adapter = new SymfonySerializerAdapter($serializer);
        $this->assertSame(['test', 'test'], $adapter->deserialize('["test","test"]', 'array', 'json'));
    }

    public function testWrongContextualizedDeserialization()
    {
        $this->setExpectedException(
            '\LogicException',
            'Deserialization context must be an array.'
        );

        $serializer = $this->getMock('Symfony\Component\Serializer\SerializerInterface');
        $adapter = new SymfonySerializerAdapter($serializer);
        $adapter->deserialize(['test', 'test'], 'array', 'json', 'false context');
    }
}
