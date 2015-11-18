<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Csa\Bundle\GuzzleBundle;

use Csa\Bundle\GuzzleBundle\DependencyInjection\CompilerPass\LoaderPass;
use Csa\Bundle\GuzzleBundle\DependencyInjection\CompilerPass\SubscriberPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Csa Guzzle Bundle.
 *
 * @author Charles Sarrazin <charles@sarraz.in>
 */
class CsaGuzzleBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new SubscriberPass());
        $container->addCompilerPass(new LoaderPass());
    }
}
