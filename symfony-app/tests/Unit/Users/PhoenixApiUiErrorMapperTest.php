<?php

declare(strict_types=1);

namespace App\Tests\Unit\Users;

use App\PhoenixApi\Exception\PhoenixApiException;
use App\Users\PhoenixApiUiErrorMapper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

final class PhoenixApiUiErrorMapperTest extends TestCase
{
    private PhoenixApiUiErrorMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new PhoenixApiUiErrorMapper();
    }

    public function testIsTransportErrorDetectsTransportError(): void
    {
        $exception = new PhoenixApiException(0, PhoenixApiException::CODE_TRANSPORT_ERROR, 'Transport error');

        $this->assertTrue($this->mapper->isTransportError($exception));
    }

    public function testIsTransportErrorReturnsFalseForOthers(): void
    {
        $exception = new PhoenixApiException(404, PhoenixApiException::CODE_NOT_FOUND, 'Not found');

        $this->assertFalse($this->mapper->isTransportError($exception));
    }

    public function testIsNotFoundDetects404(): void
    {
        $exception = new PhoenixApiException(404, PhoenixApiException::CODE_NOT_FOUND, 'Not found');

        $this->assertTrue($this->mapper->isNotFound($exception));
    }

    public function testIsNotFoundReturnsFalseForOtherStatus(): void
    {
        $exception = new PhoenixApiException(422, PhoenixApiException::CODE_NOT_FOUND, 'Not found');

        $this->assertFalse($this->mapper->isNotFound($exception));
    }

    public function testIsNotFoundReturnsFalseForOtherCode(): void
    {
        $exception = new PhoenixApiException(404, PhoenixApiException::CODE_VALIDATION_ERROR, 'Validation error');

        $this->assertFalse($this->mapper->isNotFound($exception));
    }

    public function testIsValidationErrorDetects422(): void
    {
        $exception = new PhoenixApiException(422, PhoenixApiException::CODE_VALIDATION_ERROR, 'Validation error');

        $this->assertTrue($this->mapper->isValidationError($exception));
    }

    public function testIsValidationErrorReturnsFalseForOtherStatus(): void
    {
        $exception = new PhoenixApiException(400, PhoenixApiException::CODE_VALIDATION_ERROR, 'Validation error');

        $this->assertFalse($this->mapper->isValidationError($exception));
    }

    public function testIsValidationErrorReturnsFalseForOtherCode(): void
    {
        $exception = new PhoenixApiException(422, PhoenixApiException::CODE_NOT_FOUND, 'Not found');

        $this->assertFalse($this->mapper->isValidationError($exception));
    }

    public function testResponseStatusReturns503ForTransport(): void
    {
        $exception = new PhoenixApiException(0, PhoenixApiException::CODE_TRANSPORT_ERROR, 'Transport error');

        $this->assertSame(Response::HTTP_SERVICE_UNAVAILABLE, $this->mapper->responseStatus($exception));
    }

    public function testResponseStatusReturnsOkForOthers(): void
    {
        $exception = new PhoenixApiException(404, PhoenixApiException::CODE_NOT_FOUND, 'Not found');

        $this->assertSame(Response::HTTP_OK, $this->mapper->responseStatus($exception));
    }

    public function testFlashMessageFormatsCorrectly(): void
    {
        $exception = new PhoenixApiException(422, 'validation_error', 'Invalid input');

        $message = $this->mapper->flashMessage($exception);

        $this->assertSame('Invalid input (validation_error)', $message);
    }
}
