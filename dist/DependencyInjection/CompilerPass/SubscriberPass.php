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
use Symfony\Component\DependencyInjection\Reference;

/**
 * Csa Guzzle client compiler pass
 *
 * @author Charles Sarrazin <charles@sarraz.in>
 */
class SubscriberPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $subscribers = $container->findTaggedServiceIds('csa_guzzle.subscriber');

        if (!count($subscribers)) {
            return;
        }

        // Factory
        $factory = $container->findDefinition('csa_guzzle.client_factory');
        $arg = [];
        foreach ($subscribers as $subscriber => $options) {
            $arg[] = new Reference($subscriber);
        }
        $factory->replaceArgument(1, $arg);
    }
}
