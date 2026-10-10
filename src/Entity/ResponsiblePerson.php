<?php

declare(strict_types=1);

namespace Tmonier\SyliusGpsrPlugin\Entity;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[UniqueEntity(fields: ['code'], message: 'tmonier_gpsr.code.unique', groups: ['sylius'])]
class ResponsiblePerson implements ResponsiblePersonInterface, \Stringable
{
    use AddressableTrait;

    #[Assert\NotBlank(groups: ['sylius'])]
    #[Assert\Choice(choices: ResponsiblePersonInterface::ROLES, groups: ['sylius'])]
    protected ?string $role = null;

    #[Assert\Length(max: 64, groups: ['sylius'])]
    protected ?string $phone = null;

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): void
    {
        $this->role = $role;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    /**
     * The responsible person must be established in the Union (Art. 4(1) of Regulation (EU) 2019/1020).
     */
    #[Assert\Callback(groups: ['sylius'])]
    public function validateEstablishedInEu(ExecutionContextInterface $context): void
    {
        if (null === $this->countryCode || $this->isEstablishedInEu()) {
            return;
        }

        $context
            ->buildViolation('tmonier_gpsr.responsible_person.established_in_eu')
            ->atPath('countryCode')
            ->addViolation()
        ;
    }
}
