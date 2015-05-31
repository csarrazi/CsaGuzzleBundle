<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Csa\Bundle\GuzzleBundle\DependencyInjection;

use Csa\Bundle\GuzzleBundle\DependencyInjection\CompilerPass\SubscriberPass;
use Csa\Bundle\GuzzleBundle\DependencyInjection\CsaGuzzleExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
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

    private function createContainer($yaml)
    {
        $parser = new Parser();
        $container = new ContainerBuilder();

        $loader = new CsaGuzzleExtension();
        $loader->load([$parser->parse($yaml)], $container);

        return $container;
    }
}
