<?php

declare(strict_types=1);

namespace App\Tests\Unit\Users;

use App\Users\UsersIndexRedirectParamsExtractor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class UsersIndexRedirectParamsExtractorTest extends TestCase
{
    private UsersIndexRedirectParamsExtractor $extractor;

    protected function setUp(): void
    {
        $this->extractor = new UsersIndexRedirectParamsExtractor();
    }

    public function testFromRequestExtractsQueryParams(): void
    {
        $request = new Request(query: [
            'first_name' => 'Jan',
            'page' => '2',
            'sort_by' => 'last_name',
        ]);

        $params = $this->extractor->fromRequest($request);

        $this->assertSame('Jan', $params['first_name']);
        $this->assertSame('2', $params['page']);
        $this->assertSame('last_name', $params['sort_by']);
        $this->assertCount(3, $params);
    }

    public function testFromRequestExtractsRequestParams(): void
    {
        $request = new Request(request: [
            'first_name' => 'Anna',
            'page' => '3',
        ]);

        $params = $this->extractor->fromRequest($request);

        $this->assertSame([
            'first_name' => 'Anna',
            'page' => '3',
        ], $params);
    }

    public function testFromRequestPrioritizesRequestOverQuery(): void
    {
        $request = new Request(
            query: ['first_name' => 'FromQuery', 'page' => '1'],
            request: ['first_name' => 'FromRequest']
        );

        $params = $this->extractor->fromRequest($request);

        $this->assertSame('FromRequest', $params['first_name']);
        $this->assertSame('1', $params['page']);
    }

    public function testFromRequestOmitsEmptyValues(): void
    {
        $request = new Request(query: [
            'first_name' => '',
            'last_name' => 'Kowalski',
            'page' => null,
            'sort_by' => 'id',
        ]);

        $params = $this->extractor->fromRequest($request);

        $this->assertArrayNotHasKey('first_name', $params);
        $this->assertArrayNotHasKey('page', $params);
        $this->assertArrayHasKey('last_name', $params);
        $this->assertArrayHasKey('sort_by', $params);
    }

    public function testFromRequestHandlesAllDefinedKeys(): void
    {
        $request = new Request(query: [
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'gender' => 'male',
            'birthdate_from' => '1990-01-01',
            'birthdate_to' => '2000-12-31',
            'sort_by' => 'birthdate',
            'sort_dir' => 'desc',
            'page' => '5',
            'page_size' => '50',
        ]);

        $params = $this->extractor->fromRequest($request);

        $this->assertCount(9, $params);
    }
}
