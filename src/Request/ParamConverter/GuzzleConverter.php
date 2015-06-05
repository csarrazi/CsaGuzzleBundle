<?php
/**
 * Created by PhpStorm.
 * User: AJanssen
 * Date: 03-06-15
 * Time: 13:43
 */

namespace Csa\Bundle\GuzzleBundle\Request\ParamConverter;


use GuzzleHttp\Command\Guzzle\Description;
use GuzzleHttp\Exception\ClientException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class GuzzleConverter implements ParamConverterInterface
{
    /**
     * @var array
     */
    private $services = [];

    /**
     * @return array
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * @param array $services
     */
    public function setServices($services)
    {
        $this->services = $services;
    }

    public function addService($name, $service)
    {
        $this->services[$name] = $service;
    }

    /**
     * Stores the object in the request.
     *
     * @param Request        $request       The request
     * @param ParamConverter $configuration Contains the name, class and options of the object
     *
     * @return bool True if the object has been successfully set, else false
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $client = $this->findService($configuration);
        $description = $client->getDescription();
        /** @var Description $description */
        if (isset($configuration->getOptions()['operation'])) {
            $function = $configuration->getOptions()['operation'];
        } else {
            foreach ($description->getOperations() as $name => $searchOperation) {
                if (isset($searchOperation['responseModel']) && $searchOperation['responseModel'] == $configuration->getClass()) {
                    $function = $name;
                    break;
                }
            }
        }

        $operation = $description->getOperation($function);
        if (isset($operation)) {
            $parameters = [];
            foreach ($operation->getParams() as $param) {
                $parameters[$param->getName()] = $request->get($param->getName());
            }
            try {
                $request->attributes->set($configuration->getName(), $client->$function($parameters));
            } catch (\Exception $e) {
            }
        }

    }

    /**
     * Checks if the object is supported.
     *
     * @param ParamConverter $configuration Should be an instance of ParamConverter
     *
     * @return bool True if the object is supported, else false
     */
    public function supports(ParamConverter $configuration)
    {
        return ($this->findService($configuration) !== null);
    }

    public function findService($configuration)
    {

        foreach ($this->services as $service) {
            $description = $service->getDescription();
            /** @var Description $description */

            foreach ($description->getModels() as $model) {
                if ($configuration->getClass() == $model->class) {
                    return $service;
                }
            }
        }
    }

}