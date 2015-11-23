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
 * Csa Guzzle definition loaders compiler pass.
 *
 * @author Charles Sarrazin <charles@sarraz.in>
 */
class LoaderPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $ids = $container->findTaggedServiceIds('csa_guzzle.description_loader');

        if (!count($ids)) {
            return;
        }

        $resolverDefinition = $container->findDefinition('csa_guzzle.description_loader.resolver');

        $loaders = [];

        foreach ($ids as $id => $options) {
            $loaders[] = new Reference($id);
        }

        $resolverDefinition->setArguments([$loaders]);
    }
}
