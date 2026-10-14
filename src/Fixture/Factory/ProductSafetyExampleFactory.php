<?php

declare(strict_types=1);

namespace Tmonier\SyliusGpsrPlugin\Fixture\Factory;

use Sylius\Bundle\CoreBundle\Fixture\Factory\AbstractExampleFactory;
use Sylius\Component\Product\Model\ProductInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Sylius\Resource\Factory\FactoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tmonier\SyliusGpsrPlugin\Entity\ManufacturerInterface;
use Tmonier\SyliusGpsrPlugin\Entity\ProductSafetyInterface;
use Tmonier\SyliusGpsrPlugin\Entity\ResponsiblePersonInterface;

final class ProductSafetyExampleFactory extends AbstractExampleFactory
{
    private readonly OptionsResolver $optionsResolver;

    /**
     * @param FactoryInterface<ProductSafetyInterface> $productSafetyFactory
     * @param RepositoryInterface<ProductInterface> $productRepository
     * @param RepositoryInterface<ManufacturerInterface> $manufacturerRepository
     * @param RepositoryInterface<ResponsiblePersonInterface> $responsiblePersonRepository
     */
    public function __construct(
        private readonly FactoryInterface $productSafetyFactory,
        private readonly RepositoryInterface $productRepository,
        private readonly RepositoryInterface $manufacturerRepository,
        private readonly RepositoryInterface $responsiblePersonRepository,
    ) {
        $this->optionsResolver = new OptionsResolver();

        $this->configureOptions($this->optionsResolver);
    }

    /** @param array<string, mixed> $options */
    public function create(array $options = []): ProductSafetyInterface
    {
        /** @var array{product: string, manufacturer: string|null, responsible_person: string|null, type_designation: string|null, other_identifier: string|null, translations: array<string, array{warnings?: string|null, safety_information?: string|null}>} $options */
        $options = $this->optionsResolver->resolve($options);

        $product = $this->productRepository->findOneBy(['code' => $options['product']]);
        if (!$product instanceof ProductInterface) {
            throw new \InvalidArgumentException(sprintf('Product "%s" not found.', $options['product']));
        }

        $productSafety = $this->productSafetyFactory->createNew();
        $productSafety->setProduct($product);
        $productSafety->setTypeDesignation($options['type_designation']);
        $productSafety->setOtherIdentifier($options['other_identifier']);

        if (null !== $options['manufacturer']) {
            $manufacturer = $this->manufacturerRepository->findOneBy(['code' => $options['manufacturer']]);
            if (!$manufacturer instanceof ManufacturerInterface) {
                throw new \InvalidArgumentException(sprintf('Manufacturer "%s" not found.', $options['manufacturer']));
            }
            $productSafety->setManufacturer($manufacturer);
        }

        if (null !== $options['responsible_person']) {
            $responsiblePerson = $this->responsiblePersonRepository->findOneBy(['code' => $options['responsible_person']]);
            if (!$responsiblePerson instanceof ResponsiblePersonInterface) {
                throw new \InvalidArgumentException(sprintf('Responsible person "%s" not found.', $options['responsible_person']));
            }
            $productSafety->setResponsiblePerson($responsiblePerson);
        }

        foreach ($options['translations'] as $localeCode => $translation) {
            $productSafety->setCurrentLocale($localeCode);
            $productSafety->setFallbackLocale($localeCode);
            $productSafety->setWarnings($translation['warnings'] ?? null);
            $productSafety->setSafetyInformation($translation['safety_information'] ?? null);
        }

        return $productSafety;
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('product')
            ->setAllowedTypes('product', 'string')
            ->setDefault('manufacturer', null)
            ->setDefault('responsible_person', null)
            ->setDefault('type_designation', null)
            ->setDefault('other_identifier', null)
            ->setDefault('translations', [])
            ->setAllowedTypes('translations', 'array')
        ;
    }
}
