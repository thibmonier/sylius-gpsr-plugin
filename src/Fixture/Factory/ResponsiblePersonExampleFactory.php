<?php

declare(strict_types=1);

namespace Tmonier\SyliusGpsrPlugin\Fixture\Factory;

use Faker\Factory;
use Faker\Generator;
use Sylius\Bundle\CoreBundle\Fixture\Factory\AbstractExampleFactory;
use Sylius\Component\Core\Formatter\StringInflector;
use Sylius\Resource\Factory\FactoryInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tmonier\SyliusGpsrPlugin\Entity\ResponsiblePersonInterface;
use Tmonier\SyliusGpsrPlugin\Model\EuMemberStates;

final class ResponsiblePersonExampleFactory extends AbstractExampleFactory
{
    private Generator $faker;

    private OptionsResolver $optionsResolver;

    /** @param FactoryInterface<ResponsiblePersonInterface> $responsiblePersonFactory */
    public function __construct(private readonly FactoryInterface $responsiblePersonFactory)
    {
        $this->faker = Factory::create();
        $this->optionsResolver = new OptionsResolver();

        $this->configureOptions($this->optionsResolver);
    }

    /** @param array<string, mixed> $options */
    public function create(array $options = []): ResponsiblePersonInterface
    {
        /** @var array{code: string, name: string, role: string, street: string, postcode: string, city: string, country_code: string, email: string, phone: string|null} $options */
        $options = $this->optionsResolver->resolve($options);

        $responsiblePerson = $this->responsiblePersonFactory->createNew();
        $responsiblePerson->setCode($options['code']);
        $responsiblePerson->setName($options['name']);
        $responsiblePerson->setRole($options['role']);
        $responsiblePerson->setStreet($options['street']);
        $responsiblePerson->setPostcode($options['postcode']);
        $responsiblePerson->setCity($options['city']);
        $responsiblePerson->setCountryCode($options['country_code']);
        $responsiblePerson->setEmail($options['email']);
        $responsiblePerson->setPhone($options['phone']);

        return $responsiblePerson;
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('name', fn (): string => $this->faker->company())
            ->setDefault('code', static fn (Options $options): string => StringInflector::nameToCode(is_string($options['name']) ? $options['name'] : ''))
            ->setDefault('role', ResponsiblePersonInterface::ROLE_IMPORTER)
            ->setAllowedValues('role', ResponsiblePersonInterface::ROLES)
            ->setDefault('street', fn (): string => $this->faker->streetAddress())
            ->setDefault('postcode', fn (): string => $this->faker->postcode())
            ->setDefault('city', fn (): string => $this->faker->city())
            ->setDefault('country_code', static fn (): string => EuMemberStates::CODES[array_rand(EuMemberStates::CODES)])
            ->setDefault('email', fn (): string => $this->faker->companyEmail())
            ->setDefault('phone', null)
        ;
    }
}
