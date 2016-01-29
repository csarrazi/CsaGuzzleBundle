<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Csa\Bundle\GuzzleBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Csa Guzzle middleware compiler pass.
 *
 * @author Charles Sarrazin <charles@sarraz.in>
 */
class MiddlewarePass implements CompilerPassInterface
{
    const MIDDLEWARE_TAG = 'csa_guzzle.middleware';
    const CLIENT_TAG = 'csa_guzzle.client';

    public function process(ContainerBuilder $container)
    {
        $middleware = $this->findAvailableMiddleware($container);

        $this->registerMiddleware($container, $middleware);
    }

    /**
     * Fetches the list of available middleware.
     *
     * @param ContainerBuilder $container
     *
     * @return array
     */
    private function findAvailableMiddleware(ContainerBuilder $container)
    {
        $services = $container->findTaggedServiceIds(self::MIDDLEWARE_TAG);
        $middleware = [];

        foreach ($services as $id => $tags) {
            if (count($tags) > 1) {
                throw new \LogicException('Middleware should only use a single \'csa_guzzle.middleware\' tag');
            }

            if (!isset($tags[0]['alias'])) {
                throw new \LogicException('The \'alias\' attribute is mandatory for the \'csa_guzzle.middleware\' tag');
            }

            $priority = isset($tags[0]['priority']) ? $tags[0]['priority'] : 0;

            $middleware[$priority][] = [
                'alias' => $tags[0]['alias'],
                'id' => $id,
            ];
        }

        if (empty($middleware)) {
            return [];
        }

        krsort($middleware);

        return call_user_func_array('array_merge', $middleware);
    }

    /**
     * Sets up handlers and registers middleware for each tagged client.
     *
     * @param ContainerBuilder $container
     * @param array            $middlewareBag
     */
    private function registerMiddleware(ContainerBuilder $container, array $middlewareBag)
    {
        if (empty($middlewareBag)) {
            return;
        }

        $clients = $container->findTaggedServiceIds(self::CLIENT_TAG);

        foreach ($clients as $clientId => $tags) {
            if (count($tags) > 1) {
                throw new \LogicException('Clients should use a single \'csa_guzzle.client\' tag');
            }

            $clientMiddleware = $middlewareBag;

            if (isset($tags[0]['middleware'])) {
                $whitelist = explode(' ', $tags[0]['middleware']);
                $clientMiddleware = array_filter($clientMiddleware, function ($value) use ($whitelist) {
                    return in_array($value['alias'], $whitelist, true);
                });
            }

            if (empty($clientMiddleware)) {
                continue;
            }

            $clientDefinition = $container->findDefinition($clientId);

            $arguments = $clientDefinition->getArguments();

            $options = [];

            if (!empty($arguments)) {
                $options = array_shift($arguments);
            }

            if (!isset($options['handler'])) {
                $handlerStack = new Definition('csa_guzzle.handler_stack');
                $handlerStack->setFactory(['GuzzleHttp\HandlerStack', 'create']);
                $handlerStack->setPublic(false);

                $clientHandlerStackId = sprintf('csa_guzzle.handler_stack.%s', $clientId);

                $container->setDefinition($clientHandlerStackId, $handlerStack);
                $options['handler'] = $handlerStack;
            }

            $handlerStack = $options['handler'];

            foreach ($clientMiddleware as $middleware) {
                $handlerStack->addMethodCall('push', [new Reference($middleware['id']), $middleware['alias']]);
            }

            array_unshift($arguments, $options);
            $clientDefinition->setArguments($arguments);
        }
    }
}
