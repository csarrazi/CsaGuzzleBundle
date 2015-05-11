<?php

namespace Csa\Bundle\GuzzleBundle\Tests\HttpFoundation;

use Csa\Bundle\GuzzleBundle\HttpFoundation\StreamResponse;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;

class StreamResponseTest extends \PHPUnit_Framework_TestCase
{
    public function testNormalOutput()
    {
        $this->expectOutputString('this should not be streamed');

        $mock = new Response(200, [], Stream::factory('this should not be streamed'));
        $response = new StreamResponse($mock);
        $response->send();
    }

    public function testChunkedOutput()
    {
        $this->expectOutputString("a\r\nthis shoul\r\na\r\nd not be s\r\n7\r\ntreamed\r\n0\r\n\r\n");

        $mock = new Response(200, ['Transfer-Encoding' => 'chunked'], Stream::factory('this should not be streamed'));
        $response = new StreamResponse($mock, 10);
        $response->send();
    }
}
