<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthControllerTest extends WebTestCase
{
    public function testLoginPageLoads(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/auth/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input[name="username"]');
        $this->assertSelectorExists('input[name="token"]');
    }

    public function testLoginWithInvalidCredentialsFails(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/auth/login');

        $form = $crawler->selectButton('Sign In')->form([
            'username' => 'invalid',
            'token' => 'invalid',
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/auth/login');
        $client->followRedirect();

        // Assert error message exists if implemented
        // $this->assertSelectorExists('.alert-danger');
    }
}
