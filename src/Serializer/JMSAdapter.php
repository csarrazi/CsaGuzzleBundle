<?php
/**
 * Created by PhpStorm.
 * User: AJanssen
 * Date: 05-06-15
 * Time: 15:22
 */

namespace Csa\Bundle\GuzzleBundle\Serializer;


use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use Symfony\Component\Serializer\SerializerInterface;
use \JMS\Serializer\SerializerInterface as JMSSerializerInterface;

class JMSAdapter implements SerializerInterface
{
    /**
     * @var JMSSerializerInterface
     */
    private $serializer;

    /**
     * @param \JMS\Serializer\SerializerInterface $serializer
     */
    public function __construct(JMSSerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Serializes data in the appropriate format.
     *
     * @param mixed  $data    any data
     * @param string $format  format name
     * @param array  $context options normalizers/encoders have access to
     *
     * @return string
     */
    public function serialize($data, $format, array $context = [])
    {
        return $this->serializer->serialize($data, $format, SerializationContext::create($context));
    }

    /**
     * Deserializes data into the given type.
     *
     * @param mixed  $data
     * @param string $type
     * @param string $format
     * @param array  $context
     *
     * @return object
     */
    public function deserialize($data, $type, $format, array $context = [])
    {
        return $this->serializer->deserialize($data, $type, $format, DeserializationContext::create($context));
    }


}