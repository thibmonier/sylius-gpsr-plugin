<?php

declare(strict_types=1);

namespace Tests\Tmonier\SyliusGpsrPlugin\Unit\Entity;

use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintValidatorFactory;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;
use Tmonier\SyliusGpsrPlugin\Entity\ResponsiblePerson;
use Tmonier\SyliusGpsrPlugin\Entity\ResponsiblePersonInterface;

final class ResponsiblePersonTest extends TestCase
{
    public function testAValidResponsiblePersonHasNoViolation(): void
    {
        self::assertCount(0, $this->validate($this->createResponsiblePerson('DE')));
    }

    public function testItMustBeEstablishedInTheEu(): void
    {
        $violations = $this->validate($this->createResponsiblePerson('GB'));

        self::assertCount(1, $violations);
        self::assertSame('countryCode', $violations->get(0)->getPropertyPath());
        self::assertSame('tmonier_gpsr.responsible_person.established_in_eu', $violations->get(0)->getMessageTemplate());
    }

    public function testTheRoleMustBeKnown(): void
    {
        $person = $this->createResponsiblePerson('DE');
        $person->setRole('distributor');

        $violations = $this->validate($person);

        self::assertCount(1, $violations);
        self::assertSame('role', $violations->get(0)->getPropertyPath());
    }

    public function testTheEmailIsRequired(): void
    {
        $person = $this->createResponsiblePerson('DE');
        $person->setEmail(null);

        self::assertSame('email', $this->validate($person)->get(0)->getPropertyPath());
    }

    private function createResponsiblePerson(string $countryCode): ResponsiblePerson
    {
        $person = new ResponsiblePerson();
        $person->setCode('eu_importer');
        $person->setName('EU Import GmbH');
        $person->setRole(ResponsiblePersonInterface::ROLE_IMPORTER);
        $person->setStreet('Hauptstraße 1');
        $person->setPostcode('10115');
        $person->setCity('Berlin');
        $person->setCountryCode($countryCode);
        $person->setEmail('safety@example.com');

        return $person;
    }

    private function validate(ResponsiblePerson $person): ConstraintViolationListInterface
    {
        $validatorFactory = new class() extends ConstraintValidatorFactory {
            public function getInstance(Constraint $constraint): ConstraintValidatorInterface
            {
                // UniqueEntity needs Doctrine and is covered by the functional tests.
                if ($constraint instanceof UniqueEntity) {
                    return new class() extends ConstraintValidator {
                        public function validate(mixed $value, Constraint $constraint): void
                        {
                        }
                    };
                }

                return parent::getInstance($constraint);
            }
        };

        return Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->setConstraintValidatorFactory($validatorFactory)
            ->getValidator()
            ->validate($person, null, ['sylius'])
        ;
    }
}
