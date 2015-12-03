<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Csa\Bundle\GuzzleBundle\Tests\DependencyInjection\CompilerPass;

use Csa\Bundle\GuzzleBundle\DependencyInjection\CompilerPass\MiddlewarePass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class MiddlewarePassTest extends \PHPUnit_Framework_TestCase
{
    public function testAllMiddlewareAddedToTaggedClientsByDefault()
    {
        $container = $this->createContainer();
        $container->setDefinition('client', $client = $this->createClient());
        $this->createMiddleware($container, 'my_mid');
        $this->createMiddleware($container, 'my_mid2');

        $pass = new MiddlewarePass();
        $pass->process($container);

        $handler = $client->getArgument(0)['handler'];
        $handlerDefinition = $container->getDefinition((string) $handler);
        $this->assertCount(2, $calls = $handlerDefinition->getMethodCalls());
        $this->assertEquals(['push', [new Reference('my_mid'), 'my_mid']], $calls[0]);
        $this->assertEquals(['push', [new Reference('my_mid2'), 'my_mid2']], $calls[1]);
    }

    public function testSpecificMiddlewareAddedToClient()
    {
        $client = $this->createClient(['foo', 'bar']);

        $container = $this->createContainer();
        $container->setDefinition('client', $client);

        foreach (['foo', 'bar', 'qux'] as $alias) {
            $this->createMiddleware($container, $alias);
        }

        $pass = new MiddlewarePass();
        $pass->process($container);

        $handler = $client->getArgument(0)['handler'];
        $handlerDefinition = $container->getDefinition((string) $handler);
        $this->assertCount(2, $calls = $handlerDefinition->getMethodCalls());
        $this->assertEquals(['push', [new Reference('foo'), 'foo']], $calls[0]);
        $this->assertEquals(['push', [new Reference('bar'), 'bar']], $calls[1]);
    }

    public function testMiddlewareWithPriority()
    {
        $client = $this->createClient();

        $container = $this->createContainer();
        $container->setDefinition('client', $client);

        foreach (['foo' => 0, 'bar' => 10, 'qux' => -1000] as $alias => $priority) {
            $this->createMiddleware($container, $alias, $priority);
        }

        $pass = new MiddlewarePass();
        $pass->process($container);

        $handler = $client->getArgument(0)['handler'];
        $handlerDefinition = $container->getDefinition((string) $handler);
        $this->assertCount(3, $calls = $handlerDefinition->getMethodCalls());
        $this->assertEquals(['push', [new Reference('bar'), 'bar']], $calls[0]);
        $this->assertEquals(['push', [new Reference('foo'), 'foo']], $calls[1]);
        $this->assertEquals(['push', [new Reference('qux'), 'qux']], $calls[2]);
    }

    public function testNoMiddleware()
    {
        $client = $this->createClient();

        $container = $this->createContainer();
        $container->setDefinition('client', $client);

        $pass = new MiddlewarePass();
        $pass->process($container);

        $handler = $client->getArgument(0)['handler'];
        $handlerDefinition = $container->getDefinition((string) $handler);
        $this->assertCount(0, $calls = $handlerDefinition->getMethodCalls());
    }

    private function createMiddleware(ContainerBuilder $container, $alias, $priority = null)
    {
        $middleware = new Definition();
        $middleware->addTag(MiddlewarePass::MIDDLEWARE_TAG, ['alias' => $alias, 'priority' => $priority]);
        $container->setDefinition($alias, $middleware);
    }

    private function createClient(array $middleware = null)
    {
        $client = new Definition();
        $client->addTag(
            MiddlewarePass::CLIENT_TAG,
            $middleware ? ['middleware' => implode(' ', $middleware)] : []
        );

        return $client;
    }

    private function createContainer()
    {
        return new ContainerBuilder();
    }
}
