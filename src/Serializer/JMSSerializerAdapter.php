<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Csa\Bundle\GuzzleBundle\Serializer;

use JMS\Serializer\SerializationContext as JMSSerializationContext;
use JMS\Serializer\DeserializationContext as JMSDeserializationContext;
use JMS\Serializer\SerializerInterface;

class JMSSerializerAdapter implements SerializerAdapterInterface
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize($data, $format, $context = null)
    {
        $context = $context ?: JMSSerializationContext::create();
        if (!$context instanceof JMSSerializationContext) {
            throw new \LogicException(
                'Serialization context must be an instance of JMS\Serializer\SerializationContext.'
            );
        }

        return $this->serializer->serialize($data, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function deserialize($data, $type, $format, $context = null)
    {
        $context = $context ?: JMSDeserializationContext::create();
        if (!$context instanceof JMSDeserializationContext) {
            throw new \LogicException(
                'Deserialization context must be an instance of JMS\Serializer\DeserializationContext.'
            );
        }
        return $this->serializer->deserialize($data, $type, $format, $context);
    }
}
