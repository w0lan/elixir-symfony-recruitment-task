<?php

declare(strict_types=1);

namespace App\PhoenixApi\Exception;

use RuntimeException;
use Throwable;
use function is_array;

final class PhoenixApiException extends RuntimeException
{
    public const string CODE_TRANSPORT_ERROR = 'transport_error';
    public const string CODE_INVALID_RESPONSE = 'invalid_response';
    public const string CODE_VALIDATION_ERROR = 'validation_error';
    public const string CODE_NOT_FOUND = 'not_found';

    public function __construct(
        private readonly int    $statusCode,
        private readonly string $apiCode,
        private readonly string $apiMessage,
        private readonly array  $apiDetails = [],
        ?Throwable              $previous = null,
    ) {
        parent::__construct($apiMessage, 0, $previous);
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }

    public function apiCode(): string
    {
        return $this->apiCode;
    }

    public function apiMessage(): string
    {
        return $this->apiMessage;
    }

    public function apiDetails(): array
    {
        return $this->apiDetails;
    }

    public static function fromResponse(int $statusCode, mixed $payload): self
    {
        $code = 'unknown_error';
        $message = 'Request failed';
        $details = [];

        if (is_array($payload) && isset($payload['error']) && is_array($payload['error'])) {
            $code = (string) ($payload['error']['code'] ?? $code);
            $message = (string) ($payload['error']['message'] ?? $message);
            $details = is_array($payload['error']['details'] ?? null) ? $payload['error']['details'] : [];
        }

        return new self($statusCode, $code, $message, $details);
    }
}
