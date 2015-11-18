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

use Csa\Bundle\GuzzleBundle\DependencyInjection\CompilerPass\SubscriberPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class SubscriberPassTest extends \PHPUnit_Framework_TestCase
{
    public function testSubscriberRegisteredToFactory()
    {
        $container = $this->createContainer();
        $container->setDefinition('sub', $this->createSubscriber('my_sub'));

        $pass = new SubscriberPass();
        $pass->process($container);

        $calls = $container
            ->findDefinition(SubscriberPass::FACTORY_SERVICE_ID)
            ->getMethodCalls()
        ;

        $this->assertCount(1, $calls);

        $methodName = $calls[0][0];
        $methodArgs = $calls[0][1];

        $this->assertEquals('registerSubscriber', $methodName);
        $this->assertEquals('my_sub', $methodArgs[0]);
        $this->assertEquals('sub', (string) $methodArgs[1]);
    }

    public function testAllSubscribersAddedToTaggedClientsByDefault()
    {
        $container = $this->createContainer();
        $container->setDefinition('client', $client = $this->createClient());
        $container->setDefinition('sub', $this->createSubscriber('my_sub'));

        $pass = new SubscriberPass();
        $pass->process($container);

        $this->assertNotNull($callback = $client->getConfigurator());

        $this->assertEquals('configure', $callback[1]);
        $configurator = $container->findDefinition($callback[0]);

        $this->assertEquals([new Reference('sub')], $configurator->getArgument(0));
    }

    public function testSpecificSubscribersAddedToClient()
    {
        $client = $this->createClient($expected = ['foo', 'bar']);

        $container = $this->createContainer();
        $container->setDefinition('client', $client);

        foreach (['foo', 'bar', 'qux'] as $alias) {
            $container->setDefinition($alias, $this->createSubscriber($alias));
        }

        $pass = new SubscriberPass();
        $pass->process($container);

        $references = $container
            ->findDefinition($client->getConfigurator()[0])
            ->getArgument(0)
        ;

        $subscribers = array_map(function ($reference) {
            return (string) $reference;
        }, $references);

        $this->assertEquals(['foo', 'bar'], $subscribers, 'Only the specified subscribers must be added.');
    }

    public function testPreviousConfiguratorWrapped()
    {
        $client = $this->createClient();
        $client->setConfigurator($parent = [new Reference('foo'), 'configure']);

        $container = $this->createContainer();
        $container->setDefinition('client', $client);
        $container->setDefinition('sub', $this->createSubscriber('my_sub'));

        $pass = new SubscriberPass();
        $pass->process($container);

        $callback = $client->getConfigurator();
        $this->assertNotSame($parent, $callback, 'Subscriber pass should have replaced the configurator.');

        $configurator = $container->findDefinition($callback[0]);
        $this->assertCount(2, $configurator->getArguments(), 'The parent configurator should have been passed as the 2nd argument.');

        $this->assertSame($parent, $configurator->getArgument(1));
    }

    private function createSubscriber($alias)
    {
        $subscriber = new Definition();
        $subscriber->addTag(SubscriberPass::SUBSCRIBER_TAG, ['alias' => $alias]);

        return $subscriber;
    }

    private function createClient(array $subscribers = null)
    {
        $client = new Definition();
        $client->addTag(
            SubscriberPass::CLIENT_TAG,
            $subscribers ? ['subscribers' => implode(', ', $subscribers)] : []
        );

        return $client;
    }

    private function createContainer()
    {
        $container = new ContainerBuilder();
        $container->setDefinition(SubscriberPass::FACTORY_SERVICE_ID, new Definition());

        return $container;
    }
}
