<?php

declare(strict_types=1);

namespace Tests\Tmonier\SyliusGpsrPlugin\Unit\Fixture\Factory;

use PHPUnit\Framework\TestCase;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Sylius\Resource\Factory\FactoryInterface;
use Tmonier\SyliusGpsrPlugin\Entity\Manufacturer;
use Tmonier\SyliusGpsrPlugin\Entity\ManufacturerInterface;
use Tmonier\SyliusGpsrPlugin\Entity\ResponsiblePerson;
use Tmonier\SyliusGpsrPlugin\Entity\ResponsiblePersonInterface;
use Tmonier\SyliusGpsrPlugin\Fixture\Factory\ManufacturerExampleFactory;

final class ManufacturerExampleFactoryTest extends TestCase
{
    private const FULL_OPTIONS = [
        'code' => 'ACME',
        'name' => 'Acme Corp',
        'trade_mark' => 'Acme™',
        'street' => '1 Rue de Paris',
        'postcode' => '75001',
        'city' => 'Paris',
        'country_code' => 'FR',
        'email' => 'contact@acme.com',
        'website' => 'https://acme.com',
    ];

    public function testItCreatesAManufacturerWithAllOptions(): void
    {
        $manufacturer = new Manufacturer();

        /** @var FactoryInterface<ManufacturerInterface>&\PHPUnit\Framework\MockObject\MockObject $factory */
        $factory = $this->createMock(FactoryInterface::class);
        $factory->expects(self::once())->method('createNew')->willReturn($manufacturer);

        /** @var RepositoryInterface<ResponsiblePersonInterface>&\PHPUnit\Framework\MockObject\MockObject $repo */
        $repo = $this->createMock(RepositoryInterface::class);

        $exampleFactory = new ManufacturerExampleFactory($factory, $repo);
        $result = $exampleFactory->create(self::FULL_OPTIONS);

        self::assertSame('ACME', $result->getCode());
        self::assertSame('Acme Corp', $result->getName());
        self::assertSame('Acme™', $result->getTradeMark());
        self::assertSame('1 Rue de Paris', $result->getStreet());
        self::assertSame('75001', $result->getPostcode());
        self::assertSame('Paris', $result->getCity());
        self::assertSame('FR', $result->getCountryCode());
        self::assertSame('contact@acme.com', $result->getEmail());
        self::assertSame('https://acme.com', $result->getWebsite());
    }

    public function testItLinksDefaultResponsiblePerson(): void
    {
        $manufacturer = new Manufacturer();
        $responsiblePerson = new ResponsiblePerson();

        /** @var FactoryInterface<ManufacturerInterface>&\PHPUnit\Framework\MockObject\MockObject $factory */
        $factory = $this->createMock(FactoryInterface::class);
        $factory->expects(self::once())->method('createNew')->willReturn($manufacturer);

        /** @var RepositoryInterface<ResponsiblePersonInterface>&\PHPUnit\Framework\MockObject\MockObject $repo */
        $repo = $this->createMock(RepositoryInterface::class);
        $repo->expects(self::once())
            ->method('findOneBy')
            ->with(['code' => 'EU_REP'])
            ->willReturn($responsiblePerson);

        $exampleFactory = new ManufacturerExampleFactory($factory, $repo);
        $result = $exampleFactory->create(array_merge(self::FULL_OPTIONS, ['default_responsible_person' => 'EU_REP']));

        self::assertSame($responsiblePerson, $result->getDefaultResponsiblePerson());
    }

    public function testItThrowsWhenResponsiblePersonNotFound(): void
    {
        $manufacturer = new Manufacturer();

        /** @var FactoryInterface<ManufacturerInterface>&\PHPUnit\Framework\MockObject\MockObject $factory */
        $factory = $this->createMock(FactoryInterface::class);
        $factory->expects(self::once())->method('createNew')->willReturn($manufacturer);

        /** @var RepositoryInterface<ResponsiblePersonInterface>&\PHPUnit\Framework\MockObject\MockObject $repo */
        $repo = $this->createMock(RepositoryInterface::class);
        $repo->expects(self::once())
            ->method('findOneBy')
            ->with(['code' => 'MISSING'])
            ->willReturn(null);

        $exampleFactory = new ManufacturerExampleFactory($factory, $repo);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Responsible person "MISSING" not found.');

        $exampleFactory->create(array_merge(self::FULL_OPTIONS, ['default_responsible_person' => 'MISSING']));
    }

    public function testNullableFieldsDefaultToNull(): void
    {
        $manufacturer = new Manufacturer();

        /** @var FactoryInterface<ManufacturerInterface>&\PHPUnit\Framework\MockObject\MockObject $factory */
        $factory = $this->createMock(FactoryInterface::class);
        $factory->expects(self::once())->method('createNew')->willReturn($manufacturer);

        /** @var RepositoryInterface<ResponsiblePersonInterface>&\PHPUnit\Framework\MockObject\MockObject $repo */
        $repo = $this->createMock(RepositoryInterface::class);

        $options = self::FULL_OPTIONS;
        unset($options['trade_mark'], $options['website']);
        $options['trade_mark'] = null;
        $options['website'] = null;

        $exampleFactory = new ManufacturerExampleFactory($factory, $repo);
        $result = $exampleFactory->create($options);

        self::assertNull($result->getTradeMark());
        self::assertNull($result->getWebsite());
        self::assertNull($result->getDefaultResponsiblePerson());
    }
}
