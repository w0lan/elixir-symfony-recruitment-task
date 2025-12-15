<?php

declare(strict_types=1);

namespace App\PhoenixApi\Dto;

use DateTimeImmutable;

final readonly class UsersListQuery
{
    public function __construct(
        public ?string            $firstName,
        public ?string            $lastName,
        public ?string            $gender,
        public ?DateTimeImmutable $birthdateFrom,
        public ?DateTimeImmutable $birthdateTo,
        public string             $sortBy,
        public string             $sortDir,
        public int                $page,
        public int                $pageSize,
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

        if ($this->firstName !== null && $this->firstName !== '') {
            $query['first_name'] = $this->firstName;
        }

        if ($this->lastName !== null && $this->lastName !== '') {
            $query['last_name'] = $this->lastName;
        }

        if ($this->gender !== null && $this->gender !== '') {
            $query['gender'] = $this->gender;
        }

        if ($this->birthdateFrom !== null) {
            $query['birthdate_from'] = $this->birthdateFrom->format('Y-m-d');
        }

        if ($this->birthdateTo !== null) {
            $query['birthdate_to'] = $this->birthdateTo->format('Y-m-d');
        }

        return $query;
    }
}
