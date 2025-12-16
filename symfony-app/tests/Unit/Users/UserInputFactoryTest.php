<?php

declare(strict_types=1);

namespace App\Tests\Unit\Users;

use App\Form\Model\UserFormData;
use App\Users\UserInputFactory;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class UserInputFactoryTest extends TestCase
{
    public function testFromValidatedFormDataCreatesUserInput(): void
    {
        $formData = new UserFormData();
        $formData->firstName = 'Jan';
        $formData->lastName = 'Kowalski';
        $formData->birthdate = new DateTimeImmutable('1990-05-15');
        $formData->gender = 'male';

        $factory = new UserInputFactory();
        $input = $factory->fromValidatedFormData($formData);

        $this->assertSame('Jan', $input->firstName);
        $this->assertSame('Kowalski', $input->lastName);
        $this->assertSame('1990-05-15', $input->birthdate->format('Y-m-d'));
        $this->assertSame('male', $input->gender);
    }

    public function testFromValidatedFormDataHandlesAllFields(): void
    {
        $formData = new UserFormData();
        $formData->firstName = 'Anna';
        $formData->lastName = 'Nowak';
        $formData->birthdate = new DateTimeImmutable('2000-12-31');
        $formData->gender = 'female';

        $factory = new UserInputFactory();
        $input = $factory->fromValidatedFormData($formData);

        $array = $input->toArray();

        $this->assertSame('Anna', $array['first_name']);
        $this->assertSame('Nowak', $array['last_name']);
        $this->assertSame('2000-12-31', $array['birthdate']);
        $this->assertSame('female', $array['gender']);
    }
}
