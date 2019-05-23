<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Csa\Bundle\GuzzleBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Csa Guzzle Collector.
 *
 * @author Charles Sarrazin <charles@sarraz.in>
 */
class GuzzleExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('csa_guzzle_pretty_print', [$this, 'prettyPrint']),
            new TwigFilter('csa_guzzle_status_code_class', [$this, 'statusCodeClass']),
            new TwigFilter('csa_guzzle_format_duration', [$this, 'formatDuration']),
            new TwigFilter('csa_guzzle_short_uri', [$this, 'shortenUri']),
        ];
    }

    /**
     * Get functions.
     *
     * @return TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('csa_guzzle_detect_lang', [$this, 'detectLang']),
        ];
    }

    public function detectLang($body)
    {
        switch (true) {
            case 0 === strpos($body, '<?xml'):
                return 'xml';
            case 0 === strpos($body, '{'):
            case 0 === strpos($body, '['):
                return 'json';
            default:
                return 'markup';
        }
    }

    /**
     * Pretty print.
     *
     * @param $code
     * @param $lang
     *
     * @return false|string
     */
    public function prettyPrint($code, $lang)
    {
        switch ($lang) {
            case 'json':
                return json_encode(json_decode($code), JSON_PRETTY_PRINT);
            case 'xml':
                $xml = new \DomDocument('1.0');
                $xml->preserveWhiteSpace = false;
                $xml->formatOutput = true;
                $xml->loadXml($code, LIBXML_NOWARNING);

                return $xml->saveXml();
            default:
                return $code;
        }
    }

    public function statusCodeClass($statusCode)
    {
        switch (true) {
            case $statusCode >= 500:
                return 'server-error';
            case $statusCode >= 400:
                return 'client-error';
            case $statusCode >= 300:
                return 'redirection';
            case $statusCode >= 200:
                return 'success';
            case $statusCode >= 100:
                return 'informational';
            default:
                return 'unknown';
        }
    }

    public function formatDuration($seconds)
    {
        $formats = ['%.2f s', '%d ms', '%d µs'];

        while ($format = array_shift($formats)) {
            if ($seconds > 1) {
                break;
            }

            $seconds *= 1000;
        }

        return sprintf($format, $seconds);
    }

    public function shortenUri($uri)
    {
        $parts = parse_url($uri);

        return sprintf(
            '%s://%s%s',
            isset($parts['scheme']) ? $parts['scheme'] : 'http',
            $parts['host'],
            isset($parts['port']) ? (':'.$parts['port']) : ''
        );
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return 'csa_guzzle';
    }
}
