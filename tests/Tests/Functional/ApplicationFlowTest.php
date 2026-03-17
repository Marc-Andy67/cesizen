<?php

namespace App\Tests\Functional;

use Symfony\Component\Panther\PantherTestCase;

class ApplicationFlowTest extends PantherTestCase
{
    public function testHomePageAndLogin(): void
    {
        // Use Panther client
        $client = static::createPantherClient();

        // 1. Visit Home Page
        $crawler = $client->request('GET', '/');
        
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Evaluez et maîtrisez');
        
        // 2. Click on "Se connecter" from Home Page
        $link = $crawler->selectLink('Commencer mon évaluation')->link();
        // Since it might go to register first, let's just go directly to login to test auth
        $crawler = $client->request('GET', '/connexion');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form[action="/connexion"]');

        // Note: For a real test with fixtures, we would fill the form and submit:
        /*
        $form = $crawler->selectButton('Se connecter')->form([
            'email' => 'user1@cesizen.fr',
            'password' => 'UserPwd123!'
        ]);
        $client->submit($form);
        $this->assertResponseRedirects('/profil');
        */
    }

    public function testDiagnosticIndexPage(): void
    {
        $client = static::createPantherClient();
        $crawler = $client->request('GET', '/diagnostic/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Évaluation');
        $this->assertSelectorTextContains('a.btn-primary', 'Démarrer le test'); // "Démarrer le test" button
    }
}
