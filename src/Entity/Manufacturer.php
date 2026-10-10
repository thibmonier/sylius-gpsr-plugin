<?php

declare(strict_types=1);

namespace Tmonier\SyliusGpsrPlugin\Entity;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[UniqueEntity(fields: ['code'], message: 'tmonier_gpsr.code.unique', groups: ['sylius'])]
class Manufacturer implements ManufacturerInterface, \Stringable
{
    use AddressableTrait;

    #[Assert\Length(max: 255, groups: ['sylius'])]
    protected ?string $tradeMark = null;

    #[Assert\Url(protocols: ['http', 'https'], groups: ['sylius'])]
    #[Assert\Length(max: 255, groups: ['sylius'])]
    protected ?string $website = null;

    protected ?ResponsiblePersonInterface $defaultResponsiblePerson = null;

    public function getTradeMark(): ?string
    {
        return $this->tradeMark;
    }

    public function setTradeMark(?string $tradeMark): void
    {
        $this->tradeMark = $tradeMark;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): void
    {
        $this->website = $website;
    }

    public function getDefaultResponsiblePerson(): ?ResponsiblePersonInterface
    {
        return $this->defaultResponsiblePerson;
    }

    public function setDefaultResponsiblePerson(?ResponsiblePersonInterface $responsiblePerson): void
    {
        $this->defaultResponsiblePerson = $responsiblePerson;
    }

    public function requiresResponsiblePerson(): bool
    {
        return null !== $this->countryCode && !$this->isEstablishedInEu();
    }

    public function isMissingResponsiblePerson(): bool
    {
        return $this->requiresResponsiblePerson() && null === $this->defaultResponsiblePerson;
    }
}
