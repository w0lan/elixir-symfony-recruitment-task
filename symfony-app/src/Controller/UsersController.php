<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\Model\UserFormData;
use App\Form\Model\UsersFilterData;
use App\Form\UserType;
use App\Form\UsersFilterType;
use App\PhoenixApi\Exception\PhoenixApiException;
use App\PhoenixApi\PhoenixApiClient;
use App\Users\PhoenixApiUiErrorMapper;
use App\Users\PhoenixValidationErrorApplier;
use App\Users\UserInputFactory;
use App\Users\UsersIndexRedirectParamsExtractor;
use App\Users\UsersListQueryFactory;
use App\Users\UsersSortConfig;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/users')]
final class UsersController extends AbstractController
{
    private function renderUsersIndexFallback(array $uiQuery, int $statusCode): Response
    {
        $filterData = new UsersFilterData();
        $filterData->firstName = isset($uiQuery['first_name']) ? (string) $uiQuery['first_name'] : null;
        $filterData->lastName = isset($uiQuery['last_name']) ? (string) $uiQuery['last_name'] : null;
        $filterData->gender = isset($uiQuery['gender']) ? (string) $uiQuery['gender'] : null;
        $filterData->pageSize = isset($uiQuery['page_size']) ? (int) $uiQuery['page_size'] : null;

        if (isset($uiQuery['birthdate_from'])) {
            $birthdateFrom = \DateTimeImmutable::createFromFormat('Y-m-d', (string) $uiQuery['birthdate_from']);
            $filterData->birthdateFrom = $birthdateFrom ?: null;
        }

        if (isset($uiQuery['birthdate_to'])) {
            $birthdateTo = \DateTimeImmutable::createFromFormat('Y-m-d', (string) $uiQuery['birthdate_to']);
            $filterData->birthdateTo = $birthdateTo ?: null;
        }

        $sortBy = isset($uiQuery['sort_by']) ? (string) $uiQuery['sort_by'] : 'id';
        $sortDir = isset($uiQuery['sort_dir']) ? (string) $uiQuery['sort_dir'] : UsersSortConfig::SORT_DIR_ASC;

        $filterForm = $this->createForm(UsersFilterType::class, $filterData);

        return $this->render('users/index.html.twig', [
            'filterForm' => $filterForm->createView(),
            'users' => [],
            'meta' => null,
            'query' => $uiQuery,
            'sortBy' => $sortBy,
            'sortDir' => $sortDir,
            'sortColumns' => UsersSortConfig::TABLE_COLUMNS,
        ], new Response(status: $statusCode));
    }

    #[Route('', name: 'users_index', methods: ['GET'])]
    public function index(Request $request, PhoenixApiClient $client, UsersListQueryFactory $queryFactory, PhoenixApiUiErrorMapper $errorMapper): Response
    {
        $filterData = new UsersFilterData();
        $filterForm = $this->createForm(UsersFilterType::class, $filterData);
        $filterForm->handleRequest($request);

        $ctx = $queryFactory->fromRequest($request, $filterData);

        try {
            $result = $client->listUsers($ctx->query);
        } catch (PhoenixApiException $e) {
            $this->addFlash('error', $errorMapper->flashMessage($e));
            $statusCode = $errorMapper->responseStatus($e);

            return $this->render('users/index.html.twig', [
                'filterForm' => $filterForm->createView(),
                'users' => [],
                'meta' => null,
                'query' => $ctx->uiQuery,
                'sortBy' => $ctx->sortBy,
                'sortDir' => $ctx->sortDir,
                'sortColumns' => UsersSortConfig::TABLE_COLUMNS,
            ], new Response(status: $statusCode));
        }

        return $this->render('users/index.html.twig', [
            'filterForm' => $filterForm->createView(),
            'users' => $result->users,
            'meta' => $result->meta,
            'query' => $ctx->uiQuery,
            'sortBy' => $ctx->sortBy,
            'sortDir' => $ctx->sortDir,
            'sortColumns' => UsersSortConfig::TABLE_COLUMNS,
        ]);
    }

    #[Route('/new', name: 'users_new', methods: ['GET', 'POST'])]
    public function new(Request $request, PhoenixApiClient $client, UserInputFactory $inputFactory, PhoenixValidationErrorApplier $validationErrorApplier, PhoenixApiUiErrorMapper $errorMapper): Response
    {
        $data = new UserFormData();
        $form = $this->createForm(UserType::class, $data);
        $form->handleRequest($request);

        $statusCode = Response::HTTP_OK;

        if ($form->isSubmitted() && $form->isValid()) {
            $input = $inputFactory->fromValidatedFormData($data);

            try {
                $client->createUser($input);
                $this->addFlash('success', 'User created');

                return $this->redirectToRoute('users_index');
            } catch (PhoenixApiException $e) {
                if ($errorMapper->isValidationError($e)) {
                    $validationErrorApplier->apply($form, $e->apiDetails());
                } else {
                    $this->addFlash('error', $errorMapper->flashMessage($e));
                    $statusCode = $errorMapper->responseStatus($e);
                }
            }
        }

        return $this->render('users/new.html.twig', [
            'form' => $form->createView(),
        ], new Response(status: $statusCode));
    }

    #[Route('/{id<\\d+>}/edit', name: 'users_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request, PhoenixApiClient $client, UserInputFactory $inputFactory, PhoenixValidationErrorApplier $validationErrorApplier, UsersIndexRedirectParamsExtractor $redirectParamsExtractor, PhoenixApiUiErrorMapper $errorMapper): Response
    {
        try {
            $user = $client->getUser($id);
        } catch (PhoenixApiException $e) {
            if ($errorMapper->isNotFound($e)) {
                throw $this->createNotFoundException();
            }

            $this->addFlash('error', $errorMapper->flashMessage($e));

            if ($errorMapper->isTransportError($e)) {
                return $this->renderUsersIndexFallback($redirectParamsExtractor->fromRequest($request), Response::HTTP_SERVICE_UNAVAILABLE);
            }

            return $this->redirectToRoute('users_index');
        }

        $data = new UserFormData();
        $data->firstName = $user->firstName;
        $data->lastName = $user->lastName;
        $data->gender = $user->gender;
        $data->birthdate = \DateTimeImmutable::createFromFormat('Y-m-d', $user->birthdate) ?: null;

        $form = $this->createForm(UserType::class, $data);
        $form->handleRequest($request);

        $statusCode = Response::HTTP_OK;

        if ($form->isSubmitted() && $form->isValid()) {
            $input = $inputFactory->fromValidatedFormData($data);

            try {
                $client->updateUser($id, $input);
                $this->addFlash('success', 'User updated');

                return $this->redirectToRoute('users_index');
            } catch (PhoenixApiException $e) {
                if ($errorMapper->isValidationError($e)) {
                    $validationErrorApplier->apply($form, $e->apiDetails());
                } elseif ($errorMapper->isNotFound($e)) {
                    throw $this->createNotFoundException();
                } else {
                    $this->addFlash('error', $errorMapper->flashMessage($e));
                    $statusCode = $errorMapper->responseStatus($e);
                }
            }
        }

        return $this->render('users/edit.html.twig', [
            'id' => $id,
            'form' => $form->createView(),
        ], new Response(status: $statusCode));
    }

    #[Route('/{id<\\d+>}/delete', name: 'users_delete', methods: ['POST'])]
    public function delete(int $id, Request $request, PhoenixApiClient $client, UsersIndexRedirectParamsExtractor $redirectParamsExtractor, PhoenixApiUiErrorMapper $errorMapper): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('delete_user_'.$id, (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        try {
            $client->deleteUser($id);
            $this->addFlash('success', 'User deleted');
        } catch (PhoenixApiException $e) {
            if ($errorMapper->isNotFound($e)) {
                $this->addFlash('error', 'User not found');
            } else {
                $this->addFlash('error', $errorMapper->flashMessage($e));
            }

            if ($errorMapper->isTransportError($e)) {
                return $this->renderUsersIndexFallback($redirectParamsExtractor->fromRequest($request), Response::HTTP_SERVICE_UNAVAILABLE);
            }
        }

        return $this->redirectToRoute('users_index', $redirectParamsExtractor->fromRequest($request));
    }

    #[Route('/import', name: 'users_import', methods: ['POST'])]
    public function import(Request $request, PhoenixApiClient $client, UsersIndexRedirectParamsExtractor $redirectParamsExtractor, PhoenixApiUiErrorMapper $errorMapper): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('import_users', (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        try {
            $inserted = $client->importUsers();
            $this->addFlash('success', sprintf('Imported: %d', $inserted));
        } catch (PhoenixApiException $e) {
            $this->addFlash('error', $errorMapper->flashMessage($e));

            if ($errorMapper->isTransportError($e)) {
                return $this->renderUsersIndexFallback($redirectParamsExtractor->fromRequest($request), Response::HTTP_SERVICE_UNAVAILABLE);
            }
        }

        return $this->redirectToRoute('users_index', $redirectParamsExtractor->fromRequest($request));
    }
}

