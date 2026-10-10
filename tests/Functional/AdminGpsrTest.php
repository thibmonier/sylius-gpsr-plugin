<?php

declare(strict_types=1);

namespace Tests\Tmonier\SyliusGpsrPlugin\Functional;

use Sylius\Component\Core\Model\ProductInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tmonier\SyliusGpsrPlugin\Entity\Manufacturer;
use Tmonier\SyliusGpsrPlugin\Entity\ManufacturerInterface;
use Tmonier\SyliusGpsrPlugin\Entity\ProductSafetyTranslationInterface;
use Tmonier\SyliusGpsrPlugin\Entity\ResponsiblePerson;
use Tmonier\SyliusGpsrPlugin\Entity\ResponsiblePersonInterface;

final class AdminGpsrTest extends WebTestCase
{
    use GpsrTestTrait;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->resetTestData();
        $product = $this->entityManager()->getRepository(ProductInterface::class)->findOneBy(['code' => 'TEST_GPSR_PRODUCT']);
        if (null !== $product) {
            $this->entityManager()->remove($product);
            $this->entityManager()->flush();
        }
        $this->loginAdmin();
    }

    public function testAnAdministratorCreatesAResponsiblePersonAndAManufacturer(): void
    {
        $crawler = $this->client->request('GET', '/admin/responsible-persons/');
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('a[href="/admin/manufacturers/"]');
        self::assertSelectorExists('a[href="/admin/responsible-persons/"]');

        $this->client->click($crawler->selectLink('Create')->link());
        self::assertResponseIsSuccessful();
        $this->client->submitForm('Create', [
            'tmonier_gpsr_responsible_person[code]' => 'test_importer',
            'tmonier_gpsr_responsible_person[name]' => 'Test Import GmbH',
            'tmonier_gpsr_responsible_person[role]' => ResponsiblePersonInterface::ROLE_IMPORTER,
            'tmonier_gpsr_responsible_person[street]' => 'Hauptstraße 1',
            'tmonier_gpsr_responsible_person[postcode]' => '10115',
            'tmonier_gpsr_responsible_person[city]' => 'Berlin',
            'tmonier_gpsr_responsible_person[countryCode]' => 'DE',
            'tmonier_gpsr_responsible_person[email]' => 'rp@test-import.example',
        ]);
        self::assertResponseRedirects();

        $person = $this->entityManager()->getRepository(ResponsiblePerson::class)->findOneBy(['code' => 'test_importer']);
        self::assertInstanceOf(ResponsiblePersonInterface::class, $person);
        self::assertSame('Berlin', $person->getCity());

        $this->client->request('GET', '/admin/manufacturers/new');
        self::assertResponseIsSuccessful();
        $this->client->submitForm('Create', [
            'tmonier_gpsr_manufacturer[code]' => 'test_shenzhen',
            'tmonier_gpsr_manufacturer[name]' => 'Shenzhen Test Factory Ltd.',
            'tmonier_gpsr_manufacturer[tradeMark]' => 'TESTCO',
            'tmonier_gpsr_manufacturer[street]' => '88 Keyuan Road',
            'tmonier_gpsr_manufacturer[postcode]' => '518057',
            'tmonier_gpsr_manufacturer[city]' => 'Shenzhen',
            'tmonier_gpsr_manufacturer[countryCode]' => 'CN',
            'tmonier_gpsr_manufacturer[email]' => 'export@test-factory.example',
            'tmonier_gpsr_manufacturer[website]' => 'https://test-factory.example',
            'tmonier_gpsr_manufacturer[defaultResponsiblePerson]' => $this->idOf($person),
        ]);
        self::assertResponseRedirects();
        $this->client->followRedirect();
        self::assertSelectorNotExists('[data-test-responsible-person-warning]');

        $this->entityManager()->clear();
        $manufacturer = $this->entityManager()->getRepository(Manufacturer::class)->findOneBy(['code' => 'test_shenzhen']);
        self::assertInstanceOf(ManufacturerInterface::class, $manufacturer);
        self::assertSame('test_importer', $manufacturer->getDefaultResponsiblePerson()?->getCode());
        self::assertFalse($manufacturer->isEstablishedInEu());

        $crawler = $this->client->request('GET', '/admin/manufacturers/');
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Shenzhen Test Factory Ltd.', $crawler->filter('table')->text());
    }

    public function testANonEuManufacturerWithoutResponsiblePersonIsSavedWithAWarning(): void
    {
        $this->client->request('GET', '/admin/manufacturers/new');
        $this->client->submitForm('Create', [
            'tmonier_gpsr_manufacturer[code]' => 'test_us',
            'tmonier_gpsr_manufacturer[name]' => 'US Test Corp.',
            'tmonier_gpsr_manufacturer[street]' => '1 Main Street',
            'tmonier_gpsr_manufacturer[postcode]' => '10001',
            'tmonier_gpsr_manufacturer[city]' => 'New York',
            'tmonier_gpsr_manufacturer[countryCode]' => 'US',
            'tmonier_gpsr_manufacturer[email]' => 'safety@us-test.example',
        ]);
        self::assertResponseRedirects();

        $this->client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('[data-test-responsible-person-warning]');

        $crawler = $this->client->request('GET', '/admin/manufacturers/');
        self::assertCount(1, $crawler->filter('[data-test-responsible-person-missing]'));
    }

    public function testInvalidOperatorsAreRejected(): void
    {
        $this->client->request('GET', '/admin/responsible-persons/new');
        $this->client->submitForm('Create', [
            'tmonier_gpsr_responsible_person[code]' => 'test_uk',
            'tmonier_gpsr_responsible_person[name]' => 'UK Ltd',
            'tmonier_gpsr_responsible_person[role]' => ResponsiblePersonInterface::ROLE_AUTHORISED_REPRESENTATIVE,
            'tmonier_gpsr_responsible_person[street]' => '1 High Street',
            'tmonier_gpsr_responsible_person[postcode]' => 'SW1A 1AA',
            'tmonier_gpsr_responsible_person[city]' => 'London',
            'tmonier_gpsr_responsible_person[countryCode]' => 'GB',
            'tmonier_gpsr_responsible_person[email]' => 'not-an-email',
        ]);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('body', 'The responsible person must be established in the European Union.');
        self::assertNull($this->entityManager()->getRepository(ResponsiblePerson::class)->findOneBy(['code' => 'test_uk']));
    }

    public function testAnAdministratorAttachesGpsrDataToAProduct(): void
    {
        $manufacturer = $this->createManufacturer('test_fr', 'FR');
        $product = $this->findProduct('Lunar_Echo_T_Shirt');
        self::assertNull($this->findProductSafety($product));

        $this->client->request('GET', $this->productEditUrl($product));
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('[data-test-side-navigation-tab="product-safety"]');

        $this->client->submitForm('Update', [
            'sylius_admin_product[productSafety][manufacturer]' => $this->idOf($manufacturer),
            'sylius_admin_product[productSafety][typeDesignation]' => 'LUNAR-ECHO',
            'sylius_admin_product[productSafety][otherIdentifier]' => '4006381333931',
            'sylius_admin_product[productSafety][translations][en_US][warnings]' => 'Keep away from fire.',
            'sylius_admin_product[productSafety][translations][fr_FR][warnings]' => 'Tenir éloigné du feu.',
        ]);
        self::assertResponseRedirects();

        $productSafety = $this->findProductSafety($product);
        self::assertNotNull($productSafety);
        self::assertSame('test_fr', $productSafety->getManufacturer()?->getCode());
        self::assertSame('LUNAR-ECHO', $productSafety->getTypeDesignation());
        self::assertSame('4006381333931', $productSafety->getOtherIdentifier());
        $french = $productSafety->getTranslations()->get('fr_FR');
        self::assertInstanceOf(ProductSafetyTranslationInterface::class, $french);
        self::assertSame('Tenir éloigné du feu.', $french->getWarnings());
        self::assertFalse($productSafety->getTranslations()->containsKey('de_DE'), 'Empty translations are not stored.');

        // Editing again updates the same record.
        $crawler = $this->client->request('GET', $this->productEditUrl($product));
        self::assertSame('LUNAR-ECHO', $crawler->filter('[name="sylius_admin_product[productSafety][typeDesignation]"]')->attr('value'));
        $this->client->submitForm('Update', [
            'sylius_admin_product[productSafety][typeDesignation]' => 'LUNAR-ECHO-2',
        ]);
        self::assertResponseRedirects();

        $updated = $this->findProductSafety($product);
        self::assertNotNull($updated);
        self::assertSame($productSafety->getId(), $updated->getId());
        self::assertSame('LUNAR-ECHO-2', $updated->getTypeDesignation());
    }

    public function testSavingAProductWithoutGpsrDataCreatesNoRecord(): void
    {
        $product = $this->findProduct('Nebula_Dream_T_Shirt');

        $this->client->request('GET', $this->productEditUrl($product));
        $this->client->submitForm('Update');
        self::assertResponseRedirects();

        self::assertNull($this->findProductSafety($product));
    }

    public function testAdminPagesAreFullyTranslated(): void
    {
        $manufacturer = $this->createManufacturer('test_cn', 'CN');
        $person = $this->createResponsiblePerson('test_rp');
        $product = $this->findProduct('Nebula_Dream_T_Shirt');

        foreach ([
            '/admin/manufacturers/',
            '/admin/manufacturers/new',
            sprintf('/admin/manufacturers/%s/edit', $this->idOf($manufacturer)),
            '/admin/responsible-persons/',
            '/admin/responsible-persons/new',
            sprintf('/admin/responsible-persons/%s/edit', $this->idOf($person)),
            $this->productEditUrl($product),
            '/admin/products/',
        ] as $url) {
            $crawler = $this->client->request('GET', $url);
            self::assertResponseIsSuccessful($url);
            self::assertStringNotContainsString('tmonier_gpsr.', $crawler->filter('body')->text(), $url);
        }
    }

    public function testInvalidGpsrDataPreventsSavingTheProduct(): void
    {
        $product = $this->findProduct('Nebula_Dream_T_Shirt');

        $this->client->request('GET', $this->productEditUrl($product));
        $this->client->submitForm('Update', [
            'sylius_admin_product[productSafety][typeDesignation]' => str_repeat('x', 256),
        ]);

        self::assertResponseStatusCodeSame(422);
        self::assertNull($this->findProductSafety($product));
    }

    public function testAnAdministratorCreatesAProductWithGpsrData(): void
    {
        $manufacturer = $this->createManufacturer('test_fr', 'FR');

        $this->client->request('GET', '/admin/products/new/simple');
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('[data-test-side-navigation-tab="product-safety"]');

        $this->client->submitForm('Create', [
            'sylius_admin_product[code]' => 'TEST_GPSR_PRODUCT',
            'sylius_admin_product[translations][en_US][name]' => 'GPSR test product',
            'sylius_admin_product[translations][en_US][slug]' => 'gpsr-test-product',
            'sylius_admin_product[variant][channelPricings][FASHION_WEB][price]' => '10.00',
            'sylius_admin_product[productSafety][manufacturer]' => $this->idOf($manufacturer),
            'sylius_admin_product[productSafety][typeDesignation]' => 'NEW-1',
        ]);
        self::assertResponseRedirects();

        $product = $this->findProduct('TEST_GPSR_PRODUCT');
        $productSafety = $this->findProductSafety($product);
        self::assertNotNull($productSafety);
        self::assertSame('test_fr', $productSafety->getManufacturer()?->getCode());
        self::assertSame('NEW-1', $productSafety->getTypeDesignation());
    }

    public function testTheProductsGridFiltersProductsWithMissingGpsrData(): void
    {
        $responsiblePerson = $this->createResponsiblePerson('test_rp');
        $complete = $this->findProduct('Solar_Echo_T_Shirt');
        $incomplete = $this->findProduct('Ocean_Wave_Jeans');

        $withResponsiblePerson = $this->createManufacturer('test_cn_ok', 'CN', $responsiblePerson);
        $withoutResponsiblePerson = $this->createManufacturer('test_cn_ko', 'CN');

        $this->client->request('GET', $this->productEditUrl($complete));
        $this->client->submitForm('Update', [
            'sylius_admin_product[productSafety][manufacturer]' => $this->idOf($withResponsiblePerson),
        ]);
        $this->client->request('GET', $this->productEditUrl($incomplete));
        $this->client->submitForm('Update', [
            'sylius_admin_product[productSafety][manufacturer]' => $this->idOf($withoutResponsiblePerson),
        ]);

        $crawler = $this->client->request('GET', '/admin/products/?limit=100&criteria[tmonier_gpsr_data_missing]=true&criteria[search][value]=e');
        self::assertResponseIsSuccessful();
        $table = $crawler->filter('table')->text();
        self::assertStringContainsString('Ocean_Wave_Jeans', $table);
        self::assertStringNotContainsString('Solar_Echo_T_Shirt', $table);

        $crawler = $this->client->request('GET', '/admin/products/?limit=100&criteria[tmonier_gpsr_data_missing]=false');
        self::assertResponseIsSuccessful();
        $table = $crawler->filter('table')->text();
        self::assertStringContainsString('Solar_Echo_T_Shirt', $table);
        self::assertStringNotContainsString('Ocean_Wave_Jeans', $table);
        self::assertStringNotContainsString('Nebula_Dream_T_Shirt', $table);
    }

    private function productEditUrl(ProductInterface $product): string
    {
        $id = $product->getId();
        self::assertIsInt($id);

        return sprintf('/admin/products/%d/edit', $id);
    }
}
