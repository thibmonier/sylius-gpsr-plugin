<?php

declare(strict_types=1);

namespace Tests\Tmonier\SyliusGpsrPlugin\Functional;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\AdminUserInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Resource\Model\ResourceInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Tmonier\SyliusGpsrPlugin\Entity\Manufacturer;
use Tmonier\SyliusGpsrPlugin\Entity\ManufacturerInterface;
use Tmonier\SyliusGpsrPlugin\Entity\ProductSafety;
use Tmonier\SyliusGpsrPlugin\Entity\ProductSafetyInterface;
use Tmonier\SyliusGpsrPlugin\Entity\ResponsiblePerson;
use Tmonier\SyliusGpsrPlugin\Entity\ResponsiblePersonInterface;

/**
 * Helpers for tests running against the test application loaded with the Sylius fixtures.
 * Everything created by the tests uses the "test_" code prefix or one of the products below.
 */
trait GpsrTestTrait
{
    private KernelBrowser $client;

    /** Fixture products reserved for the tests (they never get demo GPSR data). */
    private const TEST_PRODUCTS = ['Lunar_Echo_T_Shirt', 'Nebula_Dream_T_Shirt', 'Solar_Echo_T_Shirt', 'Ocean_Wave_Jeans'];

    private function resetTestData(): void
    {
        $entityManager = $this->entityManager();
        $entityManager->createQuery(sprintf(
            'DELETE FROM %s ps WHERE ps.product IN (SELECT p.id FROM %s p WHERE p.code IN (:codes))',
            ProductSafety::class,
            ProductInterface::class,
        ))->setParameter('codes', self::TEST_PRODUCTS)->execute();
        $entityManager->createQuery(sprintf('DELETE FROM %s m WHERE m.code LIKE :prefix', Manufacturer::class))
            ->setParameter('prefix', 'test\_%')->execute();
        $entityManager->createQuery(sprintf('DELETE FROM %s r WHERE r.code LIKE :prefix', ResponsiblePerson::class))
            ->setParameter('prefix', 'test\_%')->execute();
    }

    private function loginAdmin(): void
    {
        $admin = $this->entityManager()->getRepository(AdminUserInterface::class)->findOneBy([]);
        self::assertInstanceOf(AdminUserInterface::class, $admin);
        $this->client->loginUser($admin, 'admin');
    }

    private function findProduct(string $code): ProductInterface
    {
        $product = $this->entityManager()->getRepository(ProductInterface::class)->findOneBy(['code' => $code]);
        self::assertInstanceOf(ProductInterface::class, $product, sprintf('The fixtures should contain the product "%s".', $code));

        return $product;
    }

    private function findProductSafety(ProductInterface $product): ?ProductSafetyInterface
    {
        $this->entityManager()->clear();
        $productSafety = $this->entityManager()->getRepository(ProductSafety::class)->findOneBy(['product' => $product->getId()]);

        return $productSafety instanceof ProductSafetyInterface ? $productSafety : null;
    }

    private function createResponsiblePerson(string $code, string $countryCode = 'DE'): ResponsiblePersonInterface
    {
        $person = new ResponsiblePerson();
        $person->setCode($code);
        $person->setName('Test Import GmbH');
        $person->setRole(ResponsiblePersonInterface::ROLE_IMPORTER);
        $person->setStreet('Hauptstraße 1');
        $person->setPostcode('10115');
        $person->setCity('Berlin');
        $person->setCountryCode($countryCode);
        $person->setEmail('rp@test-import.example');

        $this->entityManager()->persist($person);
        $this->entityManager()->flush();

        return $person;
    }

    private function createManufacturer(string $code, string $countryCode, ?ResponsiblePersonInterface $responsiblePerson = null): ManufacturerInterface
    {
        $manufacturer = new Manufacturer();
        $manufacturer->setCode($code);
        $manufacturer->setName(sprintf('Manufacturer %s', $code));
        $manufacturer->setStreet('1 Factory Road');
        $manufacturer->setPostcode('12345');
        $manufacturer->setCity('Factory City');
        $manufacturer->setCountryCode($countryCode);
        $manufacturer->setEmail(sprintf('%s@manufacturer.example', $code));
        $manufacturer->setDefaultResponsiblePerson($responsiblePerson);

        $this->entityManager()->persist($manufacturer);
        $this->entityManager()->flush();

        return $manufacturer;
    }

    private function idOf(ResourceInterface $resource): string
    {
        $id = $resource->getId();
        self::assertIsInt($id);

        return (string) $id;
    }

    private function entityManager(): EntityManagerInterface
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');

        return $entityManager;
    }
}
