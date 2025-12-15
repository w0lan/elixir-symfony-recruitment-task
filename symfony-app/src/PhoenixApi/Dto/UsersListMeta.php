<?php

declare(strict_types=1);

namespace App\PhoenixApi\Dto;

final readonly class UsersListMeta
{
    public function __construct(
        public int $page,
        public int $pageSize,
        public int $total,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            page: (int) ($data['page'] ?? 1),
            pageSize: (int) ($data['page_size'] ?? 20),
            total: (int) ($data['total'] ?? 0),
        );
    }
}

