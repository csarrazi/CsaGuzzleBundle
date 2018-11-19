<?php

namespace Csa\Bundle\GuzzleBundle\Tests\Twig\Extension;

use Csa\Bundle\GuzzleBundle\Twig\Extension\GuzzleExtension;
use PHPUnit\Framework\TestCase;
use Twig_Filter;
use Twig_SimpleFunction;

class AaaGuzzleExtensionTest extends TestCase
{
    private const PRETTY_XML = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<content>test</content>
XML;

    private const PRETTY_JSON = <<<JSON
{
    "content": "test"
}
JSON;

    private const PRETTY_JSON2 = <<<JSON
[
    {
        "content": "test"
    }
]
JSON;

    private const PRETTY_PHP = <<<STRING
stdClass Object
(
    [content] => test
)
STRING;

    private const PRETTY_MARKUP = <<<STRING
body;test
STRING;

    /** @var GuzzleExtension */
    private $extension;

    protected function setUp(): void
    {
        $this->extension = new GuzzleExtension();
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

    public function langBodyDataSet(): array
    {
        return [
            'xml'   => [
                'lang'   => 'xml',
                'body'   => '<?xml version="1.0" encoding="UTF-8"?><content>test</content>',
                'pretty' => self::PRETTY_XML,
            ],
            'json'  => [
                'lang'   => 'json',
                'body'   => '{"content":"test"}',
                'pretty' => self::PRETTY_JSON,
            ],
            'json2' => [
                'lang'   => 'json',
                'body'   => '[{"content":"test"}]',
                'pretty' => self::PRETTY_JSON2,
            ],
            'php'   => [
                'lang'   => 'php',
                'body'   => 'O:8:"stdClass":1:{s:7:"content";s:4:"test";}',
                'pretty' => self::PRETTY_PHP,
            ],
            'other' => [
                'lang'   => 'markup',
                'body'   => 'body;test',
                'pretty' => self::PRETTY_MARKUP,
            ],
        ];
    }
}
