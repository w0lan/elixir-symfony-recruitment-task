<?php

declare(strict_types=1);

namespace App\Tests\Unit\Users;

use App\Users\PhoenixValidationErrorApplier;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Forms;

final class PhoenixValidationErrorApplierTest extends TestCase
{
    private PhoenixValidationErrorApplier $applier;

    protected function setUp(): void
    {
        $this->applier = new PhoenixValidationErrorApplier();
    }

    public function testApplyMapsFieldNamesToFormFields(): void
    {
        $formFactory = Forms::createFormFactory();
        $form = $formFactory->createBuilder()
            ->add('firstName', TextType::class)
            ->add('lastName', TextType::class)
            ->getForm();

        $details = [
            'first_name' => ['First name is required'],
            'last_name' => ['Last name is required'],
        ];

        $this->applier->apply($form, $details);

        $this->assertCount(1, $form->get('firstName')->getErrors());
        $this->assertCount(1, $form->get('lastName')->getErrors());
    }

    public function testApplyAddsErrorsToFormFields(): void
    {
        $formFactory = Forms::createFormFactory();
        $form = $formFactory->createBuilder()
            ->add('firstName', TextType::class)
            ->getForm();

        $details = [
            'first_name' => ['Error 1', 'Error 2'],
        ];

        $this->applier->apply($form, $details);

        $errors = $form->get('firstName')->getErrors();
        $this->assertCount(2, $errors);
        $this->assertSame('Error 1', $errors[0]->getMessage());
        $this->assertSame('Error 2', $errors[1]->getMessage());
    }

    public function testApplyHandlesNonArrayMessages(): void
    {
        $formFactory = Forms::createFormFactory();
        $form = $formFactory->createBuilder()
            ->add('firstName', TextType::class)
            ->getForm();

        $details = [
            'first_name' => 'not an array',
        ];

        $this->applier->apply($form, $details);

        $this->assertCount(0, $form->get('firstName')->getErrors());
    }

    public function testApplyAddsErrorToFormRootWhenFieldMissing(): void
    {
        $formFactory = Forms::createFormFactory();
        $form = $formFactory->createBuilder()
            ->add('firstName', TextType::class)
            ->getForm();

        $details = [
            'unknown_field' => ['Unknown field error'],
        ];

        $this->applier->apply($form, $details);

        $this->assertCount(1, $form->getErrors());
        $this->assertSame('Unknown field error', $form->getErrors()[0]->getMessage());
    }
}
