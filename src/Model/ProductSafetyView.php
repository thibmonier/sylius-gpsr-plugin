<?php

declare(strict_types=1);

namespace Tmonier\SyliusGpsrPlugin\Model;

use Tmonier\SyliusGpsrPlugin\Entity\ManufacturerInterface;
use Tmonier\SyliusGpsrPlugin\Entity\ResponsiblePersonInterface;

/**
 * Read model of the information an online offer has to display (Art. 19 GPSR).
 */
final class ProductSafetyView
{
    public function __construct(
        public readonly ?ManufacturerInterface $manufacturer,
        public readonly ?ResponsiblePersonInterface $responsiblePerson,
        public readonly bool $responsiblePersonRequired,
        public readonly ?string $typeDesignation,
        public readonly ?string $otherIdentifier,
        public readonly ?string $warnings,
        public readonly ?string $safetyInformation,
    ) {
    }

    /** A manufacturer outside the Union is set but no responsible person could be resolved. */
    public function isResponsiblePersonMissing(): bool
    {
        return $this->responsiblePersonRequired && null === $this->responsiblePerson;
    }

    public function isEmpty(): bool
    {
        return
            null === $this->manufacturer &&
            null === $this->responsiblePerson &&
            null === $this->typeDesignation &&
            null === $this->otherIdentifier &&
            null === $this->warnings &&
            null === $this->safetyInformation
        ;
    }
}
