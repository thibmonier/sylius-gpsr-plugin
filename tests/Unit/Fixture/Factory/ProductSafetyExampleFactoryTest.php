<?php

declare(strict_types=1);

namespace Tests\Tmonier\SyliusGpsrPlugin\Unit\Fixture\Factory;

use PHPUnit\Framework\TestCase;
use Sylius\Component\Product\Model\ProductInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Sylius\Resource\Factory\FactoryInterface;
use Tmonier\SyliusGpsrPlugin\Entity\Manufacturer;
use Tmonier\SyliusGpsrPlugin\Entity\ManufacturerInterface;
use Tmonier\SyliusGpsrPlugin\Entity\ProductSafety;
use Tmonier\SyliusGpsrPlugin\Entity\ProductSafetyInterface;
use Tmonier\SyliusGpsrPlugin\Entity\ResponsiblePerson;
use Tmonier\SyliusGpsrPlugin\Entity\ResponsiblePersonInterface;
use Tmonier\SyliusGpsrPlugin\Fixture\Factory\ProductSafetyExampleFactory;

final class ProductSafetyExampleFactoryTest extends TestCase
{
    public function testItCreatesProductSafetyWithProductOnly(): void
    {
        $product = $this->createMock(ProductInterface::class);
        $productSafety = new ProductSafety();

        /** @var FactoryInterface<ProductSafetyInterface>&\PHPUnit\Framework\MockObject\MockObject $factory */
        $factory = $this->createMock(FactoryInterface::class);
        $factory->expects(self::once())->method('createNew')->willReturn($productSafety);

        /** @var RepositoryInterface<ProductInterface>&\PHPUnit\Framework\MockObject\MockObject $productRepo */
        $productRepo = $this->createMock(RepositoryInterface::class);
        $productRepo->expects(self::once())
            ->method('findOneBy')
            ->with(['code' => 'PROD-001'])
            ->willReturn($product);

        /** @var RepositoryInterface<ManufacturerInterface>&\PHPUnit\Framework\MockObject\MockObject $manufacturerRepo */
        $manufacturerRepo = $this->createMock(RepositoryInterface::class);

        /** @var RepositoryInterface<ResponsiblePersonInterface>&\PHPUnit\Framework\MockObject\MockObject $rpRepo */
        $rpRepo = $this->createMock(RepositoryInterface::class);

        $exampleFactory = new ProductSafetyExampleFactory($factory, $productRepo, $manufacturerRepo, $rpRepo);
        $result = $exampleFactory->create(['product' => 'PROD-001']);

        self::assertSame($product, $result->getProduct());
        self::assertNull($result->getManufacturer());
        self::assertNull($result->getResponsiblePerson());
    }

    public function testItLinksManufacturerAndResponsiblePerson(): void
    {
        $product = $this->createMock(ProductInterface::class);
        $manufacturer = new Manufacturer();
        $rp = new ResponsiblePerson();
        $productSafety = new ProductSafety();

        /** @var FactoryInterface<ProductSafetyInterface>&\PHPUnit\Framework\MockObject\MockObject $factory */
        $factory = $this->createMock(FactoryInterface::class);
        $factory->expects(self::once())->method('createNew')->willReturn($productSafety);

        /** @var RepositoryInterface<ProductInterface>&\PHPUnit\Framework\MockObject\MockObject $productRepo */
        $productRepo = $this->createMock(RepositoryInterface::class);
        $productRepo->method('findOneBy')->with(['code' => 'PROD-001'])->willReturn($product);

        /** @var RepositoryInterface<ManufacturerInterface>&\PHPUnit\Framework\MockObject\MockObject $manufacturerRepo */
        $manufacturerRepo = $this->createMock(RepositoryInterface::class);
        $manufacturerRepo->expects(self::once())
            ->method('findOneBy')
            ->with(['code' => 'MFR-01'])
            ->willReturn($manufacturer);

        /** @var RepositoryInterface<ResponsiblePersonInterface>&\PHPUnit\Framework\MockObject\MockObject $rpRepo */
        $rpRepo = $this->createMock(RepositoryInterface::class);
        $rpRepo->expects(self::once())
            ->method('findOneBy')
            ->with(['code' => 'RP-01'])
            ->willReturn($rp);

        $exampleFactory = new ProductSafetyExampleFactory($factory, $productRepo, $manufacturerRepo, $rpRepo);
        $result = $exampleFactory->create([
            'product' => 'PROD-001',
            'manufacturer' => 'MFR-01',
            'responsible_person' => 'RP-01',
            'type_designation' => 'Model X',
            'other_identifier' => 'EAN-123',
        ]);

        self::assertSame($manufacturer, $result->getManufacturer());
        self::assertSame($rp, $result->getResponsiblePerson());
        self::assertSame('Model X', $result->getTypeDesignation());
        self::assertSame('EAN-123', $result->getOtherIdentifier());
    }

    public function testItThrowsWhenProductNotFound(): void
    {
        /** @var FactoryInterface<ProductSafetyInterface>&\PHPUnit\Framework\MockObject\MockObject $factory */
        $factory = $this->createMock(FactoryInterface::class);

        /** @var RepositoryInterface<ProductInterface>&\PHPUnit\Framework\MockObject\MockObject $productRepo */
        $productRepo = $this->createMock(RepositoryInterface::class);
        $productRepo->method('findOneBy')->willReturn(null);

        /** @var RepositoryInterface<ManufacturerInterface>&\PHPUnit\Framework\MockObject\MockObject $manufacturerRepo */
        $manufacturerRepo = $this->createMock(RepositoryInterface::class);

        /** @var RepositoryInterface<ResponsiblePersonInterface>&\PHPUnit\Framework\MockObject\MockObject $rpRepo */
        $rpRepo = $this->createMock(RepositoryInterface::class);

        $exampleFactory = new ProductSafetyExampleFactory($factory, $productRepo, $manufacturerRepo, $rpRepo);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Product "MISSING" not found.');

        $exampleFactory->create(['product' => 'MISSING']);
    }

    public function testItThrowsWhenManufacturerNotFound(): void
    {
        $product = $this->createMock(ProductInterface::class);
        $productSafety = new ProductSafety();

        /** @var FactoryInterface<ProductSafetyInterface>&\PHPUnit\Framework\MockObject\MockObject $factory */
        $factory = $this->createMock(FactoryInterface::class);
        $factory->method('createNew')->willReturn($productSafety);

        /** @var RepositoryInterface<ProductInterface>&\PHPUnit\Framework\MockObject\MockObject $productRepo */
        $productRepo = $this->createMock(RepositoryInterface::class);
        $productRepo->method('findOneBy')->with(['code' => 'PROD-001'])->willReturn($product);

        /** @var RepositoryInterface<ManufacturerInterface>&\PHPUnit\Framework\MockObject\MockObject $manufacturerRepo */
        $manufacturerRepo = $this->createMock(RepositoryInterface::class);
        $manufacturerRepo->method('findOneBy')->with(['code' => 'BAD'])->willReturn(null);

        /** @var RepositoryInterface<ResponsiblePersonInterface>&\PHPUnit\Framework\MockObject\MockObject $rpRepo */
        $rpRepo = $this->createMock(RepositoryInterface::class);

        $exampleFactory = new ProductSafetyExampleFactory($factory, $productRepo, $manufacturerRepo, $rpRepo);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Manufacturer "BAD" not found.');

        $exampleFactory->create(['product' => 'PROD-001', 'manufacturer' => 'BAD']);
    }

    public function testItThrowsWhenResponsiblePersonNotFound(): void
    {
        $product = $this->createMock(ProductInterface::class);
        $productSafety = new ProductSafety();

        /** @var FactoryInterface<ProductSafetyInterface>&\PHPUnit\Framework\MockObject\MockObject $factory */
        $factory = $this->createMock(FactoryInterface::class);
        $factory->method('createNew')->willReturn($productSafety);

        /** @var RepositoryInterface<ProductInterface>&\PHPUnit\Framework\MockObject\MockObject $productRepo */
        $productRepo = $this->createMock(RepositoryInterface::class);
        $productRepo->method('findOneBy')->with(['code' => 'PROD-001'])->willReturn($product);

        /** @var RepositoryInterface<ManufacturerInterface>&\PHPUnit\Framework\MockObject\MockObject $manufacturerRepo */
        $manufacturerRepo = $this->createMock(RepositoryInterface::class);

        /** @var RepositoryInterface<ResponsiblePersonInterface>&\PHPUnit\Framework\MockObject\MockObject $rpRepo */
        $rpRepo = $this->createMock(RepositoryInterface::class);
        $rpRepo->method('findOneBy')->with(['code' => 'BAD'])->willReturn(null);

        $exampleFactory = new ProductSafetyExampleFactory($factory, $productRepo, $manufacturerRepo, $rpRepo);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Responsible person "BAD" not found.');

        $exampleFactory->create(['product' => 'PROD-001', 'responsible_person' => 'BAD']);
    }
}
