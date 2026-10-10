<?php

declare(strict_types=1);

namespace Tmonier\SyliusGpsrPlugin\Entity;

use Sylius\Component\Product\Model\ProductInterface;
use Sylius\Resource\Model\ResourceInterface;
use Sylius\Resource\Model\TranslatableInterface;
use Sylius\Resource\Model\TranslationInterface;

/**
 * GPSR information of one product (Art. 19 GPSR). Owning side of a one-to-one relation with the
 * product so that applications do not have to change their Product entity.
 */
interface ProductSafetyInterface extends ResourceInterface, TranslatableInterface
{
    public function getProduct(): ?ProductInterface;

    public function setProduct(?ProductInterface $product): void;

    public function getManufacturer(): ?ManufacturerInterface;

    public function setManufacturer(?ManufacturerInterface $manufacturer): void;

    /** Overrides the default responsible person of the manufacturer. */
    public function getResponsiblePerson(): ?ResponsiblePersonInterface;

    public function setResponsiblePerson(?ResponsiblePersonInterface $responsiblePerson): void;

    /** Type, batch-independent model or reference allowing to identify the product (Art. 19(c)). */
    public function getTypeDesignation(): ?string;

    public function setTypeDesignation(?string $typeDesignation): void;

    /** Any other product identifier, e.g. GTIN/EAN (Art. 19(c)). */
    public function getOtherIdentifier(): ?string;

    public function setOtherIdentifier(?string $otherIdentifier): void;

    public function getWarnings(): ?string;

    public function setWarnings(?string $warnings): void;

    public function getSafetyInformation(): ?string;

    public function setSafetyInformation(?string $safetyInformation): void;

    /** @return ProductSafetyTranslationInterface */
    public function getTranslation(?string $locale = null): TranslationInterface;

    /** True when no GPSR information at all has been filled in. */
    public function isEmpty(): bool;
}
