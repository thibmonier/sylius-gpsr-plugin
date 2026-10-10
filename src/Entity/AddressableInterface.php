<?php

declare(strict_types=1);

namespace Tmonier\SyliusGpsrPlugin\Entity;

use Sylius\Resource\Model\CodeAwareInterface;
use Sylius\Resource\Model\ResourceInterface;

/**
 * Common contact data of an economic operator (postal address + electronic address).
 */
interface AddressableInterface extends ResourceInterface, CodeAwareInterface
{
    public function getName(): ?string;

    public function setName(?string $name): void;

    public function getStreet(): ?string;

    public function setStreet(?string $street): void;

    public function getPostcode(): ?string;

    public function setPostcode(?string $postcode): void;

    public function getCity(): ?string;

    public function setCity(?string $city): void;

    public function getCountryCode(): ?string;

    public function setCountryCode(?string $countryCode): void;

    public function getEmail(): ?string;

    public function setEmail(?string $email): void;

    public function isEstablishedInEu(): bool;
}
