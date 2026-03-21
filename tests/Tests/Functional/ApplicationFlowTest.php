<?php

namespace App\Tests\Functional;

use Symfony\Component\Panther\PantherTestCase;

class ApplicationFlowTest extends PantherTestCase
{
    public function testHomePageLoads(): void
    {
        $client = static::createPantherClient();
        $client->request('GET', '/');
        $this->assertSelectorExists('header');
    }

    public function testLoginPageLoads(): void
    {
        $client = static::createPantherClient();
        $client->request('GET', '/login');
        $client->takeScreenshot('var/panther/login-debug.png');
        $this->assertSelectorExists('input[type="email"]');
    }

    public function testDiagnosticIndexPage(): void
    {
        $client = static::createPantherClient();
        $client->request('GET', '/diagnostic/');
        $client->takeScreenshot('var/panther/diagnostic-debug.png');
        $this->assertSelectorExists('header');
        $this->assertSelectorTextContains('h1', 'Évaluez votre santé mentale');
    }
}
