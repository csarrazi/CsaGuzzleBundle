<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Csa\Bundle\GuzzleBundle\Tests\GuzzleHttp\Cache;

use Csa\Bundle\GuzzleBundle\GuzzleHttp\Cache\MockStorageAdapter;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Filesystem\Filesystem;

class MockStorageAdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var string
     */
    private $tmpDir;

    protected function setUp()
    {
        $this->fs = new Filesystem();

        $this->tmpDir = sys_get_temp_dir().'/csa_guzzle_bundle_'.uniqid();
        $this->fs->mkdir($this->tmpDir);
    }

    protected function tearDown()
    {
        $this->fs->remove($this->tmpDir);
    }

    public function testFetch()
    {
        /* Mock with host in the file name look for the file with hostname first */

        $mockStorage = new MockStorageAdapter(__DIR__.'/../../Fixtures/mocks');
        $response = $mockStorage->fetch($this->getRequestMock());

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testSave()
    {
        $request = $this->getRequestMock();
        $mockStorage = new MockStorageAdapter($this->tmpDir, [], ['X-Foo']);
        $mockStorage->save($request, new Response(404, ['X-Foo' => 'bar'], 'Not found'));
        $response = $mockStorage->fetch($request);

        $this->assertCount(1, glob($this->tmpDir.'/*'));
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(404, $response->getStatusCode());

        $this->assertFalse($response->hasHeader('X-Foo'));
    }

    private function getRequestMock()
    {
        return new Request('GET', 'http://google.com/', ['Accept' => 'text/html']);
    }
}
