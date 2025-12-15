<?php

declare(strict_types=1);

namespace App\Tests\Unit\PhoenixApi\Exception;

use App\PhoenixApi\Exception\InvalidPayloadException;
use PHPUnit\Framework\TestCase;

final class InvalidPayloadExceptionTest extends TestCase
{
    public function testMissingKeyCreatesException(): void
    {
        $exception = InvalidPayloadException::missingKey('data.user', 'email');

        $this->assertSame('Missing key "email" at data.user.', $exception->getMessage());
    }

    public function testInvalidTypeCreatesException(): void
    {
        $exception = InvalidPayloadException::invalidType('data.meta', 'array');

        $this->assertSame('Invalid payload at data.meta, expected array.', $exception->getMessage());
    }
}
