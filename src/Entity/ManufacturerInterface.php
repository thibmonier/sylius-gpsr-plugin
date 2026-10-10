<?php

declare(strict_types=1);

namespace Tmonier\SyliusGpsrPlugin\Entity;

/**
 * Manufacturer as defined in Art. 3(8) GPSR. Its name, postal and electronic address must be
 * displayed with every online offer (Art. 19(a)).
 */
interface ManufacturerInterface extends AddressableInterface
{
    public function getTradeMark(): ?string;

    public function setTradeMark(?string $tradeMark): void;

    public function getWebsite(): ?string;

    public function setWebsite(?string $website): void;

    public function getDefaultResponsiblePerson(): ?ResponsiblePersonInterface;

    public function setDefaultResponsiblePerson(?ResponsiblePersonInterface $responsiblePerson): void;

    /**
     * A manufacturer established outside the Union needs an economic operator established in the
     * Union (Art. 16 GPSR, Art. 4 of Regulation (EU) 2019/1020).
     */
    public function requiresResponsiblePerson(): bool;

    /** True when a responsible person is required but no default one is configured. */
    public function isMissingResponsiblePerson(): bool;
}
