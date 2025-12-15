<?php

declare(strict_types=1);

namespace App\PhoenixApi\Dto;

final readonly class UserInput
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public \DateTimeImmutable $birthdate,
        public string $gender,
    ) {
    }

    public function toArray(): array
    {
        return [
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'birthdate' => $this->birthdate->format('Y-m-d'),
            'gender' => $this->gender,
        ];
    }
}

