<?php

declare(strict_types=1);

namespace Tests\Tmonier\SyliusGpsrPlugin\Unit\Entity;

use PHPUnit\Framework\TestCase;
use Tmonier\SyliusGpsrPlugin\Entity\Manufacturer;
use Tmonier\SyliusGpsrPlugin\Entity\ProductSafety;
use Tmonier\SyliusGpsrPlugin\Entity\ProductSafetyTranslation;

final class ProductSafetyTest extends TestCase
{
    public function testANewProductSafetyIsEmpty(): void
    {
        self::assertTrue($this->createProductSafety()->isEmpty());
    }

    public function testBlankValuesAreStoredAsNull(): void
    {
        $productSafety = $this->createProductSafety();
        $productSafety->setTypeDesignation('   ');
        $productSafety->setOtherIdentifier('');
        $productSafety->setWarnings("\n");

        self::assertNull($productSafety->getTypeDesignation());
        self::assertNull($productSafety->getOtherIdentifier());
        self::assertNull($productSafety->getWarnings());
        self::assertTrue($productSafety->isEmpty());
    }

    public function testAnyValueMakesItNonEmpty(): void
    {
        $withManufacturer = $this->createProductSafety();
        $withManufacturer->setManufacturer(new Manufacturer());
        self::assertFalse($withManufacturer->isEmpty());

        $withType = $this->createProductSafety();
        $withType->setTypeDesignation(' TX-100 ');
        self::assertSame('TX-100', $withType->getTypeDesignation());
        self::assertFalse($withType->isEmpty());

        $withSafetyInformation = $this->createProductSafety();
        $withSafetyInformation->setSafetyInformation('Keep away from fire.');
        self::assertFalse($withSafetyInformation->isEmpty());
    }

    public function testWarningsAreTranslated(): void
    {
        $productSafety = $this->createProductSafety();
        $productSafety->setWarnings('Not suitable for children under 3 years.');

        $productSafety->setCurrentLocale('fr_FR');
        $productSafety->setFallbackLocale('fr_FR');
        $productSafety->setWarnings('Ne convient pas aux enfants de moins de 3 ans.');

        $english = $productSafety->getTranslations()->get('en_US');
        self::assertInstanceOf(ProductSafetyTranslation::class, $english);
        self::assertSame('Not suitable for children under 3 years.', $english->getWarnings());
        self::assertSame('Ne convient pas aux enfants de moins de 3 ans.', $productSafety->getTranslation('fr_FR')->getWarnings());
        self::assertSame($productSafety, $english->getTranslatable());
    }

    private function createProductSafety(): ProductSafety
    {
        $productSafety = new ProductSafety();
        $productSafety->setCurrentLocale('en_US');
        $productSafety->setFallbackLocale('en_US');

        return $productSafety;
    }
}
