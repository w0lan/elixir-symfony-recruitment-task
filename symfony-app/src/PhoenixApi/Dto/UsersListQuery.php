<?php

declare(strict_types=1);

namespace App\PhoenixApi\Dto;

use DateTimeImmutable;

final readonly class UsersListQuery
{
    public function __construct(
        public ?string $firstName,
        public ?string $lastName,
        public ?string $gender,
        public ?DateTimeImmutable $birthdateFrom,
        public ?DateTimeImmutable $birthdateTo,
        public string $sortBy,
        public string $sortDir,
        public int $page,
        public int $pageSize,
    ) {
    }

    public function toQuery(): array
    {
        $query = [
            'sort_by' => $this->sortBy,
            'sort_dir' => $this->sortDir,
            'page' => $this->page,
            'page_size' => $this->pageSize,
        ];

        if (null !== $this->firstName && '' !== $this->firstName) {
            $query['first_name'] = $this->firstName;
        }

        if (null !== $this->lastName && '' !== $this->lastName) {
            $query['last_name'] = $this->lastName;
        }

        if (null !== $this->gender && '' !== $this->gender) {
            $query['gender'] = $this->gender;
        }

        if (null !== $this->birthdateFrom) {
            $query['birthdate_from'] = $this->birthdateFrom->format('Y-m-d');
        }

        if (null !== $this->birthdateTo) {
            $query['birthdate_to'] = $this->birthdateTo->format('Y-m-d');
        }

        return $query;
    }
}
