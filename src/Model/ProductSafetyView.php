<?php

declare(strict_types=1);

namespace Tmonier\SyliusGpsrPlugin\Model;

use Tmonier\SyliusGpsrPlugin\Entity\ManufacturerInterface;
use Tmonier\SyliusGpsrPlugin\Entity\ResponsiblePersonInterface;

/**
 * Read model of the information an online offer has to display (Art. 19 GPSR).
 */
final readonly class ProductSafetyView
{
    public function __construct(
        public ?ManufacturerInterface $manufacturer,
        public ?ResponsiblePersonInterface $responsiblePerson,
        public bool $responsiblePersonRequired,
        public ?string $typeDesignation,
        public ?string $otherIdentifier,
        public ?string $warnings,
        public ?string $safetyInformation,
    ) {
    }

    /** A manufacturer outside the Union is set but no responsible person could be resolved. */
    public function isResponsiblePersonMissing(): bool
    {
        return $this->responsiblePersonRequired && !$this->responsiblePerson instanceof \Tmonier\SyliusGpsrPlugin\Entity\ResponsiblePersonInterface;
    }

    public function isEmpty(): bool
    {
        return
            !$this->manufacturer instanceof \Tmonier\SyliusGpsrPlugin\Entity\ManufacturerInterface &&
            !$this->responsiblePerson instanceof \Tmonier\SyliusGpsrPlugin\Entity\ResponsiblePersonInterface &&
            null === $this->typeDesignation &&
            null === $this->otherIdentifier &&
            null === $this->warnings &&
            null === $this->safetyInformation
        ;
    }
}
