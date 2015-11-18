<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Csa\Bundle\GuzzleBundle\Description\CacheWarmer;

use Csa\Bundle\GuzzleBundle\Factory\DescriptionFactory;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmer;

/**
 * Warms up the service description cache.
 *
 * @author Charles Sarrazin <charles@sarraz.in>
 */
class DescriptionCacheWarmer extends CacheWarmer
{
    private $factory;

    public function __construct(DescriptionFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir)
    {
        $this->factory->loadDescriptions();
    }

    /**
     * {@inheritdoc}
     */
    public function isOptional()
    {
        return true;
    }
}
