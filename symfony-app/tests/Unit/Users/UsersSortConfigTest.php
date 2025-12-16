<?php

declare(strict_types=1);

namespace App\Tests\Unit\Users;

use App\Users\UsersSortConfig;
use PHPUnit\Framework\TestCase;

final class UsersSortConfigTest extends TestCase
{
    public function testAllowedFieldsReturnsAllFields(): void
    {
        $fields = UsersSortConfig::allowedFields();

        $this->assertIsArray($fields);
        $this->assertNotEmpty($fields);
    }

    public function testAllowedFieldsContainsTableColumns(): void
    {
        $fields = UsersSortConfig::allowedFields();

        $this->assertContains('id', $fields);
        $this->assertContains('first_name', $fields);
        $this->assertContains('last_name', $fields);
        $this->assertContains('birthdate', $fields);
        $this->assertContains('gender', $fields);
    }

    public function testAllowedFieldsContainsExtraFields(): void
    {
        $fields = UsersSortConfig::allowedFields();

        $this->assertContains('inserted_at', $fields);
        $this->assertContains('updated_at', $fields);
    }

    public function testAllowedFieldsReturnsUniqueValues(): void
    {
        $fields = UsersSortConfig::allowedFields();

        $this->assertSame($fields, array_unique($fields));
    }
}
