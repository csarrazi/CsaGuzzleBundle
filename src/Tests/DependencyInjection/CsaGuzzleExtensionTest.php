<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Csa\Bundle\GuzzleBundle\Tests\DependencyInjection;

use Csa\Bundle\GuzzleBundle\DependencyInjection\CompilerPass\MiddlewarePass;
use Csa\Bundle\GuzzleBundle\DependencyInjection\CsaGuzzleExtension;
use GuzzleHttp\MessageFormatter;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Yaml\Parser;

class CsaGuzzleExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testClientCreated()
    {
        $yaml = <<<YAML
profiler:
    enabled: false
clients:
    foo:
        config: { base_url: example.com }
YAML;

        $container = $this->createContainer($yaml);

        $this->assertTrue($container->hasDefinition('csa_guzzle.client.foo'), 'Client must be created.');

        $client = $container->getDefinition('csa_guzzle.client.foo');

        $this->assertEquals(
            [MiddlewarePass::CLIENT_TAG => [[]]],
            $client->getTags(),
            'Clients must be tagged.'
        );

        $this->assertEquals(
            ['base_url' => 'example.com'],
            $client->getArgument(0),
            'Config must be passed to client constructor.'
        );
    }

    public function testClientClassOverride()
    {
        $yaml = <<<YAML
clients:
    foo:
        class: AppBundle\Client
YAML;

        $container = $this->createContainer($yaml);

        $client = $container->getDefinition('csa_guzzle.client.foo');

        $this->assertEquals('AppBundle\Client', $client->getClass());
    }

    public function testClientConfigInstanceOverride()
    {
        $yaml = <<<YAML
clients:
    foo:
        config:
            handler: my.handler.id
YAML;

        $container = $this->createContainer($yaml);
        $config = $container->getDefinition('csa_guzzle.client.foo')->getArgument(0);
        $this->assertInstanceOf(
            'Symfony\Component\DependencyInjection\Reference',
            $config['handler']
        );
        $this->assertSame(
            'my.handler.id',
            (string)$config['handler']
        );
    }

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     * @expectedExceptionMessage Config for "csa_guzzle.client.bar" should be an array, but got string
     */
    public function testInvalidClientConfig()
    {
        $yaml = <<<YAML
clients:
    foo:
        config: ~       # legacy mode
    bar:
        config: invalid # exception
YAML;

        $this->createContainer($yaml);
    }

    public function testMiddlewareAddedToClient()
    {
        $yaml = <<<YAML
logger: true
profiler: true
clients:
    foo:
        middleware: [stopwatch, debug]
YAML;

        $container = $this->createContainer($yaml);

        $this->assertTrue($container->hasDefinition('csa_guzzle.client.foo'), 'Client must be created.');

        $client = $container->getDefinition('csa_guzzle.client.foo');

        $this->assertEquals(
            [MiddlewarePass::CLIENT_TAG => [['middleware' => 'stopwatch debug history logger']]],
            $client->getTags(),
            'Only explicitly disabled middleware shouldn\'t be added.'
        );
    }

    public function testCustomMiddlewareAddedToClient()
    {
        $yaml = <<<YAML
logger: true
profiler: true
clients:
    foo:
        middleware: [stopwatch, debug, foo]
YAML;

        $container = $this->createContainer($yaml);

        $definition = new Definition();
        $definition->addTag('csa_guzzle.subscriber', ['alias' => 'foo']);
        $container->setDefinition('my.service.foo', $definition);

        $this->assertTrue($container->hasDefinition('csa_guzzle.client.foo'), 'Client must be created.');

        $client = $container->getDefinition('csa_guzzle.client.foo');

        $this->assertEquals(
            [MiddlewarePass::CLIENT_TAG => [['middleware' => 'stopwatch debug foo history logger']]],
            $client->getTags(),
            'Only explicitly disabled middleware shouldn\'t be added.'
        );
    }

    public function testLoggerConfiguration()
    {
        $yaml = <<<YAML
logger:
    enabled: true
    service: monolog.logger
    format: %s
YAML;
        $formats = ['clf' => MessageFormatter::CLF, 'debug' => MessageFormatter::DEBUG, 'short' => MessageFormatter::SHORT];

        foreach ($formats as $alias => $format) {
            $container = $this->createContainer(sprintf($yaml, $alias));

            $this->assertSame($format, $container->getDefinition('csa_guzzle.logger.message_formatter')->getArgument(0));
            $this->assertSame('monolog.logger', (string)$container->getDefinition('csa_guzzle.middleware.logger')->getArgument(0));
        }

        $yaml = <<<YAML
logger: false
YAML;

        $container = $this->createContainer($yaml);
        $this->assertFalse($container->hasDefinition('csa_guzzle.middleware.logger'));
    }

    public function testCacheConfiguration()
    {
        $yaml = <<<YAML
cache: false
YAML;

        $container = $this->createContainer($yaml);
        $this->assertFalse($container->hasDefinition('csa_guzzle.middleware.cache'));

        $yaml = <<<YAML
cache:
    enabled: true
    adapter: my.adapter.id
YAML;

        $container = $this->createContainer($yaml);
        $container->setDefinition('my.adapter.id', new Definition());
        $alias = $container->getAlias('csa_guzzle.cache_adapter');
        $this->assertSame('my.adapter.id', (string)$alias);
    }

    private function createContainer($yaml)
    {
        $parser = new Parser();
        $container = new ContainerBuilder();

        $loader = new CsaGuzzleExtension();
        $loader->load([$parser->parse($yaml)], $container);

        return $container;
    }
}
