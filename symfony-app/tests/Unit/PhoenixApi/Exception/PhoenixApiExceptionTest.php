<?php

declare(strict_types=1);

namespace App\Tests\Unit\PhoenixApi\Exception;

use App\PhoenixApi\Exception\PhoenixApiException;
use PHPUnit\Framework\TestCase;

final class PhoenixApiExceptionTest extends TestCase
{
    public function testFromResponseParsesErrorPayload(): void
    {
        $payload = [
            'error' => [
                'code' => 'validation_error',
                'message' => 'Invalid input',
                'details' => ['email' => ['Email is required']],
            ],
        ];

        $exception = PhoenixApiException::fromResponse(422, $payload);

        $this->assertSame(422, $exception->statusCode());
        $this->assertSame('validation_error', $exception->apiCode());
        $this->assertSame('Invalid input', $exception->apiMessage());
        $this->assertSame(['email' => ['Email is required']], $exception->apiDetails());
    }

    public function testFromResponseHandlesMissingErrorKey(): void
    {
        $payload = ['data' => []];

        $exception = PhoenixApiException::fromResponse(500, $payload);

        $this->assertSame(500, $exception->statusCode());
        $this->assertSame('unknown_error', $exception->apiCode());
        $this->assertSame('Request failed', $exception->apiMessage());
        $this->assertSame([], $exception->apiDetails());
    }

    public function testFromResponseHandlesInvalidPayload(): void
    {
        $exception = PhoenixApiException::fromResponse(400, null);

        $this->assertSame(400, $exception->statusCode());
        $this->assertSame('unknown_error', $exception->apiCode());
        $this->assertSame('Request failed', $exception->apiMessage());
        $this->assertSame([], $exception->apiDetails());
    }

    public function testFromResponseHandlesMissingCodeAndMessage(): void
    {
        $payload = [
            'error' => [
                'details' => ['field' => ['error']],
            ],
        ];

        $exception = PhoenixApiException::fromResponse(404, $payload);

        $this->assertSame('unknown_error', $exception->apiCode());
        $this->assertSame('Request failed', $exception->apiMessage());
        $this->assertSame(['field' => ['error']], $exception->apiDetails());
    }

    public function testFromResponseHandlesInvalidDetailsType(): void
    {
        $payload = [
            'error' => [
                'code' => 'error',
                'message' => 'Error',
                'details' => 'not an array',
            ],
        ];

        $exception = PhoenixApiException::fromResponse(400, $payload);

        $this->assertSame([], $exception->apiDetails());
    }
}
