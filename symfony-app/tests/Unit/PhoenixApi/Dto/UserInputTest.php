<?php

declare(strict_types=1);

namespace App\Tests\Unit\PhoenixApi\Dto;

use App\PhoenixApi\Dto\UserInput;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class UserInputTest extends TestCase
{
    public function testToArrayConvertsToApiFormat(): void
    {
        $birthdate = new DateTimeImmutable('1990-05-15');
        $input = new UserInput(
            firstName: 'Jan',
            lastName: 'Kowalski',
            birthdate: $birthdate,
            gender: 'male'
        );

        $array = $input->toArray();

        $this->assertSame([
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'birthdate' => '1990-05-15',
            'gender' => 'male',
        ], $array);
    }

    public function testToArrayFormatsDateProperly(): void
    {
        $birthdate = new DateTimeImmutable('2000-12-31 15:30:45');
        $input = new UserInput(
            firstName: 'Anna',
            lastName: 'Nowak',
            birthdate: $birthdate,
            gender: 'female'
        );

        $array = $input->toArray();

        $this->assertSame('2000-12-31', $array['birthdate']);
    }
}
