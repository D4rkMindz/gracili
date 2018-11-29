<?php

namespace App\Test\Controller;

use App\Test\ApiTestCase;

/**
 * Class IndexControllerTest.
 *
 * @coversDefaultClass \App\Controller\IndexController
 */
class IndexControllerTest extends ApiTestCase
{
    /**
     * Test page found.
     */
    public function testPageNotFound()
    {
        $request = $this->createRequest('GET', '/');
        $response = $this->request($request);
        $this->assertEquals(200, $response->getStatusCode());
        //$this->assertEmpty((string)$response->getBody());
        $this->assertContains('html', $response->getBody()->__toString());
    }

    /**
     * Test home page with language.
     */
    public function testGetWithLang()
    {
        $request = $this->createRequest('GET', '/de');
        $response = $this->request($request);
        $this->assertEquals(200, $response->getStatusCode());
        //$this->assertEmpty((string)$response->getBody());
        $this->assertContains('html', $response->getBody()->__toString());
    }
}
