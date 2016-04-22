<?php

namespace Pcp\Tests\Http;

use Pcp\Http\CurlClient;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @group internet
 */
class CurlClientTest extends TestCase
{
    protected $client;

    protected function setUp()
    {
        if (!function_exists('curl_init')) {
            $this->markTestSkipped('cURL has to be enabled.');
        }

        $this->client = new CurlClient();
    }

    protected function tearDown()
    {
        $this->client = null;
    }

    public function testGetBody()
    {
        $content = $this->client->getBody('http://www.google.com');
        $this->assertNotNull($content);
        $this->assertContains('google', $content);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testGetBodyThrowsRuntimeException()
    {
        $this->client->getBody('/null');
    }
}
