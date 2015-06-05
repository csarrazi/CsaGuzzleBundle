<?php
/**
 * Created by PhpStorm.
 * User: AJanssen
 * Date: 03-06-15
 * Time: 14:35
 */

namespace Csa\Bundle\GuzzleBundle\GuzzleHttp\Command\Guzzle;

use Csa\Bundle\GuzzleBundle\GuzzleHttp\Command\Guzzle\Subscriber\ProcessResponse;
use GuzzleHttp\Command\Guzzle\GuzzleClient as AbstractGuzzleClient;
use Symfony\Component\Serializer\SerializerInterface;

class GuzzleClient extends AbstractGuzzleClient
{
    /**
     * @var SerializerInterface $serializer;
     */
    protected $serializer;

    /**
     * @param \Symfony\Component\Serializer\SerializerInterface $serializer
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }


    public function addProcess()
    {
        $this->getEmitter()->attach(
            new ProcessResponse(
                $this->getDescription(),
                $this->serializer,
                isset($config['response_locations'])
                    ? $config['response_locations']
                    : []
            )
        );
    }


}