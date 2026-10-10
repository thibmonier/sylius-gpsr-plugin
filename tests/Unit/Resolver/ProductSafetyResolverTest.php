<?php

declare(strict_types=1);

namespace Tests\Tmonier\SyliusGpsrPlugin\Unit\Resolver;

use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\Product;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Sylius\Component\Locale\Context\LocaleNotFoundException;
use Sylius\Resource\Translation\Provider\TranslationLocaleProviderInterface;
use Tmonier\SyliusGpsrPlugin\Entity\Manufacturer;
use Tmonier\SyliusGpsrPlugin\Entity\ProductSafety;
use Tmonier\SyliusGpsrPlugin\Entity\ProductSafetyTranslation;
use Tmonier\SyliusGpsrPlugin\Entity\ResponsiblePerson;
use Tmonier\SyliusGpsrPlugin\Model\ProductSafetyView;
use Tmonier\SyliusGpsrPlugin\Repository\ProductSafetyRepositoryInterface;
use Tmonier\SyliusGpsrPlugin\Resolver\ProductSafetyResolver;

final class ProductSafetyResolverTest extends TestCase
{
    private ?ProductSafety $productSafety = null;

    private string $currentLocale = 'fr_FR';

    public function testItReturnsNullWithoutData(): void
    {
        self::assertNull($this->resolve());

        $this->productSafety = new ProductSafety();
        self::assertNull($this->resolve(), 'An empty record is treated as no data.');
    }

    public function testAnEuManufacturerDoesNotRequireAResponsiblePerson(): void
    {
        $this->productSafety = new ProductSafety();
        $this->productSafety->setManufacturer($this->manufacturer('FR'));
        $this->productSafety->setTypeDesignation('TX-1');
        $this->productSafety->setOtherIdentifier('4006381333931');

        $view = $this->resolve();

        self::assertNotNull($view);
        self::assertSame('ACME', $view->manufacturer?->getName());
        self::assertNull($view->responsiblePerson);
        self::assertFalse($view->responsiblePersonRequired);
        self::assertFalse($view->isResponsiblePersonMissing());
        self::assertSame('TX-1', $view->typeDesignation);
        self::assertSame('4006381333931', $view->otherIdentifier);
    }

    public function testAnEuManufacturerResponsiblePersonIsStillShownWhenSet(): void
    {
        $manufacturer = $this->manufacturer('DE');
        $manufacturer->setDefaultResponsiblePerson($this->responsiblePerson('default'));
        $this->productSafety = new ProductSafety();
        $this->productSafety->setManufacturer($manufacturer);

        self::assertSame('default', $this->resolve()?->responsiblePerson?->getCode());
    }

    public function testANonEuManufacturerUsesItsDefaultResponsiblePerson(): void
    {
        $manufacturer = $this->manufacturer('CN');
        $this->productSafety = new ProductSafety();
        $this->productSafety->setManufacturer($manufacturer);

        $view = $this->resolve();
        self::assertNotNull($view);
        self::assertTrue($view->responsiblePersonRequired);
        self::assertTrue($view->isResponsiblePersonMissing());

        $manufacturer->setDefaultResponsiblePerson($this->responsiblePerson('default'));
        self::assertSame('default', $this->resolve()?->responsiblePerson?->getCode());

        $this->productSafety->setResponsiblePerson($this->responsiblePerson('override'));
        $view = $this->resolve();
        self::assertNotNull($view);
        self::assertSame('override', $view->responsiblePerson?->getCode());
        self::assertFalse($view->isResponsiblePersonMissing());
    }

    public function testTextsUseTheCurrentLocaleWithFallbackPerField(): void
    {
        $this->productSafety = $productSafety = new ProductSafety();
        $this->addTranslation('en_US', 'Keep away from fire.', 'Wash at 30 °C.');
        $this->addTranslation('fr_FR', 'Tenir éloigné du feu.', null);

        $view = $this->resolve();
        self::assertNotNull($view);
        self::assertSame('Tenir éloigné du feu.', $view->warnings);
        self::assertSame('Wash at 30 °C.', $view->safetyInformation);

        self::assertSame('Keep away from fire.', $this->resolve('de_DE')?->warnings);
        self::assertCount(2, $productSafety->getTranslations(), 'Resolving never creates translations.');
    }

    public function testItWorksWithoutLocaleContext(): void
    {
        $this->currentLocale = '';
        $this->productSafety = new ProductSafety();
        $this->addTranslation('en_US', 'Keep away from fire.', null);

        self::assertSame('Keep away from fire.', $this->resolve()?->warnings);
    }

    private function resolve(?string $localeCode = null): ?ProductSafetyView
    {
        $repository = $this->createMock(ProductSafetyRepositoryInterface::class);
        $repository->method('findOneByProduct')->willReturn($this->productSafety);

        $localeContext = $this->createMock(LocaleContextInterface::class);
        if ('' === $this->currentLocale) {
            $localeContext->method('getLocaleCode')->willThrowException(new LocaleNotFoundException());
        } else {
            $localeContext->method('getLocaleCode')->willReturn($this->currentLocale);
        }

        $localeProvider = $this->createMock(TranslationLocaleProviderInterface::class);
        $localeProvider->method('getDefaultLocaleCode')->willReturn('en_US');

        return (new ProductSafetyResolver($repository, $localeContext, $localeProvider))->resolve(new Product(), $localeCode);
    }

    private function addTranslation(string $locale, ?string $warnings, ?string $safetyInformation): void
    {
        $translation = new ProductSafetyTranslation();
        $translation->setLocale($locale);
        $translation->setWarnings($warnings);
        $translation->setSafetyInformation($safetyInformation);
        $this->productSafety?->addTranslation($translation);
    }

    private function manufacturer(string $countryCode): Manufacturer
    {
        $manufacturer = new Manufacturer();
        $manufacturer->setName('ACME');
        $manufacturer->setCountryCode($countryCode);

        return $manufacturer;
    }

    private function responsiblePerson(string $code): ResponsiblePerson
    {
        $person = new ResponsiblePerson();
        $person->setCode($code);

        return $person;
    }
}
