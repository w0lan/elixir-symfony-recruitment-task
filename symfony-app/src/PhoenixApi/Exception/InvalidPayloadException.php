<?php

declare(strict_types=1);

namespace App\PhoenixApi\Exception;

use UnexpectedValueException;

final class InvalidPayloadException extends UnexpectedValueException
{
    public static function missingKey(string $path, string $key): self
    {
        return new self(sprintf('Missing key "%s" at %s.', $key, $path));
    }

    public static function invalidType(string $path, string $expected): self
    {
        return new self(sprintf('Invalid payload at %s, expected %s.', $path, $expected));
    }
}
