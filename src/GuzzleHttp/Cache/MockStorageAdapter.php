<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Csa\Bundle\GuzzleBundle\GuzzleHttp\Cache;

use Csa\Bundle\GuzzleBundle\GuzzleHttp\Cache\NamingStrategy\LegacyNamingStrategy;
use Csa\Bundle\GuzzleBundle\GuzzleHttp\Cache\NamingStrategy\NamingStrategyInterface;
use Csa\Bundle\GuzzleBundle\GuzzleHttp\Cache\NamingStrategy\SubfolderNamingStrategy;
use GuzzleHttp\Psr7;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Filesystem\Filesystem;

class MockStorageAdapter implements StorageAdapterInterface
{
    private $storagePath;
    /** @var NamingStrategyInterface[] */
    private $namingStrategies = [];
    private $responseHeadersBlacklist = [
        'X-Guzzle-Cache',
    ];

    /**
     * @param string $storagePath
     * @param array  $requestHeadersBlacklist
     * @param array  $responseHeadersBlacklist
     */
    public function __construct($storagePath, array $requestHeadersBlacklist = [], array $responseHeadersBlacklist = [])
    {
        $this->storagePath = $storagePath;

        $this->namingStrategies[] = new SubfolderNamingStrategy($requestHeadersBlacklist);
        $this->namingStrategies[] = new LegacyNamingStrategy(true, $requestHeadersBlacklist);
        $this->namingStrategies[] = new LegacyNamingStrategy(false, $requestHeadersBlacklist);

        if (!empty($responseHeadersBlacklist)) {
            $this->responseHeadersBlacklist  = $responseHeadersBlacklist;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(RequestInterface $request)
    {
        foreach ($this->namingStrategies as $strategy) {
            if (file_exists($filename = $this->getFilename($strategy->filename($request)))) {
                return Psr7\parse_response(file_get_contents($filename));
            }
        }

        throw new \RuntimeException('Record not found.');
    }

    /**
     * {@inheritdoc}
     */
    public function save(RequestInterface $request, ResponseInterface $response)
    {
        foreach ($this->responseHeadersBlacklist as $header) {
            $response = $response->withoutHeader($header);
        }

        list($strategy) = $this->namingStrategies;

        $filename = $this->getFilename($strategy->filename($request));

        $fs = new Filesystem();
        $fs->mkdir(dirname($filename));

        file_put_contents($filename, Psr7\str($response));
        $response->getBody()->rewind();
    }

    /**
     * Prefixes the generated file path with the adapter's storage path.
     *
     * @param string $name
     *
     * @return string The path to the mock file
     */
    private function getFilename($name)
    {
        return $this->storagePath.'/'.$name.'.txt';
    }
}
