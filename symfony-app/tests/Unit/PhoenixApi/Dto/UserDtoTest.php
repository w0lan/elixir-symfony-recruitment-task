<?php

declare(strict_types=1);

namespace App\Tests\Unit\PhoenixApi\Dto;

use App\PhoenixApi\Dto\UserDto;
use App\PhoenixApi\Exception\InvalidPayloadException;
use PHPUnit\Framework\TestCase;

final class UserDtoTest extends TestCase
{
    public function testFromArrayCreatesValidDto(): void
    {
        $data = [
            'id' => 123,
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'birthdate' => '1990-05-15',
            'gender' => 'male',
            'inserted_at' => '2025-12-15T10:00:00Z',
            'updated_at' => '2025-12-15T12:00:00Z',
        ];

        $dto = UserDto::fromArray($data);

        $this->assertSame(123, $dto->id);
        $this->assertSame('Jan', $dto->firstName);
        $this->assertSame('Kowalski', $dto->lastName);
        $this->assertSame('1990-05-15', $dto->birthdate);
        $this->assertSame('male', $dto->gender);
        $this->assertSame('2025-12-15T10:00:00Z', $dto->insertedAt);
        $this->assertSame('2025-12-15T12:00:00Z', $dto->updatedAt);
    }

    public function testFromArrayThrowsOnMissingId(): void
    {
        $this->expectException(InvalidPayloadException::class);
        $this->expectExceptionMessage('Missing key "id" at data.');

        UserDto::fromArray([
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'birthdate' => '1990-05-15',
            'gender' => 'male',
            'inserted_at' => '2025-12-15T10:00:00Z',
            'updated_at' => '2025-12-15T12:00:00Z',
        ]);
    }

    public function testFromArrayThrowsOnMissingFirstName(): void
    {
        $this->expectException(InvalidPayloadException::class);
        $this->expectExceptionMessage('Missing key "first_name" at data.');

        UserDto::fromArray([
            'id' => 123,
            'last_name' => 'Kowalski',
            'birthdate' => '1990-05-15',
            'gender' => 'male',
            'inserted_at' => '2025-12-15T10:00:00Z',
            'updated_at' => '2025-12-15T12:00:00Z',
        ]);
    }
}
