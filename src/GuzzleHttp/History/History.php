<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Csa\Bundle\GuzzleBundle\GuzzleHttp\History;

use GuzzleHttp\TransferStats;
use Psr\Http\Message\RequestInterface;

class History extends \SplObjectStorage
{
    public function mergeInfo(RequestInterface $request, array $info)
    {
        $info = array_merge(
            ['response' => null, 'error' => null, 'info' => null],
            array_filter($this->contains($request) ? $this[$request] : []),
            array_filter($info)
        );

        $this->attach($request, $info);
    }

    public function addStats(TransferStats $stats)
    {
        $this->mergeInfo($stats->getRequest(), ['info' => $stats->getHandlerStats()]);
    }
}
