<?php

declare(strict_types=1);

namespace Tmonier\SyliusGpsrPlugin\Fixture\Factory;

use Faker\Factory;
use Faker\Generator;
use Sylius\Bundle\CoreBundle\Fixture\Factory\AbstractExampleFactory;
use Sylius\Component\Core\Formatter\StringInflector;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Sylius\Resource\Factory\FactoryInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tmonier\SyliusGpsrPlugin\Entity\ManufacturerInterface;
use Tmonier\SyliusGpsrPlugin\Entity\ResponsiblePersonInterface;

final class ManufacturerExampleFactory extends AbstractExampleFactory
{
    private readonly Generator $faker;

    private readonly OptionsResolver $optionsResolver;

    /**
     * @param FactoryInterface<ManufacturerInterface> $manufacturerFactory
     * @param RepositoryInterface<ResponsiblePersonInterface> $responsiblePersonRepository
     */
    public function __construct(
        private readonly FactoryInterface $manufacturerFactory,
        private readonly RepositoryInterface $responsiblePersonRepository,
    ) {
        $this->faker = Factory::create();
        $this->optionsResolver = new OptionsResolver();

        $this->configureOptions($this->optionsResolver);
    }

    /** @param array<string, mixed> $options */
    public function create(array $options = []): ManufacturerInterface
    {
        /** @var array{code: string, name: string, trade_mark: string|null, street: string, postcode: string, city: string, country_code: string, email: string, website: string|null, default_responsible_person: string|null} $options */
        $options = $this->optionsResolver->resolve($options);

        $manufacturer = $this->manufacturerFactory->createNew();
        $manufacturer->setCode($options['code']);
        $manufacturer->setName($options['name']);
        $manufacturer->setTradeMark($options['trade_mark']);
        $manufacturer->setStreet($options['street']);
        $manufacturer->setPostcode($options['postcode']);
        $manufacturer->setCity($options['city']);
        $manufacturer->setCountryCode($options['country_code']);
        $manufacturer->setEmail($options['email']);
        $manufacturer->setWebsite($options['website']);

        if (null !== $options['default_responsible_person']) {
            $responsiblePerson = $this->responsiblePersonRepository->findOneBy(['code' => $options['default_responsible_person']]);
            if (!$responsiblePerson instanceof ResponsiblePersonInterface) {
                throw new \InvalidArgumentException(sprintf('Responsible person "%s" not found.', $options['default_responsible_person']));
            }
            $manufacturer->setDefaultResponsiblePerson($responsiblePerson);
        }

        return $manufacturer;
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('name', fn (): string => $this->faker->company())
            ->setDefault('code', static fn (Options $options): string => StringInflector::nameToCode(is_string($options['name']) ? $options['name'] : ''))
            ->setDefault('trade_mark', null)
            ->setDefault('street', fn (): string => $this->faker->streetAddress())
            ->setDefault('postcode', fn (): string => $this->faker->postcode())
            ->setDefault('city', fn (): string => $this->faker->city())
            ->setDefault('country_code', 'FR')
            ->setDefault('email', fn (): string => $this->faker->companyEmail())
            ->setDefault('website', null)
            ->setDefault('default_responsible_person', null)
        ;
    }
}
