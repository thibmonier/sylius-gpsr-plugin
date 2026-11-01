<?php

declare(strict_types=1);

namespace Tests\Tmonier\SyliusGpsrPlugin\Unit\Model;

use PHPUnit\Framework\TestCase;
use Tmonier\SyliusGpsrPlugin\Entity\Manufacturer;
use Tmonier\SyliusGpsrPlugin\Entity\ResponsiblePerson;
use Tmonier\SyliusGpsrPlugin\Model\ProductSafetyView;

final class ProductSafetyViewTest extends TestCase
{
    public function testIsEmptyWhenAllFieldsAreNull(): void
    {
        $view = new ProductSafetyView(null, null, false, null, null, null, null);

        self::assertTrue($view->isEmpty());
    }

    public function testIsNotEmptyWhenManufacturerIsSet(): void
    {
        $view = new ProductSafetyView(new Manufacturer(), null, false, null, null, null, null);

        self::assertFalse($view->isEmpty());
    }

    public function testIsNotEmptyWhenResponsiblePersonIsSet(): void
    {
        $view = new ProductSafetyView(null, new ResponsiblePerson(), false, null, null, null, null);

        self::assertFalse($view->isEmpty());
    }

    public function testIsNotEmptyWhenTypeDesignationIsSet(): void
    {
        $view = new ProductSafetyView(null, null, false, 'Model X', null, null, null);

        self::assertFalse($view->isEmpty());
    }

    public function testIsNotEmptyWhenOtherIdentifierIsSet(): void
    {
        $view = new ProductSafetyView(null, null, false, null, 'EAN123', null, null);

        self::assertFalse($view->isEmpty());
    }

    public function testIsNotEmptyWhenWarningsAreSet(): void
    {
        $view = new ProductSafetyView(null, null, false, null, null, 'Caution', null);

        self::assertFalse($view->isEmpty());
    }

    public function testIsNotEmptyWhenSafetyInformationIsSet(): void
    {
        $view = new ProductSafetyView(null, null, false, null, null, null, 'Keep dry');

        self::assertFalse($view->isEmpty());
    }

    public function testResponsiblePersonIsMissingWhenRequiredAndAbsent(): void
    {
        $view = new ProductSafetyView(new Manufacturer(), null, true, null, null, null, null);

        self::assertTrue($view->isResponsiblePersonMissing());
    }

    public function testResponsiblePersonIsNotMissingWhenNotRequired(): void
    {
        $view = new ProductSafetyView(new Manufacturer(), null, false, null, null, null, null);

        self::assertFalse($view->isResponsiblePersonMissing());
    }

    public function testResponsiblePersonIsNotMissingWhenPresent(): void
    {
        $view = new ProductSafetyView(new Manufacturer(), new ResponsiblePerson(), true, null, null, null, null);

        self::assertFalse($view->isResponsiblePersonMissing());
    }

    public function testPublicPropertiesAreAccessible(): void
    {
        $manufacturer = new Manufacturer();
        $responsiblePerson = new ResponsiblePerson();

        $view = new ProductSafetyView(
            $manufacturer,
            $responsiblePerson,
            true,
            'Type A',
            'ID-42',
            'Warning text',
            'Safety info',
        );

        self::assertSame($manufacturer, $view->manufacturer);
        self::assertSame($responsiblePerson, $view->responsiblePerson);
        self::assertTrue($view->responsiblePersonRequired);
        self::assertSame('Type A', $view->typeDesignation);
        self::assertSame('ID-42', $view->otherIdentifier);
        self::assertSame('Warning text', $view->warnings);
        self::assertSame('Safety info', $view->safetyInformation);
    }
}
