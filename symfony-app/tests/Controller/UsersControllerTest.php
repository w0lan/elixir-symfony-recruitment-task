<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\PhoenixApi\PhoenixApiClientInterface;
use App\Tests\Fake\InMemoryPhoenixApiClient;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class UsersControllerTest extends WebTestCase
{
    private function createClientWithFakeApi(): object
    {
        $client = static::createClient();
        $client->disableReboot();
        $apiClient = new InMemoryPhoenixApiClient();
        $client->getContainer()->set(PhoenixApiClientInterface::class, $apiClient);

        return (object) ['client' => $client, 'api' => $apiClient];
    }

    public function testIndexRendersUsersListOnSuccess(): void
    {
        $env = $this->createClientWithFakeApi();

        $env->api->addUser(1, 'Jan', 'Kowalski', '1990-05-15', 'male');
        $env->api->addUser(2, 'Anna', 'Nowak', '2000-12-31', 'female');

        $client = $env->client;

        $crawler = $client->request('GET', '/users');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Jan', $client->getResponse()->getContent());
        $this->assertStringContainsString('Anna', $client->getResponse()->getContent());
    }

    public function testIndexHandlesFiltering(): void
    {
        $env = $this->createClientWithFakeApi();

        $env->api->addUser(1, 'Jan', 'Kowalski', '1990-05-15', 'male');
        $env->api->addUser(2, 'Anna', 'Nowak', '2000-12-31', 'female');

        $crawler = $env->client->request('GET', '/users?first_name=Jan');

        $this->assertResponseIsSuccessful();
        $content = $env->client->getResponse()->getContent();
        $this->assertStringContainsString('Jan', $content);
        $this->assertStringNotContainsString('Anna', $content);
    }

    public function testNewRendersForm(): void
    {
        $env = $this->createClientWithFakeApi();

        $crawler = $env->client->request('GET', '/users/new');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testNewCreatesUserOnValidSubmit(): void
    {
        $env = $this->createClientWithFakeApi();

        $crawler = $env->client->request('GET', '/users/new');

        $form = $crawler->selectButton('Create')->form([
            'user[firstName]' => 'Jan',
            'user[lastName]' => 'Kowalski',
            'user[birthdate]' => '1990-05-15',
            'user[gender]' => 'male',
        ]);

        $env->client->submit($form);

        $this->assertResponseRedirects('/users');
        $env->client->followRedirect();

        $this->assertSelectorTextContains('.alert-success', 'User created');
        $this->assertSame(1, $env->api->getUsersCount());
    }

    public function testEditLoadsUser(): void
    {
        $env = $this->createClientWithFakeApi();

        $env->api->addUser(1, 'Jan', 'Kowalski', '1990-05-15', 'male');

        $crawler = $env->client->request('GET', '/users/1/edit');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
        $this->assertStringContainsString('Jan', $env->client->getResponse()->getContent());
    }

    public function testEditThrows404OnNotFound(): void
    {
        $env = $this->createClientWithFakeApi();

        $env->client->request('GET', '/users/999/edit');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testEditUpdatesUserOnValidSubmit(): void
    {
        $env = $this->createClientWithFakeApi();

        $env->api->addUser(1, 'Jan', 'Kowalski', '1990-05-15', 'male');

        $crawler = $env->client->request('GET', '/users/1/edit');

        $form = $crawler->selectButton('Save')->form([
            'user[firstName]' => 'Anna',
            'user[lastName]' => 'Nowak',
            'user[birthdate]' => '2000-12-31',
            'user[gender]' => 'female',
        ]);

        $env->client->submit($form);

        $this->assertResponseRedirects('/users');
        $env->client->followRedirect();

        $this->assertSelectorTextContains('.alert-success', 'User updated');

        $user = $env->api->getUser(1);
        $this->assertSame('Anna', $user->firstName);
    }

    public function testDeleteDeletesUserOnValidCsrf(): void
    {
        $env = $this->createClientWithFakeApi();

        $env->api->addUser(1, 'Jan', 'Kowalski', '1990-05-15', 'male');

        $crawler = $env->client->request('GET', '/users');

        $form = $crawler->filter('form[action="/users/1/delete"]')->form();
        $env->client->submit($form);

        $this->assertResponseRedirects('/users');
        $env->client->followRedirect();

        $this->assertSelectorTextContains('.alert-success', 'User deleted');
        $this->assertSame(0, $env->api->getUsersCount());
    }

    public function testDeleteHandlesNotFound(): void
    {
        $env = $this->createClientWithFakeApi();

        $env->api->addUser(999, 'Jan', 'Kowalski', '1990-05-15', 'male');

        $crawler = $env->client->request('GET', '/users');
        $deleteForm = $crawler->filter('form[action="/users/999/delete"]')->form();

        $env->api->deleteUser(999);

        $env->client->submit($deleteForm);

        $this->assertResponseRedirects('/users');
        $env->client->followRedirect();

        $this->assertSelectorTextContains('.alert-danger', 'User not found');
    }

    public function testImportCallsImportUsers(): void
    {
        $env = $this->createClientWithFakeApi();

        $crawler = $env->client->request('GET', '/users');
        $importForm = $crawler->filter('form[action*="import"]')->form();

        $env->client->submit($importForm);

        $this->assertResponseRedirects('/users');
        $env->client->followRedirect();

        $this->assertSelectorTextContains('.alert-success', 'Imported: 100');
    }
}
