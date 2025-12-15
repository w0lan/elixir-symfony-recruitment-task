<?php

declare(strict_types=1);

namespace App\Tests\Unit\PhoenixApi\Dto;

use App\PhoenixApi\Dto\UsersListMeta;
use PHPUnit\Framework\TestCase;

final class UsersListMetaTest extends TestCase
{
    public function testFromArrayCreatesValidMeta(): void
    {
        $data = [
            'page' => 2,
            'page_size' => 50,
            'total' => 150,
        ];

        $meta = UsersListMeta::fromArray($data);

        $this->assertSame(2, $meta->page);
        $this->assertSame(50, $meta->pageSize);
        $this->assertSame(150, $meta->total);
    }

    public function testFromArrayHandlesMissingKeys(): void
    {
        $data = [];

        $meta = UsersListMeta::fromArray($data);

        $this->assertSame(1, $meta->page);
        $this->assertSame(20, $meta->pageSize);
        $this->assertSame(0, $meta->total);
    }

    public function testFromArrayCastsTypes(): void
    {
        $data = [
            'page' => '3',
            'page_size' => '25',
            'total' => '100',
        ];

        $meta = UsersListMeta::fromArray($data);

        $this->assertSame(3, $meta->page);
        $this->assertSame(25, $meta->pageSize);
        $this->assertSame(100, $meta->total);
    }
}
