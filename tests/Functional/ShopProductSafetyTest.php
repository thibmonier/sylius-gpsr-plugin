<?php

declare(strict_types=1);

namespace Tests\Tmonier\SyliusGpsrPlugin\Functional;

use Sylius\Component\Core\Model\ProductInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tmonier\SyliusGpsrPlugin\Entity\ManufacturerInterface;
use Tmonier\SyliusGpsrPlugin\Entity\ProductSafety;
use Tmonier\SyliusGpsrPlugin\Entity\ProductSafetyTranslation;
use Tmonier\SyliusGpsrPlugin\Entity\ResponsiblePersonInterface;
use Tmonier\SyliusGpsrPlugin\Resolver\ProductSafetyResolverInterface;

final class ShopProductSafetyTest extends WebTestCase
{
    use GpsrTestTrait;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->resetTestData();
    }

    public function testTheProductPageShowsTheManufacturerOfAnEuProduct(): void
    {
        $product = $this->findProduct('Lunar_Echo_T_Shirt');
        $this->attach($product, $this->createManufacturer('test_fr', 'FR'), null, [
            'en_US' => 'Keep away from fire.',
            'fr_FR' => 'Tenir éloigné du feu.',
        ]);

        $crawler = $this->client->request('GET', $this->productUrl($product));

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('[data-test-product-safety]');
        self::assertSelectorTextContains('[data-test-product-safety-manufacturer]', 'Manufacturer test_fr');
        self::assertSelectorTextContains('[data-test-product-safety-manufacturer] [data-test-email]', 'test_fr@manufacturer.example');
        self::assertSelectorTextContains('[data-test-product-safety-manufacturer]', 'France');
        self::assertSelectorNotExists('[data-test-product-safety-responsible-person]');
        self::assertSelectorTextContains('[data-test-product-safety-type]', 'TEST-TYPE');
        self::assertSelectorTextContains('[data-test-product-safety-warnings]', 'Keep away from fire.');
        self::assertStringNotContainsString('Tenir éloigné du feu.', $crawler->filter('[data-test-product-safety]')->text());

        // The channel of the fixtures only has en_US: check the other locales on the resolver.
        $resolver = self::getContainer()->get(ProductSafetyResolverInterface::class);
        self::assertInstanceOf(ProductSafetyResolverInterface::class, $resolver);
        self::assertSame('Tenir éloigné du feu.', $resolver->resolve($product, 'fr_FR')?->warnings);
        self::assertSame('Keep away from fire.', $resolver->resolve($product, 'de_DE')?->warnings, 'Falls back to the default locale.');
    }

    public function testTheProductPageShowsTheResponsiblePersonOfANonEuManufacturer(): void
    {
        $product = $this->findProduct('Solar_Echo_T_Shirt');
        $responsiblePerson = $this->createResponsiblePerson('test_rp');
        $this->attach($product, $this->createManufacturer('test_cn', 'CN', $responsiblePerson), null, ['en_US' => 'Small parts.']);

        $this->client->request('GET', $this->productUrl($product));

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('[data-test-product-safety-manufacturer] [data-test-email]', 'test_cn@manufacturer.example');
        self::assertSelectorTextContains('[data-test-product-safety-responsible-person]', 'Test Import GmbH');
        self::assertSelectorTextContains('[data-test-product-safety-responsible-person]', 'Importer');
        self::assertSelectorTextContains('[data-test-product-safety-responsible-person] [data-test-email]', 'rp@test-import.example');
    }

    public function testTheProductOverrideWinsOverTheManufacturerDefault(): void
    {
        $product = $this->findProduct('Ocean_Wave_Jeans');
        $default = $this->createResponsiblePerson('test_rp_default');
        $override = $this->createResponsiblePerson('test_rp_override', 'IT');
        $override->setName('Rappresentante Test S.r.l.');
        $this->entityManager()->flush();
        $this->attach($product, $this->createManufacturer('test_cn', 'CN', $default), $override, []);

        $this->client->request('GET', $this->productUrl($product));

        self::assertSelectorTextContains('[data-test-product-safety-responsible-person]', 'Rappresentante Test S.r.l.');
    }

    public function testAProductWithoutGpsrDataShowsNoBlock(): void
    {
        $product = $this->findProduct('Nebula_Dream_T_Shirt');

        $this->client->request('GET', $this->productUrl($product));

        self::assertResponseIsSuccessful();
        self::assertSelectorNotExists('[data-test-product-safety]');
    }

    /** @param array<string, string> $warnings */
    private function attach(ProductInterface $product, ManufacturerInterface $manufacturer, ?ResponsiblePersonInterface $responsiblePerson, array $warnings): void
    {
        $productSafety = new ProductSafety();
        $productSafety->setCurrentLocale('en_US');
        $productSafety->setFallbackLocale('en_US');
        $productSafety->setProduct($product);
        $productSafety->setManufacturer($manufacturer);
        $productSafety->setResponsiblePerson($responsiblePerson);
        $productSafety->setTypeDesignation('TEST-TYPE');
        foreach ($warnings as $locale => $warning) {
            $translation = new ProductSafetyTranslation();
            $translation->setLocale($locale);
            $translation->setWarnings($warning);
            $productSafety->addTranslation($translation);
        }

        $this->entityManager()->persist($productSafety);
        $this->entityManager()->flush();
    }

    private function productUrl(ProductInterface $product): string
    {
        return sprintf('/en_US/products/%s', $product->getTranslation('en_US')->getSlug());
    }
}
