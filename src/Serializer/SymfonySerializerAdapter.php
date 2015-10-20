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

use Symfony\Component\Serializer\SerializerInterface;

class SymfonySerializerAdapter implements SerializerAdapterInterface
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
    public function serialize($data, $format, $context = [])
    {
        if (!is_array($context)) {
            throw new \LogicException(
                'Serialization context must be an array.'
            );
        }

        return $this->serializer->serialize($data, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function deserialize($data, $type, $format, $context = [])
    {
        if (!is_array($context)) {
            throw new \LogicException(
                'Deserialization context must be an array.'
            );
        }

        return $this->serializer->deserialize($data, $type, $format, $context);
    }
}
