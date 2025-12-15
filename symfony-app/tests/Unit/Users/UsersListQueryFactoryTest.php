<?php

declare(strict_types=1);

namespace App\Tests\Unit\Users;

use App\Form\Model\UsersFilterData;
use App\Users\UsersListQueryFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class UsersListQueryFactoryTest extends TestCase
{
    private UsersListQueryFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new UsersListQueryFactory();
    }

    public function testFromRequestBuildsQueryWithFilters(): void
    {
        $request = new Request(query: [
            'sort_by' => 'first_name',
            'sort_dir' => 'desc',
            'page' => '2',
        ]);

        $filterData = new UsersFilterData();
        $filterData->firstName = 'Jan';
        $filterData->lastName = 'Kowalski';
        $filterData->gender = 'male';

        $context = $this->factory->fromRequest($request, $filterData);

        $this->assertSame('Jan', $context->query->firstName);
        $this->assertSame('Kowalski', $context->query->lastName);
        $this->assertSame('male', $context->query->gender);
        $this->assertSame('first_name', $context->query->sortBy);
        $this->assertSame('desc', $context->query->sortDir);
        $this->assertSame(2, $context->query->page);
    }

    public function testFromRequestAppliesSortDefaults(): void
    {
        $request = new Request();
        $filterData = new UsersFilterData();

        $context = $this->factory->fromRequest($request, $filterData);

        $this->assertSame('id', $context->query->sortBy);
        $this->assertSame('asc', $context->query->sortDir);
    }

    public function testFromRequestValidatesSortBy(): void
    {
        $request = new Request(query: ['sort_by' => 'invalid_field']);
        $filterData = new UsersFilterData();

        $context = $this->factory->fromRequest($request, $filterData);

        $this->assertSame('id', $context->query->sortBy);
    }

    public function testFromRequestValidatesSortDir(): void
    {
        $request = new Request(query: ['sort_dir' => 'invalid_direction']);
        $filterData = new UsersFilterData();

        $context = $this->factory->fromRequest($request, $filterData);

        $this->assertSame('asc', $context->query->sortDir);
    }

    public function testFromRequestClampsPage(): void
    {
        $request = new Request(query: ['page' => '0']);
        $filterData = new UsersFilterData();

        $context = $this->factory->fromRequest($request, $filterData);

        $this->assertSame(1, $context->query->page);
    }

    public function testFromRequestClampsPageSizeMin(): void
    {
        $request = new Request(query: ['page_size' => '0']);
        $filterData = new UsersFilterData();

        $context = $this->factory->fromRequest($request, $filterData);

        $this->assertGreaterThanOrEqual(1, $context->query->pageSize);
    }

    public function testFromRequestClampsPageSizeMax(): void
    {
        $request = new Request(query: ['page_size' => '999']);
        $filterData = new UsersFilterData();

        $context = $this->factory->fromRequest($request, $filterData);

        $this->assertLessThanOrEqual(100, $context->query->pageSize);
    }

    public function testFromRequestReturnsContext(): void
    {
        $request = new Request(query: [
            'first_name' => 'Jan',
            'sort_by' => 'last_name',
            'sort_dir' => 'desc',
        ]);
        $filterData = new UsersFilterData();

        $context = $this->factory->fromRequest($request, $filterData);

        $this->assertSame('last_name', $context->sortBy);
        $this->assertSame('desc', $context->sortDir);
        $this->assertIsArray($context->uiQuery);
        $this->assertSame('Jan', $context->uiQuery['first_name']);
    }

    public function testFromRequestUsesPageSizeFromFilterData(): void
    {
        $request = new Request();
        $filterData = new UsersFilterData();
        $filterData->pageSize = 50;

        $context = $this->factory->fromRequest($request, $filterData);

        $this->assertSame(50, $context->query->pageSize);
    }
}
