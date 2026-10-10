<?php

declare(strict_types=1);

namespace Tests\Tmonier\SyliusGpsrPlugin\Unit\Entity;

use PHPUnit\Framework\TestCase;
use Tmonier\SyliusGpsrPlugin\Entity\Manufacturer;
use Tmonier\SyliusGpsrPlugin\Entity\ResponsiblePerson;

final class ManufacturerTest extends TestCase
{
    public function testAnEuManufacturerDoesNotNeedAResponsiblePerson(): void
    {
        $manufacturer = new Manufacturer();
        $manufacturer->setCountryCode('fr');

        self::assertSame('FR', $manufacturer->getCountryCode());
        self::assertTrue($manufacturer->isEstablishedInEu());
        self::assertFalse($manufacturer->requiresResponsiblePerson());
        self::assertFalse($manufacturer->isMissingResponsiblePerson());
    }

    public function testANonEuManufacturerNeedsAResponsiblePerson(): void
    {
        $manufacturer = new Manufacturer();
        $manufacturer->setCountryCode('CN');

        self::assertFalse($manufacturer->isEstablishedInEu());
        self::assertTrue($manufacturer->requiresResponsiblePerson());
        self::assertTrue($manufacturer->isMissingResponsiblePerson());

        $manufacturer->setDefaultResponsiblePerson(new ResponsiblePerson());

        self::assertTrue($manufacturer->requiresResponsiblePerson());
        self::assertFalse($manufacturer->isMissingResponsiblePerson());
    }

    public function testAManufacturerWithoutCountryIsNotFlagged(): void
    {
        $manufacturer = new Manufacturer();

        self::assertFalse($manufacturer->isEstablishedInEu());
        self::assertFalse($manufacturer->requiresResponsiblePerson());
    }

    public function testItIsStringable(): void
    {
        $manufacturer = new Manufacturer();
        $manufacturer->setName('ACME Toys');

        self::assertSame('ACME Toys', (string) $manufacturer);
    }
}
