<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Thiago Cordeiro <thiagoguetten@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Csa\Bundle\GuzzleBundle\Tests\Twig\Extension;

use Csa\Bundle\GuzzleBundle\Twig\Extension\GuzzleExtension;
use PHPUnit\Framework\TestCase;
use Twig_Filter;
use Twig_SimpleFunction;

class GuzzleExtensionTest extends TestCase
{
    private const PRETTY_XML = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<content>test</content>
XML;

    private const PRETTY_JSON = <<<'JSON'
{
    "content": "test"
}
JSON;

    private const PRETTY_JSON2 = <<<'JSON'
[
    {
        "content": "test"
    }
]
JSON;

    private const PRETTY_PHP = <<<'STRING'
stdClass Object
(
    [content] => test
)
STRING;

    private const PRETTY_MARKUP = <<<'STRING'
body;test
STRING;

    /** @var GuzzleExtension */
    private $extension;

    protected function setUp(): void
    {
        $this->extension = new GuzzleExtension();
    }

    public function testGetFilters(): void
    {
        $filters = $this->extension->getFilters();

        $names = array_map(function (Twig_Filter $filter) {
            return $filter->getName();
        }, $filters);

        $this->assertEquals([
            'csa_guzzle_pretty_print',
            'csa_guzzle_status_code_class',
            'csa_guzzle_format_duration',
            'csa_guzzle_short_uri',
        ], $names);
    }

    public function testFetFunctions(): void
    {
        $functions = $this->extension->getFunctions();

        $names = array_map(function (Twig_SimpleFunction $filter) {
            return $filter->getName();
        }, $functions);

        $this->assertEquals(['csa_guzzle_detect_lang'], $names);
    }

    /**
     * @dataProvider langBodyDataSet
     */
    public function testDetectLang(string $lang, string $body): void
    {
        $detected = $this->extension->detectLang($body);

        $this->assertEquals($lang, $detected);
    }

    /**
     * @dataProvider langBodyDataSet
     */
    public function testPrettyPrint(string $lang, string $body, string $pretty): void
    {
        $rendered = $this->extension->prettyPrint($body, $lang);

        $this->assertEquals(trim($pretty), trim($rendered));
    }

    /**
     * @dataProvider statusCodeMessageDataSet
     */
    public function testStatusCodeClass(int $statusCode, string $expectedMessage): void
    {
        $message = $this->extension->statusCodeClass($statusCode);

        $this->assertEquals($expectedMessage, $message);
    }

    /**
     * @dataProvider formatDurationDataSet
     */
    public function testFormatDuration(int $seconds, string $expectedFormatted): void
    {
        $formatted = $this->extension->formatDuration($seconds);

        $this->assertEquals($expectedFormatted, $formatted);
    }

    public function testShortenUri(): void
    {
        $uri = 'http://localhost/foo/bar?foo=bar';

        $short = $this->extension->shortenUri($uri);

        $this->assertEquals('http://localhost', $short);
    }

    public function testGetName(): void
    {
        $name = $this->extension->getName();

        $this->assertEquals('csa_guzzle', $name);
    }

    public function langBodyDataSet(): array
    {
        return [
            'xml'   => [
                'lang' => 'xml',
                'body' => '<?xml version="1.0" encoding="UTF-8"?><content>test</content>',
                'pretty' => self::PRETTY_XML,
            ],
            'json'  => [
                'lang' => 'json',
                'body' => '{"content":"test"}',
                'pretty' => self::PRETTY_JSON,
            ],
            'json2' => [
                'lang' => 'json',
                'body' => '[{"content":"test"}]',
                'pretty' => self::PRETTY_JSON2,
            ],
            'php'   => [
                'lang' => 'php',
                'body' => 'O:8:"stdClass":1:{s:7:"content";s:4:"test";}',
                'pretty' => self::PRETTY_PHP,
            ],
            'other' => [
                'lang' => 'markup',
                'body' => 'body;test',
                'pretty' => self::PRETTY_MARKUP,
            ],
        ];
    }

    public function statusCodeMessageDataSet(): array
    {
        return [
            'status 599' => ['statusCode' => 599, 'expectedMessage' => 'server-error'],
            'status 500' => ['statusCode' => 500, 'expectedMessage' => 'server-error'],
            'status 499' => ['statusCode' => 499, 'expectedMessage' => 'client-error'],
            'status 400' => ['statusCode' => 400, 'expectedMessage' => 'client-error'],
            'status 399' => ['statusCode' => 399, 'expectedMessage' => 'redirection'],
            'status 300' => ['statusCode' => 300, 'expectedMessage' => 'redirection'],
            'status 299' => ['statusCode' => 299, 'expectedMessage' => 'success'],
            'status 200' => ['statusCode' => 200, 'expectedMessage' => 'success'],
            'status 199' => ['statusCode' => 199, 'expectedMessage' => 'informational'],
            'status 100' => ['statusCode' => 100, 'expectedMessage' => 'informational'],
            'status 99' => ['statusCode' => 99, 'expectedMessage' => 'unknown'],
            'status 0' => ['statusCode' => 0, 'expectedMessage' => 'unknown'],
        ];
    }

    public function formatDurationDataSet(): array
    {
        return [
            '15 Seconds' => ['seconds' => 15, 'formatted' => '15.00 s'],
            '92 Seconds' => ['seconds' => 92, 'formatted' => '92.00 s'],
            '136 Seconds' => ['seconds' => 136, 'formatted' => '136.00 s'],
            '532 Seconds' => ['seconds' => 532, 'formatted' => '532.00 s'],
            '1689 Seconds' => ['seconds' => 1689, 'formatted' => '1689.00 s'],
        ];
    }
}
