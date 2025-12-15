<?php

declare(strict_types=1);

namespace App\PhoenixApi\Dto;

use App\PhoenixApi\Exception\InvalidPayloadException;

final readonly class UserDto
{
    public function __construct(
        public int $id,
        public string $firstName,
        public string $lastName,
        public string $birthdate,
        public string $gender,
        public string $insertedAt,
        public string $updatedAt,
    ) {
    }

    public static function fromArray(array $data): self
    {
        $path = 'data';

        foreach (['id', 'first_name', 'last_name', 'birthdate', 'gender', 'inserted_at', 'updated_at'] as $key) {
            if (!array_key_exists($key, $data)) {
                throw InvalidPayloadException::missingKey($path, $key);
            }
        }

        return new self(
            id: (int) $data['id'],
            firstName: (string) $data['first_name'],
            lastName: (string) $data['last_name'],
            birthdate: (string) $data['birthdate'],
            gender: (string) $data['gender'],
            insertedAt: (string) $data['inserted_at'],
            updatedAt: (string) $data['updated_at'],
        );
    }
}

