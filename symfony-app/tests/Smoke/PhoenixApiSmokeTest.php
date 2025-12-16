<?php

declare(strict_types=1);

namespace App\Tests\Smoke;

use App\PhoenixApi\Dto\UserInput;
use App\PhoenixApi\Dto\UsersListQuery;
use App\PhoenixApi\Exception\PhoenixApiException;
use App\PhoenixApi\PhoenixApiClient;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

final class PhoenixApiSmokeTest extends TestCase
{
    private PhoenixApiClient $client;

    /**
     * @var list<int>
     */
    private array $createdUserIds = [];

    protected function setUp(): void
    {
        $baseUrl = (string) (getenv('PHOENIX_BASE_URL') ?: 'http://phoenix:4000');

        $http = HttpClient::create([
            'base_uri' => $baseUrl,
            'timeout' => 10,
        ]);

        $this->client = new PhoenixApiClient($http);

        try {
            $response = $http->request('GET', '/health.json');
            $status = $response->getStatusCode();
        } catch (TransportExceptionInterface) {
            $this->markTestSkipped('Phoenix API is not reachable.');

            return;
        }

        if (200 !== $status) {
            $this->markTestSkipped('Phoenix API is not healthy.');
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->createdUserIds as $id) {
            try {
                $this->client->deleteUser($id);
            } catch (PhoenixApiException) {
            }
        }
    }

    public function testCrudWorkflow(): void
    {
        $created = $this->client->createUser(new UserInput(
            firstName: 'Smoke',
            lastName: $this->uniqueLastName('Crud'),
            birthdate: new DateTimeImmutable('1990-05-15'),
            gender: 'male',
        ));

        $this->createdUserIds[] = $created->id;

        $fetched = $this->client->getUser($created->id);
        $this->assertSame($created->id, $fetched->id);

        $updated = $this->client->updateUser($created->id, new UserInput(
            firstName: 'SmokeUpdated',
            lastName: $created->lastName,
            birthdate: new DateTimeImmutable('2000-12-31'),
            gender: 'female',
        ));

        $this->assertSame('SmokeUpdated', $updated->firstName);
        $this->assertSame('female', $updated->gender);
        $this->assertSame('2000-12-31', $updated->birthdate);

        $this->client->deleteUser($created->id);
        $this->createdUserIds = array_values(array_filter($this->createdUserIds, static fn (int $id) => $id !== $created->id));

        try {
            $this->client->getUser($created->id);
            $this->fail('Expected not_found after delete.');
        } catch (PhoenixApiException $e) {
            $this->assertSame(404, $e->statusCode());
            $this->assertSame(PhoenixApiException::CODE_NOT_FOUND, $e->apiCode());
        }
    }

    public function testListUsersFiltersSortsAndPaginates(): void
    {
        $lastNamePrefix = $this->uniqueLastName('List');

        $a = $this->client->createUser(new UserInput(
            firstName: 'AAA',
            lastName: $lastNamePrefix.'A',
            birthdate: new DateTimeImmutable('1990-01-01'),
            gender: 'male',
        ));
        $b = $this->client->createUser(new UserInput(
            firstName: 'BBB',
            lastName: $lastNamePrefix.'B',
            birthdate: new DateTimeImmutable('1990-01-02'),
            gender: 'female',
        ));

        $this->createdUserIds[] = $a->id;
        $this->createdUserIds[] = $b->id;

        $query = new UsersListQuery(
            firstName: null,
            lastName: $lastNamePrefix,
            gender: null,
            birthdateFrom: null,
            birthdateTo: null,
            sortBy: 'first_name',
            sortDir: 'asc',
            page: 1,
            pageSize: 100,
        );

        $result = $this->client->listUsers($query);
        $this->assertGreaterThanOrEqual(2, $result->meta->total);

        $matching = array_values(array_filter(
            $result->users,
            static fn ($u) => str_starts_with($u->lastName, $lastNamePrefix),
        ));

        $this->assertCount(2, $matching);
        $this->assertSame('AAA', $matching[0]->firstName);
        $this->assertSame('BBB', $matching[1]->firstName);

        $page1 = $this->client->listUsers(new UsersListQuery(
            firstName: null,
            lastName: $lastNamePrefix,
            gender: null,
            birthdateFrom: null,
            birthdateTo: null,
            sortBy: 'first_name',
            sortDir: 'asc',
            page: 1,
            pageSize: 1,
        ));

        $page2 = $this->client->listUsers(new UsersListQuery(
            firstName: null,
            lastName: $lastNamePrefix,
            gender: null,
            birthdateFrom: null,
            birthdateTo: null,
            sortBy: 'first_name',
            sortDir: 'asc',
            page: 2,
            pageSize: 1,
        ));

        $this->assertCount(1, $page1->users);
        $this->assertCount(1, $page2->users);
        $this->assertNotSame($page1->users[0]->id, $page2->users[0]->id);
    }

    private function uniqueLastName(string $suffix): string
    {
        return sprintf('SmokeTest_%s_%s', $suffix, bin2hex(random_bytes(6)));
    }
}
