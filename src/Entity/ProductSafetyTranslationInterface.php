<?php

declare(strict_types=1);

namespace Tmonier\SyliusGpsrPlugin\Entity;

use Sylius\Resource\Model\ResourceInterface;
use Sylius\Resource\Model\TranslationInterface;

/**
 * Warnings and safety information in the language of the consumer (Art. 19(d) GPSR).
 */
interface ProductSafetyTranslationInterface extends ResourceInterface, TranslationInterface
{
    public function getWarnings(): ?string;

    public function setWarnings(?string $warnings): void;

    public function getSafetyInformation(): ?string;

    public function setSafetyInformation(?string $safetyInformation): void;

    public function isEmpty(): bool;
}
