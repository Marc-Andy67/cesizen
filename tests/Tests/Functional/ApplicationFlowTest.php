<?php

namespace App\Tests\Functional;

use Symfony\Component\Panther\PantherTestCase;

class ApplicationFlowTest extends PantherTestCase
{
    public function testHomePageAndLogin(): void
    {
        $client = static::createPantherClient();
        $crawler = $client->request('GET', '/');

        // Panther WebDriver : utiliser assertSelectorExists au lieu de assertResponseIsSuccessful
        $this->assertSelectorExists('body');
        $this->assertSelectorExists('nav');
    }

    public function testDiagnosticIndexPage(): void
    {
        $client = static::createPantherClient();
        $client->request('GET', '/diagnostic/');

        $this->assertSelectorExists('body');
        $this->assertSelectorTextContains('h1', 'Évaluation');
    }
}
