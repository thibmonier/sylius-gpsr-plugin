<?php

declare(strict_types=1);

namespace Tmonier\SyliusGpsrPlugin\Entity;

use Sylius\Component\Product\Model\ProductInterface;
use Sylius\Resource\Model\TranslatableTrait;
use Sylius\Resource\Model\TranslationInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ProductSafety implements ProductSafetyInterface
{
    use TranslatableTrait {
        __construct as private initializeTranslationsCollection;
        getTranslation as private doGetTranslation;
    }

    /** @var mixed */
    protected $id;

    protected ?ProductInterface $product = null;

    protected ?ManufacturerInterface $manufacturer = null;

    protected ?ResponsiblePersonInterface $responsiblePerson = null;

    #[Assert\Length(max: 255, groups: ['sylius'])]
    protected ?string $typeDesignation = null;

    #[Assert\Length(max: 255, groups: ['sylius'])]
    protected ?string $otherIdentifier = null;

    public function __construct()
    {
        $this->initializeTranslationsCollection();
    }

    public function getId(): mixed
    {
        return $this->id;
    }

    public function getProduct(): ?ProductInterface
    {
        return $this->product;
    }

    public function setProduct(?ProductInterface $product): void
    {
        $this->product = $product;
    }

    public function getManufacturer(): ?ManufacturerInterface
    {
        return $this->manufacturer;
    }

    public function setManufacturer(?ManufacturerInterface $manufacturer): void
    {
        $this->manufacturer = $manufacturer;
    }

    public function getResponsiblePerson(): ?ResponsiblePersonInterface
    {
        return $this->responsiblePerson;
    }

    public function setResponsiblePerson(?ResponsiblePersonInterface $responsiblePerson): void
    {
        $this->responsiblePerson = $responsiblePerson;
    }

    public function getTypeDesignation(): ?string
    {
        return $this->typeDesignation;
    }

    public function setTypeDesignation(?string $typeDesignation): void
    {
        $this->typeDesignation = $this->normalize($typeDesignation);
    }

    public function getOtherIdentifier(): ?string
    {
        return $this->otherIdentifier;
    }

    public function setOtherIdentifier(?string $otherIdentifier): void
    {
        $this->otherIdentifier = $this->normalize($otherIdentifier);
    }

    public function getWarnings(): ?string
    {
        return $this->getTranslation()->getWarnings();
    }

    public function setWarnings(?string $warnings): void
    {
        $this->getTranslation()->setWarnings($warnings);
    }

    public function getSafetyInformation(): ?string
    {
        return $this->getTranslation()->getSafetyInformation();
    }

    public function setSafetyInformation(?string $safetyInformation): void
    {
        $this->getTranslation()->setSafetyInformation($safetyInformation);
    }

    /** @return ProductSafetyTranslationInterface */
    public function getTranslation(?string $locale = null): TranslationInterface
    {
        /** @var ProductSafetyTranslationInterface $translation */
        $translation = $this->doGetTranslation($locale);

        return $translation;
    }

    public function isEmpty(): bool
    {
        if (
            $this->manufacturer instanceof \Tmonier\SyliusGpsrPlugin\Entity\ManufacturerInterface ||
            $this->responsiblePerson instanceof \Tmonier\SyliusGpsrPlugin\Entity\ResponsiblePersonInterface ||
            null !== $this->typeDesignation ||
            null !== $this->otherIdentifier
        ) {
            return false;
        }

        foreach ($this->translations as $translation) {
            if ($translation instanceof ProductSafetyTranslationInterface && !$translation->isEmpty()) {
                return false;
            }
        }

        return true;
    }

    protected function createTranslation(): ProductSafetyTranslationInterface
    {
        return new ProductSafetyTranslation();
    }

    private function normalize(?string $value): ?string
    {
        $value = null === $value ? null : trim($value);

        return '' === $value ? null : $value;
    }
}
