<?php

declare(strict_types=1);

namespace App\Users;

use App\PhoenixApi\Dto\UsersListQuery;

final readonly class UsersListQueryContext
{
    public function __construct(
        public UsersListQuery $query,
        public string $sortBy,
        public string $sortDir,
        public array $uiQuery,
    ) {
    }
}
