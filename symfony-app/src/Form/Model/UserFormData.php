<?php

declare(strict_types=1);

namespace App\Form\Model;

use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;

final class UserFormData
{
    public const string BIRTHDATE_MIN = '1970-01-01';
    public const string BIRTHDATE_MAX = '2024-12-31';

    #[Assert\NotBlank]
    public string $firstName = '';

    #[Assert\NotBlank]
    public string $lastName = '';

    #[Assert\NotNull]
    #[Assert\Range(min: self::BIRTHDATE_MIN, max: self::BIRTHDATE_MAX)]
    public ?DateTimeImmutable $birthdate = null;

    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['male', 'female'])]
    public string $gender = '';
}
