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

use Csa\Bundle\GuzzleBundle\DependencyInjection\CompilerPass\SubscriberPass;
use Csa\Bundle\GuzzleBundle\DependencyInjection\CsaGuzzleExtension;
use GuzzleHttp\Subscriber\Log\Formatter;
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
            [SubscriberPass::CLIENT_TAG => [['subscribers' => '']]],
            $client->getTags(),
            'Clients must be tagged.'
        );

        $this->assertEquals(
            ['base_url' => 'example.com'],
            $client->getArgument(0),
            'Config must be passed to client constructor.'
        );
    }

    public function testClientWithDescription()
    {
        $yaml = <<<YAML
clients:
    foo:
        config: { base_url: example.com }
        description: %s
YAML;

        $container = $this->createContainer(sprintf($yaml, realpath(__DIR__ . '/../Fixtures/github.description.json')));
        $this->assertTrue($container->hasDefinition('csa_guzzle.service.foo'));
        $this->assertSame('csa_guzzle.client.foo', (string)$container->getDefinition('csa_guzzle.service.foo')->getArgument(0));
        $this->assertSame('service("csa_guzzle.description_factory").getDescription("foo")', (string)$container->getDefinition('csa_guzzle.service.foo')->getArgument(1));
    }

    public function testSubscribersAddedToClient()
    {
        $yaml = <<<YAML
logger: true
profiler: true
clients:
    foo:
        subscribers:
            stopwatch: false
            debug: true
YAML;

        $container = $this->createContainer($yaml);

        $this->assertTrue($container->hasDefinition('csa_guzzle.client.foo'), 'Client must be created.');

        $client = $container->getDefinition('csa_guzzle.client.foo');

        $this->assertEquals(
            [SubscriberPass::CLIENT_TAG => [['subscribers' => 'debug,logger']]],
            $client->getTags(),
            'Only explicitly disabled subscribers shouldn\'t be added.'
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
        $formats = ['clf' => Formatter::CLF, 'debug' => Formatter::DEBUG, 'short' => Formatter::SHORT];

        foreach ($formats as $alias => $format) {
            $container = $this->createContainer(sprintf($yaml, $alias));

            $this->assertSame($format, $container->getDefinition('csa_guzzle.subscriber.logger')->getArgument(1));
            $this->assertSame('monolog.logger', (string)$container->getDefinition('csa_guzzle.subscriber.logger')->getArgument(0));
        }

        $yaml = <<<YAML
logger: false
YAML;

        $container = $this->createContainer($yaml);
        $this->assertFalse($container->hasDefinition('csa_guzzle.subscriber.logger'));
    }

    public function testCacheConfiguration()
    {
        $yaml = <<<YAML
cache: false
YAML;

        $container = $this->createContainer($yaml);
        $this->assertFalse($container->hasDefinition('csa_guzzle.subscriber.cache'));

        $yaml = <<<YAML
cache:
    enabled: true
    adapter: my.adapter.id
YAML;

        $container = $this->createContainer($yaml);
        $container->setDefinition('my.adapter.id', new Definition());
        $alias = $container->getAlias('csa_guzzle.default_cache_adapter');
        $this->assertSame('my.adapter.id', (string)$alias);
    }

    public function testLegacyCacheConfiguration()
    {
        $yaml = <<<YAML
cache:
    enabled: true
    service: my.service.id
YAML;

        $container = $this->createContainer($yaml);
        $container->setDefinition('my.service.id', new Definition(null, [null, null]));
        $alias = $container->getAlias('csa_guzzle.default_cache_adapter');
        $this->assertSame('my.service.id', (string)$container->getDefinition((string) $alias)->getArgument(0));
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Invalid configuration for path "csa_guzzle.cache.adapter.type": Invalid cache adapter
     */
    public function testLegacyWrongCacheAdapterTypeThrowsException()
    {
        $yaml = <<<YAML
cache:
    enabled: true
    adapter:
        type: foo
YAML;

        $this->createContainer($yaml);
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
