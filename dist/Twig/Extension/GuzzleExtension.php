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

use Csa\Bundle\GuzzleBundle\GuzzleHttp\Subscriber\DebugSubscriber;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Csa Guzzle Collector
 *
 * @author Charles Sarrazin <charles@sarraz.in>
 */
class GuzzleExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('pretty_print', [$this, 'prettyPrint']),
            new \Twig_SimpleFilter('status_code_class', [$this, 'statusCodeClass']),
        ];
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('detect_lang', [$this, 'detectLang']),
        ];
    }

    public function detectLang($body)
    {
        switch (true) {
            case 0 === strpos($body, '<?xml'):
                return 'xml';
            case 0 === strpos($body, '{'):
                return 'json';
            default:
                return 'markup';
        }
    }

    public function prettyPrint($code, $lang)
    {
        switch ($lang) {
            case 'json':
                return json_encode(json_decode($code), JSON_PRETTY_PRINT);
            case 'xml':
                $xml = new \DomDocument('1.0');
                $xml->preserveWhiteSpace = false;
                $xml->formatOutput = true;
                $xml->loadXml($code);

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

    public function getName()
    {
        return 'csa_guzzle';
    }
}
