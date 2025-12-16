<?php

declare(strict_types=1);

namespace App\Form\Model;

use DateTimeImmutable;

final class UsersFilterData
{
    public const array AVAILABLE_PAGE_SIZES = [
        '50' => 50,
        '100' => 100,
    ];

    public ?string $firstName = null;
    public ?string $lastName = null;
    public ?string $gender = null;
    public ?DateTimeImmutable $birthdateFrom = null;
    public ?DateTimeImmutable $birthdateTo = null;
    public ?int $pageSize = null;
}
