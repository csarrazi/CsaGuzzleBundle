<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Csa\Bundle\GuzzleBundle\Description\Loader;

/**
 * Description loader interface.
 *
 * @author Charles Sarrazin <charles@sarraz.in>
 */
interface LoaderInterface
{
    /**
     * Loads a description from the specified resource.
     *
     * @param string $resource
     *
     * @return array
     */
    public function load($resource);

    /**
     * Checks whether the loader is able to load the specified resource.
     *
     * @param string $resource
     *
     * @return bool
     */
    public function supports($resource);
}
