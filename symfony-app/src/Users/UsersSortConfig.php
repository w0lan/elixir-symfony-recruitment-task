<?php

declare(strict_types=1);

namespace App\Users;

final readonly class UsersSortConfig
{
    public const string SORT_DIR_ASC = 'asc';
    public const string SORT_DIR_DESC = 'desc';

    public const array TABLE_COLUMNS = [
        ['field' => 'id', 'label' => 'ID'],
        ['field' => 'first_name', 'label' => 'First name'],
        ['field' => 'last_name', 'label' => 'Last name'],
        ['field' => 'birthdate', 'label' => 'Birthdate'],
        ['field' => 'gender', 'label' => 'Gender'],
    ];

    public const array EXTRA_SORT_FIELDS = [
        'inserted_at',
        'updated_at',
    ];

    /**
     * @return list<string>
     */
    public static function allowedFields(): array
    {
        $fields = array_column(self::TABLE_COLUMNS, 'field');

        return array_values(array_unique(array_merge($fields, self::EXTRA_SORT_FIELDS)));
    }
}
