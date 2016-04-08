<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Csa\Bundle\GuzzleBundle\Cache\NamingStrategy;

use Psr\Http\Message\RequestInterface;

class SubfolderNamingStrategy extends AbstractNamingStrategy
{
    /**
     * {@inheritdoc}
     */
    public function filename(RequestInterface $request)
    {
        $filename = $request->getUri()->getHost();

        if ('' !== $path = urldecode(ltrim($request->getUri()->getPath(), '/'))) {
            $filename .= '/'.$path;
        }

        $filename .= '/'.$request->getMethod();
        $filename .= '_'.$this->getFingerprint($request);

        return $filename;
    }
}
