<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Csa\Bundle\GuzzleBundle\Factory;

/**
 * Csa Guzzle client compiler pass
 *
 * @author Charles Sarrazin <charles@sarraz.in>
 */
class ClientFactory
{
    private $class;

    /**
     * @param string $class The client's class
     */
    public function __construct($class)
    {
        $this->class = $class;
    }

    public function create(array $options = [])
    {
        return new $this->class($options);
    }
}
