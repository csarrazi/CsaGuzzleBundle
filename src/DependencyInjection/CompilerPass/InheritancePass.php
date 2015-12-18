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

/**
 * Csa Guzzle definition inheritancePass.
 *
 * @author Sofiane HADDAG <sofiane.haddag@yahoo.fr>
 */
class InheritancePass implements CompilerPassInterface
{
    const TAG = 'csa_guzzle.inheritance';

    private $checkedNodes;

    public function process(ContainerBuilder $container)
    {
        $ids = $container->findTaggedServiceIds(self::TAG);

        if (!count($ids)) {
            return;
        }

        $this->checkedNodes = [];

        foreach ($ids as $id => $options) {
            $client = $container->findDefinition($id);
            $arguments = $client->getArguments();
            $config = isset($arguments[0]) ? $arguments[0] : [];
            $resolvedConfig = $this->resolveMerge($id, $client, $container, $config);

            $client->replaceArgument(0, $resolvedConfig);
        }
    }

    private function resolveMerge($id, $client, ContainerBuilder $container, array $config = [])
    {
        $tag = $client->getTag(self::TAG);
        if (!isset($tag[0]['extends'])) {
            throw new \RuntimeException(
                sprintf('The tag "%s" require an attribute named "extends" of the service "%s".', self::TAG, $id)
            );
        }

        $parent = $tag[0]['extends'];
        $this->checkCircularReference($id, $parent);
        $parentDefinition = $container->findDefinition($parent);
        $parentArguments = $parentDefinition->getArguments();
        $parentConfig = isset($parentArguments[0]) ? $parentArguments[0] : [];

        $config = array_merge($parentConfig, $config);

        if ($parentDefinition->hasTag(self::TAG)) {
            $config = $this->resolveMerge($parent, $parentDefinition, $container, $config);
        }

        return $config;
    }

    private function checkCircularReference($id, $parent)
    {
        if (!isset($this->checkedNodes[$parent]) || !in_array($id, $this->checkedNodes[$parent])) {
            $this->checkedNodes[$parent][] = $id;
            if (isset($this->checkedNodes[$id])) {
                if (in_array($parent, $this->checkedNodes[$id])) {
                    throw new \RuntimeException(
                        sprintf('Circular reference detected between services "%s" and "%s"', $parent, $id)
                    );
                }

                $this->checkedNodes[$parent] = array_merge(
                    $this->checkedNodes[$parent],
                    $this->checkedNodes[$id]
                );
            }
        }
    }
}
