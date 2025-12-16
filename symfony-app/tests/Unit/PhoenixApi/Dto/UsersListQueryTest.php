<?php

declare(strict_types=1);

namespace App\Tests\Unit\PhoenixApi\Dto;

use App\PhoenixApi\Dto\UsersListQuery;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class UsersListQueryTest extends TestCase
{
    public function testToQueryIncludesAllParams(): void
    {
        $query = new UsersListQuery(
            firstName: 'Jan',
            lastName: 'Kowalski',
            gender: 'male',
            birthdateFrom: new DateTimeImmutable('1990-01-01'),
            birthdateTo: new DateTimeImmutable('2000-12-31'),
            sortBy: 'first_name',
            sortDir: 'desc',
            page: 3,
            pageSize: 50
        );

        $result = $query->toQuery();

        $this->assertSame([
            'sort_by' => 'first_name',
            'sort_dir' => 'desc',
            'page' => 3,
            'page_size' => 50,
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'gender' => 'male',
            'birthdate_from' => '1990-01-01',
            'birthdate_to' => '2000-12-31',
        ], $result);
    }

    public function testToQueryOmitsNullValues(): void
    {
        $query = new UsersListQuery(
            firstName: null,
            lastName: null,
            gender: null,
            birthdateFrom: null,
            birthdateTo: null,
            sortBy: 'id',
            sortDir: 'asc',
            page: 1,
            pageSize: 20
        );

        $result = $query->toQuery();

        $this->assertSame([
            'sort_by' => 'id',
            'sort_dir' => 'asc',
            'page' => 1,
            'page_size' => 20,
        ], $result);
    }

    public function testToQueryOmitsEmptyStrings(): void
    {
        $query = new UsersListQuery(
            firstName: '',
            lastName: '',
            gender: '',
            birthdateFrom: null,
            birthdateTo: null,
            sortBy: 'id',
            sortDir: 'asc',
            page: 1,
            pageSize: 20
        );

        $result = $query->toQuery();

        $this->assertSame([
            'sort_by' => 'id',
            'sort_dir' => 'asc',
            'page' => 1,
            'page_size' => 20,
        ], $result);
    }

    public function testToQueryFormatsDatesProperly(): void
    {
        $query = new UsersListQuery(
            firstName: null,
            lastName: null,
            gender: null,
            birthdateFrom: new DateTimeImmutable('2000-05-15 14:30:45'),
            birthdateTo: new DateTimeImmutable('2024-12-31 23:59:59'),
            sortBy: 'birthdate',
            sortDir: 'asc',
            page: 1,
            pageSize: 20
        );

        $result = $query->toQuery();

        $this->assertSame('2000-05-15', $result['birthdate_from']);
        $this->assertSame('2024-12-31', $result['birthdate_to']);
    }
}
