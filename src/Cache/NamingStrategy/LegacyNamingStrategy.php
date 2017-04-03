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

/**
 * @deprecated The LegacyNamingStrategy is deprecated since version 2.1, and will be removed in 3.0
 */
class LegacyNamingStrategy extends AbstractNamingStrategy
{
    private $withHost;

    /**
     * @param bool  $withHost
     * @param array $blacklist
     */
    public function __construct($withHost, array $blacklist = [])
    {
        $this->withHost = $withHost;

        parent::__construct($blacklist, false);
    }

    /**
     * {@inheritdoc}
     */
    public function filename(RequestInterface $request)
    {
        if ($this->withHost) {
            return $this->sanitize(call_user_func_array(
                'sprintf',
                array_merge(['%s_%s_%s-%s____%s'], $this->getPartsWithHost($request))
            ));
        }

        return $this->sanitize(call_user_func_array(
            'sprintf',
            array_merge(['%s_%s-%s____%s'], $this->getPartsWithoutHost($request))
        ));
    }

    private function getPartsWithHost(RequestInterface $request)
    {
        return [
            str_pad($request->getMethod(), 6, '_'),
            $request->getUri()->getHost(),
            urldecode(ltrim($request->getUri()->getPath(), '/')),
            urldecode($request->getUri()->getQuery()),
            $this->getFingerprint($request),
        ];
    }

    private function getPartsWithoutHost(RequestInterface $request)
    {
        return [
            str_pad($request->getMethod(), 6, '_'),
            urldecode(ltrim($request->getUri()->getPath(), '/')),
            urldecode($request->getUri()->getQuery()),
            $this->getFingerprint($request),
        ];
    }

    /**
     * Sanitizes a filename.
     *
     * @param string $filename
     *
     * @return string
     */
    private function sanitize($filename)
    {
        return preg_replace('/[^a-zA-Z0-9_+=@\-\?\.]/', '-', $filename);
    }
}
