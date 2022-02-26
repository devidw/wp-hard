<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class ResponseTest extends TestCase
{
    private string $url = 'http://hack.local';

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->client = new Client([
            'base_uri' => $this->url,
            'allow_redirects' => [
                'track_redirects' => true,
            ],
        ]);
    }

    /**
     * Test if the <meta name=`generator`> html tag is removed.
     */
    public function testRemoveGenerator(): void
    {
        $response = $this->client->get('/');

        $crawler = new Crawler($response->getBody()->getContents());

        $this->assertEquals(0, $crawler->filter('meta[name=generator]')->count());
    }

    /**
     * Test if the X-Powered-By header is removed.
     */
    public function testRemoveXPoweredBy(): void
    {
        $response = $this->client->get('/');

        $this->assertArrayNotHasKey('X-Powered-By', $response->getHeaders());
    }

    /**
     * Test if fetching `?author=1` redirects to the home page.
     */
    public function testRemoveAuthorProfiles(): void
    {
        $response = $this->client->get('/?author=1');

        // dump(
        //     $response->getHeaders()
        // );

        $lastUrlIndex = count($response->getHeader('X-Guzzle-Redirect-History')) - 1;

        $this->assertEquals(301, $response->getHeader('X-Guzzle-Redirect-Status-History')[$lastUrlIndex]);
        $this->assertEquals($this->url, $response->getHeader('X-Guzzle-Redirect-History')[$lastUrlIndex]);
    }
}
