<?php

declare(strict_types=1);

namespace Tmonier\SyliusGpsrPlugin\Entity;

use Sylius\Resource\Model\AbstractTranslation;

class ProductSafetyTranslation extends AbstractTranslation implements ProductSafetyTranslationInterface
{
    /** @var mixed */
    protected $id;

    protected ?string $warnings = null;

    protected ?string $safetyInformation = null;

    public function getId(): mixed
    {
        return $this->id;
    }

    public function getWarnings(): ?string
    {
        return $this->warnings;
    }

    public function setWarnings(?string $warnings): void
    {
        $this->warnings = $this->normalize($warnings);
    }

    public function getSafetyInformation(): ?string
    {
        return $this->safetyInformation;
    }

    public function setSafetyInformation(?string $safetyInformation): void
    {
        $this->safetyInformation = $this->normalize($safetyInformation);
    }

    public function isEmpty(): bool
    {
        return null === $this->warnings && null === $this->safetyInformation;
    }

    private function normalize(?string $value): ?string
    {
        $value = null === $value ? null : trim($value);

        return '' === $value ? null : $value;
    }
}
