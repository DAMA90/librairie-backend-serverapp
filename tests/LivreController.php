<?php
namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LivreControllerTest extends WebTestCase
{
    public function testPageListeLivres(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/livres');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Liste des livres');
    }
}
