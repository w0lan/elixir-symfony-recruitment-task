<?php

declare(strict_types=1);

namespace App\PhoenixApi;

use App\PhoenixApi\Dto\UserDto;
use App\PhoenixApi\Dto\UserInput;
use App\PhoenixApi\Dto\UsersListQuery;
use App\PhoenixApi\Dto\UsersListResult;

interface PhoenixApiClientInterface
{
    public function listUsers(UsersListQuery $query): UsersListResult;

    public function getUser(int $id): UserDto;

    public function createUser(UserInput $input): UserDto;

    public function updateUser(int $id, UserInput $input): UserDto;

    public function deleteUser(int $id): void;

    public function importUsers(): int;
}
