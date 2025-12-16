<?php

declare(strict_types=1);

namespace App\Users;

use Symfony\Component\HttpFoundation\Request;

final readonly class UsersIndexRedirectParamsExtractor
{
    private const array KEYS = [
        'first_name',
        'last_name',
        'gender',
        'birthdate_from',
        'birthdate_to',
        'sort_by',
        'sort_dir',
        'page',
        'page_size',
    ];

    public function fromRequest(Request $request): array
    {
        $params = [];

        foreach (self::KEYS as $key) {
            $value = $request->request->get($key);

            if (null === $value) {
                $value = $request->query->get($key);
            }

            if (null === $value || '' === $value) {
                continue;
            }

            $params[$key] = $value;
        }

        return $params;
    }
}
