<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Csa\Bundle\GuzzleBundle\DependencyInjection\Configurator;

use GuzzleHttp\ClientInterface;

class ClientConfigurator
{
    /**
     * @var array|\Traversable
     */
    private $subscribers;

    /**
     * @var callable|null
     */
    private $parentConfigurator;

    /**
     * @param array|\Traversable $subscribers
     * @param callable|null      $parentConfigurator
     */
    public function __construct($subscribers = [], $parentConfigurator = null)
    {
        $this->subscribers = $subscribers;
        $this->parentConfigurator = $parentConfigurator;
    }

    /**
     * @param ClientInterface $client
     */
    public function configure(ClientInterface $client)
    {
        if ($this->parentConfigurator) {
            call_user_func($this->parentConfigurator, $client);
        }

        foreach ($this->subscribers as $subscriber) {
            $client->getEmitter()->attach($subscriber);
        }
    }
}
