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
use GuzzleHttp\HandlerStack;
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

        $handlerDefinition = $client->getArgument(0)['handler'];
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

        $handlerDefinition = $client->getArgument(0)['handler'];
        $this->assertCount(2, $calls = $handlerDefinition->getMethodCalls());
        $this->assertEquals(['push', [new Reference('foo'), 'foo']], $calls[0]);
        $this->assertEquals(['push', [new Reference('bar'), 'bar']], $calls[1]);
    }

    public function testDisableSpecificMiddlewareForClient()
    {
        $client = $this->createClient(['!foo']);

        $container = $this->createContainer();
        $container->setDefinition('client', $client);

        foreach (['foo', 'bar', 'qux'] as $alias) {
            $this->createMiddleware($container, $alias);
        }

        $pass = new MiddlewarePass();
        $pass->process($container);

        $handlerDefinition = $client->getArgument(0)['handler'];
        $this->assertCount(2, $calls = $handlerDefinition->getMethodCalls());
        $this->assertEquals(['push', [new Reference('bar'), 'bar']], $calls[0]);
        $this->assertEquals(['push', [new Reference('qux'), 'qux']], $calls[1]);
    }

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\LogicException
     * @expectedExceptionMessage You cannot mix whitelisting and blacklisting of middleware at the same time.
     */
    public function testForbidWhitelistingAlongWithBlacklisting()
    {
        $client = $this->createClient(['!foo', 'bar']);

        $container = $this->createContainer();
        $container->setDefinition('client', $client);

        foreach (['foo', 'bar', 'qux'] as $alias) {
            $this->createMiddleware($container, $alias);
        }

        $pass = new MiddlewarePass();
        $pass->process($container);
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

        $handlerDefinition = $client->getArgument(0)['handler'];
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

        $this->assertCount(0, $client->getArguments());
    }

    public function testCustomHandlerStackIsKeptAndMiddlewareAdded()
    {
        $handler = new Definition(HandlerStack::class);
        $client = $this->createClient([], $handler);
        $container = $this->createContainer();
        $container->setDefinition('client', $client);

        foreach (['foo' => 0, 'bar' => 10, 'qux' => -1000] as $alias => $priority) {
            $this->createMiddleware($container, $alias, $priority);
        }

        $pass = new MiddlewarePass();
        $pass->process($container);

        $clientHandler = $client->getArgument(0)['handler'];
        $this->assertSame($handler, $clientHandler);
        $this->assertSame(HandlerStack::class, $clientHandler->getClass());
        $this->assertTrue($clientHandler->hasMethodCall('push'));
    }

    public function testCustomHandlerCallableIsWrappedAndMiddlewareAdded()
    {
        $handler = function () {
        };
        $client = $this->createClient([], $handler);
        $container = $this->createContainer();
        $container->setDefinition('client', $client);

        foreach (['foo' => 0, 'bar' => 10, 'qux' => -1000] as $alias => $priority) {
            $this->createMiddleware($container, $alias, $priority);
        }

        $pass = new MiddlewarePass();
        $pass->process($container);

        $clientHandler = $client->getArgument(0)['handler'];
        $this->assertInstanceOf(Definition::class, $clientHandler);
        $this->assertSame(HandlerStack::class, $clientHandler->getClass());
        $this->assertSame($handler, $clientHandler->getArgument(0));
        $this->assertTrue($clientHandler->hasMethodCall('push'));
    }

    private function createMiddleware(ContainerBuilder $container, $alias, $priority = null)
    {
        $middleware = new Definition();
        $middleware->addTag(MiddlewarePass::MIDDLEWARE_TAG, ['alias' => $alias, 'priority' => $priority]);
        $container->setDefinition($alias, $middleware);
    }

    private function createClient(array $middleware = null, $handler = null)
    {
        $client = new Definition();
        $client->addTag(
            MiddlewarePass::CLIENT_TAG,
            $middleware ? ['middleware' => implode(' ', $middleware)] : []
        );

        if ($handler) {
            $client->addArgument(['handler' => $handler]);
        }

        return $client;
    }

    private function createContainer()
    {
        return new ContainerBuilder();
    }
}
