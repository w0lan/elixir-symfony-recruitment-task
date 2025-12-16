<?php

declare(strict_types=1);

namespace App\Tests\Fake;

use App\PhoenixApi\Dto\UserDto;
use App\PhoenixApi\Dto\UserInput;
use App\PhoenixApi\Dto\UsersListMeta;
use App\PhoenixApi\Dto\UsersListQuery;
use App\PhoenixApi\Dto\UsersListResult;
use App\PhoenixApi\Exception\PhoenixApiException;
use App\PhoenixApi\PhoenixApiClientInterface;

final class InMemoryPhoenixApiClient implements PhoenixApiClientInterface
{
    private int $nextId = 1;
    private array $users = [];
    private int $importedCount = 0;

    public function addUser(int $id, string $firstName, string $lastName, string $birthdate, string $gender): void
    {
        $this->users[$id] = [
            'id' => $id,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'birthdate' => $birthdate,
            'gender' => $gender,
            'inserted_at' => '2025-12-15T10:00:00Z',
            'updated_at' => '2025-12-15T10:00:00Z',
        ];

        if ($id >= $this->nextId) {
            $this->nextId = $id + 1;
        }
    }

    public function listUsers(UsersListQuery $query): UsersListResult
    {
        $filtered = $this->users;

        if (null !== $query->firstName && '' !== $query->firstName) {
            $filtered = array_filter($filtered, fn ($u) => str_contains(strtolower($u['first_name']), strtolower($query->firstName)));
        }

        if (null !== $query->lastName && '' !== $query->lastName) {
            $filtered = array_filter($filtered, fn ($u) => str_contains(strtolower($u['last_name']), strtolower($query->lastName)));
        }

        if (null !== $query->gender && '' !== $query->gender) {
            $filtered = array_filter($filtered, fn ($u) => $u['gender'] === $query->gender);
        }

        if (null !== $query->birthdateFrom) {
            $from = $query->birthdateFrom->format('Y-m-d');
            $filtered = array_filter($filtered, fn ($u) => $u['birthdate'] >= $from);
        }

        if (null !== $query->birthdateTo) {
            $to = $query->birthdateTo->format('Y-m-d');
            $filtered = array_filter($filtered, fn ($u) => $u['birthdate'] <= $to);
        }

        $sortField = match ($query->sortBy) {
            'id' => 'id',
            'first_name' => 'first_name',
            'last_name' => 'last_name',
            'birthdate' => 'birthdate',
            'gender' => 'gender',
            'inserted_at' => 'inserted_at',
            'updated_at' => 'updated_at',
            default => 'id',
        };

        usort($filtered, function ($a, $b) use ($sortField, $query) {
            $result = $a[$sortField] <=> $b[$sortField];

            return 'desc' === $query->sortDir ? -$result : $result;
        });

        $total = count($filtered);
        $offset = ($query->page - 1) * $query->pageSize;
        $paginated = array_slice($filtered, $offset, $query->pageSize);

        $users = array_map(fn ($data) => UserDto::fromArray($data), $paginated);
        $meta = new UsersListMeta($query->page, $query->pageSize, $total);

        return new UsersListResult($users, $meta);
    }

    public function getUser(int $id): UserDto
    {
        if (!isset($this->users[$id])) {
            throw new PhoenixApiException(404, 'not_found', 'User not found');
        }

        return UserDto::fromArray($this->users[$id]);
    }

    public function createUser(UserInput $input): UserDto
    {
        $id = $this->nextId++;

        $this->users[$id] = [
            'id' => $id,
            'first_name' => $input->firstName,
            'last_name' => $input->lastName,
            'birthdate' => $input->birthdate->format('Y-m-d'),
            'gender' => $input->gender,
            'inserted_at' => '2025-12-15T10:00:00Z',
            'updated_at' => '2025-12-15T10:00:00Z',
        ];

        return UserDto::fromArray($this->users[$id]);
    }

    public function updateUser(int $id, UserInput $input): UserDto
    {
        if (!isset($this->users[$id])) {
            throw new PhoenixApiException(404, 'not_found', 'User not found');
        }

        $this->users[$id]['first_name'] = $input->firstName;
        $this->users[$id]['last_name'] = $input->lastName;
        $this->users[$id]['birthdate'] = $input->birthdate->format('Y-m-d');
        $this->users[$id]['gender'] = $input->gender;
        $this->users[$id]['updated_at'] = date('c');

        return UserDto::fromArray($this->users[$id]);
    }

    public function deleteUser(int $id): void
    {
        if (!isset($this->users[$id])) {
            throw new PhoenixApiException(404, 'not_found', 'User not found');
        }

        unset($this->users[$id]);
    }

    public function importUsers(): int
    {
        $this->importedCount = 100;

        return $this->importedCount;
    }

    public function reset(): void
    {
        $this->users = [];
        $this->nextId = 1;
        $this->importedCount = 0;
    }

    public function getUsersCount(): int
    {
        return count($this->users);
    }
}
