<?php

declare(strict_types=1);

namespace App\Users;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;

use function is_array;

final readonly class PhoenixValidationErrorApplier
{
    public function apply(FormInterface $form, array $details): void
    {
        foreach ($details as $field => $messages) {
            $field = (string) $field;

            $target = match ($field) {
                'first_name' => 'firstName',
                'last_name' => 'lastName',
                default => $field,
            };

            if (!is_array($messages)) {
                continue;
            }

            foreach ($messages as $message) {
                $message = (string) $message;

                if ($form->has($target)) {
                    $form->get($target)->addError(new FormError($message));
                } else {
                    $form->addError(new FormError($message));
                }
            }
        }
    }
}
