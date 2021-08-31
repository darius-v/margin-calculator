<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class OrderControllerTest extends WebTestCase
{
    /**
     * Those few clicks can do manually fast, a lot of people think autotest this is not necessary. But adding for
     * demonstration.
     */
    public function testCalculatorLoads(): void
    {
        // This calls KernelTestCase::bootKernel(), and creates a
        // "client" that is acting as the browser
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Margin calculator');
        $this->assertCount(2, $crawler->filter('label'));
        $this->assertCount(2, $crawler->filter('button'));
        $this->assertCount(1, $crawler->filter('a'));
    }
}