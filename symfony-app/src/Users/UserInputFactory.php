<?php

declare(strict_types=1);

namespace App\Users;

use App\Form\Model\UserFormData;
use App\PhoenixApi\Dto\UserInput;

final readonly class UserInputFactory
{
    public function fromValidatedFormData(UserFormData $data): UserInput
    {
        return new UserInput(
            firstName: $data->firstName,
            lastName: $data->lastName,
            birthdate: $data->birthdate,
            gender: $data->gender,
        );
    }
}
