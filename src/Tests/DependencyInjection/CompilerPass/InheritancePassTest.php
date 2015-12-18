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

use Csa\Bundle\GuzzleBundle\DependencyInjection\CompilerPass\InheritancePass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class InheritancePassTest extends \PHPUnit_Framework_TestCase
{
    public function testClientWithoutConfigAndParentWithConfigWillMergeTheParentOne()
    {
        $container = $this->createContainer();
        $parent = $this->createClient();
        $parentConfig = ['proxy' => 'a'];
        $parent->addArgument($parentConfig);
        $container->setDefinition('parent.foo', $parent);

        $client = $this->createClient('parent.foo');
        $client->addArgument([]);
        $container->setDefinition('client.bar', $client);

        $pass = new InheritancePass();
        $pass->process($container);

        $clientDefinition = $container->findDefinition('client.bar');
        $this->assertCount(1, $clientDefinition->getArguments(), 'Client inheritance error');
        $this->assertEquals($parentConfig, $clientDefinition->getArgument(0), 'Client inheritance error');
    }

    public function testClientWithConfigAndParentWithConfigWillMergeBoth()
    {
        $container = $this->createContainer();
        $parent = $this->createClient();
        $parent->addArgument(['proxy' => 'a']);
        $container->setDefinition('parent.foo', $parent);

        $client = $this->createClient('parent.foo');
        $client->addArgument(['verify' => false]);
        $container->setDefinition('client.bar', $client);
        $client->addTag(InheritancePass::TAG, ['extends' => 'parent.foo']);

        $pass = new InheritancePass();
        $pass->process($container);

        $clientDefinition = $container->findDefinition('client.bar');
        $this->assertEquals(
            ['proxy' => 'a', 'verify' => false],
            $clientDefinition->getArgument(0),
            'Client inheritance error'
        );
    }

    public function testClientWithoutConfigAndParentWithoutConfigWillResultNoConfig()
    {
        $container = $this->createContainer();
        $parent = $this->createClient();
        $parent->addArgument([]);
        $container->setDefinition('parent.foo', $parent);

        $client = $this->createClient('parent.foo');
        $client->addArgument([]);
        $container->setDefinition('client.bar', $client);

        $pass = new InheritancePass();
        $pass->process($container);

        $clientDefinition = $container->findDefinition('client.bar');
        $this->assertCount(0, $clientDefinition->getArgument(0), 'Client inheritance error');
    }

    public function testMultiplesRecursivesInheritances()
    {
        $container = $this->createContainer();

        $clientA = $this->createClient();
        $clientA->addArgument(['proxy' => 'a']);
        $container->setDefinition('client.a', $clientA);

        $clientB = $this->createClient('client.a');
        $clientB->addArgument(['verify' => false]);
        $container->setDefinition('client.b', $clientB);

        $clientC = $this->createClient('client.b');
        $clientC->addArgument(['verify' => true]);
        $container->setDefinition('client.c', $clientC);

        $clientD = $this->createClient('client.c');
        $clientD->addArgument(['headers' => ['foo' => 'bar']]);
        $container->setDefinition('client.d', $clientD);

        $clientE = $this->createClient('client.b');
        $clientE->addArgument(['headers' => ['foo' => 'baz']]);
        $container->setDefinition('client.e', $clientE);

        $pass = new InheritancePass();
        $pass->process($container);

        $aDefinition = $container->findDefinition('client.a');
        $this->assertEquals(['proxy' => 'a'], $aDefinition->getArgument(0), 'Client inheritance error');

        $bDefinition = $container->findDefinition('client.b');
        $this->assertEquals(
            ['proxy' => 'a', 'verify' => false],
            $bDefinition->getArgument(0),
            'Client inheritance error'
        );

        $cDefinition = $container->findDefinition('client.c');
        $this->assertEquals(
            ['proxy' => 'a', 'verify' => true],
            $cDefinition->getArgument(0),
            'Client inheritance error'
        );

        $dDefinition = $container->findDefinition('client.d');
        $this->assertEquals(
            ['proxy' => 'a', 'verify' => true, 'headers' => ['foo' => 'bar']],
            $dDefinition->getArgument(0),
            'Client inheritance error'
        );

        $eDefinition = $container->findDefinition('client.e');
        $this->assertEquals(
            ['proxy' => 'a', 'verify' => false, 'headers' => ['foo' => 'baz']],
            $eDefinition->getArgument(0),
            'Client inheritance error'
        );
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testCircularReference()
    {
        $container = $this->createContainer();

        $clientA = $this->createClient('client.b');
        $container->setDefinition('client.a', $clientA);

        $clientB = $this->createClient('client.a');
        $container->setDefinition('client.b', $clientB);

        $pass = new InheritancePass();
        $pass->process($container);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testCircularReferenceInDepth()
    {
        $container = $this->createContainer();

        $clientA = $this->createClient('client.d');
        $container->setDefinition('client.a', $clientA);

        $clientB = $this->createClient('client.a');
        $container->setDefinition('client.b', $clientB);

        $clientC = $this->createClient('client.b');
        $container->setDefinition('client.c', $clientC);

        $clientD = $this->createClient('client.c');
        $container->setDefinition('client.d', $clientD);

        $pass = new InheritancePass();
        $pass->process($container);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage The tag "csa_guzzle.inheritance" require an attribute named "extends" of the service "client.a".
     */
    public function testTagWithoutExtendsWillThrowException()
    {
        $container = $this->createContainer();

        $clientA = new Definition();
        $clientA->addArgument([]);
        $clientA->addTag(InheritancePass::TAG);
        $container->setDefinition('client.a', $clientA);

        $pass = new InheritancePass();
        $pass->process($container);
    }

    private function createClient($parent = null)
    {
        $client = new Definition();

        if ($parent) {
            $client->addTag(InheritancePass::TAG, ['extends' => $parent]);
        }

        return $client;
    }

    private function createContainer()
    {
        return new ContainerBuilder();
    }
}
