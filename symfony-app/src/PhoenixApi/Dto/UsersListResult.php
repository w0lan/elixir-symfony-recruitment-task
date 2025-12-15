<?php

declare(strict_types=1);

namespace App\PhoenixApi\Dto;

final readonly class UsersListResult
{
    /**
     * @param UserDto[] $users
     */
    public function __construct(
        public array $users,
        public UsersListMeta $meta,
    ) {
    }
}

