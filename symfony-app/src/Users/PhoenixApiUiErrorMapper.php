<?php

declare(strict_types=1);

namespace App\Users;

use App\PhoenixApi\Exception\PhoenixApiException;
use Symfony\Component\HttpFoundation\Response;

final readonly class PhoenixApiUiErrorMapper
{
    public function isTransportError(PhoenixApiException $exception): bool
    {
        return PhoenixApiException::CODE_TRANSPORT_ERROR === $exception->apiCode();
    }

    public function isNotFound(PhoenixApiException $exception): bool
    {
        return Response::HTTP_NOT_FOUND === $exception->statusCode() && PhoenixApiException::CODE_NOT_FOUND === $exception->apiCode();
    }

    public function isValidationError(PhoenixApiException $exception): bool
    {
        return Response::HTTP_UNPROCESSABLE_ENTITY === $exception->statusCode() && PhoenixApiException::CODE_VALIDATION_ERROR === $exception->apiCode();
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
