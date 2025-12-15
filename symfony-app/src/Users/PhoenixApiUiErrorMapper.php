<?php

declare(strict_types=1);

namespace App\Users;

use App\PhoenixApi\Exception\PhoenixApiException;
use Symfony\Component\HttpFoundation\Response;

final readonly class PhoenixApiUiErrorMapper
{
    public function isTransportError(PhoenixApiException $exception): bool
    {
        return $exception->apiCode() === PhoenixApiException::CODE_TRANSPORT_ERROR;
    }

    public function isNotFound(PhoenixApiException $exception): bool
    {
        return $exception->statusCode() === Response::HTTP_NOT_FOUND && $exception->apiCode() === PhoenixApiException::CODE_NOT_FOUND;
    }

    public function isValidationError(PhoenixApiException $exception): bool
    {
        return $exception->statusCode() === Response::HTTP_UNPROCESSABLE_ENTITY && $exception->apiCode() === PhoenixApiException::CODE_VALIDATION_ERROR;
    }

    public function responseStatus(PhoenixApiException $exception): int
    {
        return $this->isTransportError($exception) ? Response::HTTP_SERVICE_UNAVAILABLE : Response::HTTP_OK;
    }

    public function flashMessage(PhoenixApiException $exception): string
    {
        return sprintf('%s (%s)', $exception->apiMessage(), $exception->apiCode());
    }
}
