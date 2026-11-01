<?php

declare(strict_types=1);

namespace Tests\Tmonier\SyliusGpsrPlugin\Unit\Entity;

use PHPUnit\Framework\TestCase;
use Tmonier\SyliusGpsrPlugin\Entity\ProductSafetyTranslation;

final class ProductSafetyTranslationTest extends TestCase
{
    public function testIdIsNullByDefault(): void
    {
        $translation = new ProductSafetyTranslation();

        self::assertNull($translation->getId());
    }

    public function testWarningsGetterSetter(): void
    {
        $translation = new ProductSafetyTranslation();

        self::assertNull($translation->getWarnings());

        $translation->setWarnings('Choking hazard');
        self::assertSame('Choking hazard', $translation->getWarnings());
    }

    public function testSafetyInformationGetterSetter(): void
    {
        $translation = new ProductSafetyTranslation();

        self::assertNull($translation->getSafetyInformation());

        $translation->setSafetyInformation('Keep away from fire');
        self::assertSame('Keep away from fire', $translation->getSafetyInformation());
    }

    public function testNormalizationTrimsWhitespace(): void
    {
        $translation = new ProductSafetyTranslation();

        $translation->setWarnings('  trimmed  ');
        self::assertSame('trimmed', $translation->getWarnings());

        $translation->setSafetyInformation('  also trimmed  ');
        self::assertSame('also trimmed', $translation->getSafetyInformation());
    }

    public function testEmptyStringIsNormalisedToNull(): void
    {
        $translation = new ProductSafetyTranslation();

        $translation->setWarnings('');
        self::assertNull($translation->getWarnings());

        $translation->setSafetyInformation('   ');
        self::assertNull($translation->getSafetyInformation());
    }

    public function testNullValueStaysNull(): void
    {
        $translation = new ProductSafetyTranslation();

        $translation->setWarnings(null);
        self::assertNull($translation->getWarnings());

        $translation->setSafetyInformation(null);
        self::assertNull($translation->getSafetyInformation());
    }

    public function testIsEmptyWhenBothFieldsAreNull(): void
    {
        $translation = new ProductSafetyTranslation();

        self::assertTrue($translation->isEmpty());
    }

    public function testIsNotEmptyWhenWarningsAreSet(): void
    {
        $translation = new ProductSafetyTranslation();
        $translation->setWarnings('Danger');

        self::assertFalse($translation->isEmpty());
    }

    public function testIsNotEmptyWhenSafetyInformationIsSet(): void
    {
        $translation = new ProductSafetyTranslation();
        $translation->setSafetyInformation('Wear gloves');

        self::assertFalse($translation->isEmpty());
    }

    public function testIsEmptyAfterClearingFields(): void
    {
        $translation = new ProductSafetyTranslation();
        $translation->setWarnings('Something');
        $translation->setSafetyInformation('Something else');

        $translation->setWarnings(null);
        $translation->setSafetyInformation(null);

        self::assertTrue($translation->isEmpty());
    }
}
