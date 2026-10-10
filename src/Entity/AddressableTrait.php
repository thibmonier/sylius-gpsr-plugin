<?php

declare(strict_types=1);

namespace Tmonier\SyliusGpsrPlugin\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Tmonier\SyliusGpsrPlugin\Model\EuMemberStates;

trait AddressableTrait
{
    /** @var mixed */
    protected $id;

    #[Assert\NotBlank(groups: ['sylius'])]
    #[Assert\Length(max: 255, groups: ['sylius'])]
    #[Assert\Regex(pattern: '/^[\w-]*$/', message: 'tmonier_gpsr.code.regex', groups: ['sylius'])]
    protected ?string $code = null;

    #[Assert\NotBlank(groups: ['sylius'])]
    #[Assert\Length(max: 255, groups: ['sylius'])]
    protected ?string $name = null;

    #[Assert\NotBlank(groups: ['sylius'])]
    #[Assert\Length(max: 255, groups: ['sylius'])]
    protected ?string $street = null;

    #[Assert\NotBlank(groups: ['sylius'])]
    #[Assert\Length(max: 32, groups: ['sylius'])]
    protected ?string $postcode = null;

    #[Assert\NotBlank(groups: ['sylius'])]
    #[Assert\Length(max: 255, groups: ['sylius'])]
    protected ?string $city = null;

    #[Assert\NotBlank(groups: ['sylius'])]
    #[Assert\Country(groups: ['sylius'])]
    protected ?string $countryCode = null;

    #[Assert\NotBlank(groups: ['sylius'])]
    #[Assert\Email(groups: ['sylius'])]
    #[Assert\Length(max: 255, groups: ['sylius'])]
    protected ?string $email = null;

    public function getId(): mixed
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): void
    {
        $this->code = $code;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): void
    {
        $this->street = $street;
    }

    public function getPostcode(): ?string
    {
        return $this->postcode;
    }

    public function setPostcode(?string $postcode): void
    {
        $this->postcode = $postcode;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    public function setCountryCode(?string $countryCode): void
    {
        $this->countryCode = null === $countryCode ? null : strtoupper($countryCode);
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function isEstablishedInEu(): bool
    {
        return EuMemberStates::contains($this->countryCode);
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }
}
