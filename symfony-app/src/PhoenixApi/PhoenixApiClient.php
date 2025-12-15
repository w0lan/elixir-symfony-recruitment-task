<?php

declare(strict_types=1);

namespace App\PhoenixApi;

use App\PhoenixApi\Dto\UserDto;
use App\PhoenixApi\Dto\UserInput;
use App\PhoenixApi\Dto\UsersListMeta;
use App\PhoenixApi\Dto\UsersListQuery;
use App\PhoenixApi\Dto\UsersListResult;
use App\PhoenixApi\Exception\InvalidPayloadException;
use App\PhoenixApi\Exception\PhoenixApiException;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class PhoenixApiClient
{
    public function __construct(
        private HttpClientInterface $client,
        private string $importToken = '',
    ) {
    }

    public function listUsers(UsersListQuery $query): UsersListResult
    {
        $payload = $this->json('GET', '/users', [
            'query' => $query->toQuery(),
        ]);

        $data = $payload['data'] ?? null;
        $metaRaw = $payload['meta'] ?? null;

        if (!\is_array($data)) {
            throw new PhoenixApiException(200, 'invalid_response', 'Invalid response');
        }

        if (!\is_array($metaRaw)) {
            throw new PhoenixApiException(200, 'invalid_response', 'Invalid response');
        }

        $users = [];

        foreach ($data as $row) {
            if (!\is_array($row)) {
                throw new PhoenixApiException(200, 'invalid_response', 'Invalid response');
            }

            try {
                $users[] = UserDto::fromArray($row);
            } catch (InvalidPayloadException $e) {
                throw new PhoenixApiException(200, 'invalid_response', 'Invalid response', [], $e);
            }
        }

        $meta = UsersListMeta::fromArray($metaRaw);

        return new UsersListResult($users, $meta);
    }

    public function getUser(int $id): UserDto
    {
        $payload = $this->json('GET', "/users/{$id}");

        if (!isset($payload['data']) || !\is_array($payload['data'])) {
            throw new PhoenixApiException(200, 'invalid_response', 'Invalid response');
        }

        try {
            return UserDto::fromArray($payload['data']);
        } catch (InvalidPayloadException $e) {
            throw new PhoenixApiException(200, 'invalid_response', 'Invalid response', [], $e);
        }
    }

    public function createUser(UserInput $input): UserDto
    {
        $payload = $this->json('POST', '/users', [
            'json' => $input->toArray(),
        ], expectedStatus: 201);

        if (!isset($payload['data']) || !\is_array($payload['data'])) {
            throw new PhoenixApiException(201, 'invalid_response', 'Invalid response');
        }

        try {
            return UserDto::fromArray($payload['data']);
        } catch (InvalidPayloadException $e) {
            throw new PhoenixApiException(201, 'invalid_response', 'Invalid response', [], $e);
        }
    }

    public function updateUser(int $id, UserInput $input): UserDto
    {
        $payload = $this->json('PUT', "/users/{$id}", [
            'json' => $input->toArray(),
        ]);

        if (!isset($payload['data']) || !\is_array($payload['data'])) {
            throw new PhoenixApiException(200, 'invalid_response', 'Invalid response');
        }

        try {
            return UserDto::fromArray($payload['data']);
        } catch (InvalidPayloadException $e) {
            throw new PhoenixApiException(200, 'invalid_response', 'Invalid response', [], $e);
        }
    }

    public function deleteUser(int $id): void
    {
        $this->json('DELETE', "/users/{$id}", expectedStatus: 204);
    }

    public function importUsers(): int
    {
        $options = [];

        if ($this->importToken !== '') {
            $options['headers'] = [
                'Authorization' => 'Bearer '.$this->importToken,
            ];
        }

        $payload = $this->json('POST', '/import', $options);

        $inserted = $payload['data']['inserted'] ?? null;

        if (!\is_int($inserted) && !(\is_string($inserted) && ctype_digit($inserted))) {
            throw new PhoenixApiException(200, 'invalid_response', 'Invalid response');
        }

        return (int) $inserted;
    }

    private function json(string $method, string $path, array $options = [], int $expectedStatus = 200): array
    {
        try {
            $response = $this->client->request($method, $path, $options);
        } catch (TransportExceptionInterface $e) {
            throw new PhoenixApiException(0, 'transport_error', 'Transport error', [], $e);
        }

        try {
            $status = $response->getStatusCode();
        } catch (TransportExceptionInterface $e) {
            throw new PhoenixApiException(0, 'transport_error', 'Transport error', [], $e);
        }

        if ($status === $expectedStatus && $expectedStatus === 204) {
            return [];
        }

        try {
            $payload = $response->toArray(false);
        } catch (TransportExceptionInterface $e) {
            throw new PhoenixApiException(0, 'transport_error', 'Transport error', [], $e);
        } catch (DecodingExceptionInterface $e) {
            throw new PhoenixApiException($status, 'invalid_response', 'Invalid response', [], $e);
        }

        if ($status !== $expectedStatus) {
            throw PhoenixApiException::fromResponse($status, $payload);
        }

        return \is_array($payload) ? $payload : [];
    }
}

