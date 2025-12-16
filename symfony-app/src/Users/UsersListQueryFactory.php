<?php

declare(strict_types=1);

namespace App\Users;

use App\Form\Model\UsersFilterData;
use App\PhoenixApi\Dto\UsersListQuery;
use Symfony\Component\HttpFoundation\Request;

use function in_array;

final readonly class UsersListQueryFactory
{
    public function fromRequest(Request $request, UsersFilterData $filterData): UsersListQueryContext
    {
        $sortBy = (string) $request->query->get('sort_by', 'id');
        $sortDir = (string) $request->query->get('sort_dir', UsersSortConfig::SORT_DIR_ASC);

        if (!in_array($sortBy, UsersSortConfig::allowedFields(), true)) {
            $sortBy = 'id';
        }

        if (!in_array($sortDir, [UsersSortConfig::SORT_DIR_ASC, UsersSortConfig::SORT_DIR_DESC], true)) {
            $sortDir = UsersSortConfig::SORT_DIR_ASC;
        }

        $page = (int) $request->query->get('page', 1);
        $page = max(1, $page);

        $pageSize = $filterData->pageSize ?? (int) $request->query->get('page_size', 20);
        $pageSize = max(1, min(100, (int) $pageSize));

        $query = new UsersListQuery(
            firstName: $filterData->firstName,
            lastName: $filterData->lastName,
            gender: $filterData->gender,
            birthdateFrom: $filterData->birthdateFrom,
            birthdateTo: $filterData->birthdateTo,
            sortBy: $sortBy,
            sortDir: $sortDir,
            page: $page,
            pageSize: $pageSize,
        );

        return new UsersListQueryContext(
            query: $query,
            sortBy: $sortBy,
            sortDir: $sortDir,
            uiQuery: $request->query->all(),
        );
    }
}
