<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Factory\PhotoFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomeControllerTest extends WebTestCase
{
    public function testHomePageLoadsSuccessfully(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('h1');
    }

    public function testHomePageDisplaysPhotos(): void
    {   
        PhotoFactory::createMany(3);

        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorCount(3, '.photo-card');
    }
}
