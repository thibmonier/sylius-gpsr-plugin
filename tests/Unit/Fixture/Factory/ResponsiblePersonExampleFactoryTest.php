<?php

declare(strict_types=1);

namespace Tests\Tmonier\SyliusGpsrPlugin\Unit\Fixture\Factory;

use PHPUnit\Framework\TestCase;
use Sylius\Resource\Factory\FactoryInterface;
use Tmonier\SyliusGpsrPlugin\Entity\ResponsiblePerson;
use Tmonier\SyliusGpsrPlugin\Entity\ResponsiblePersonInterface;
use Tmonier\SyliusGpsrPlugin\Fixture\Factory\ResponsiblePersonExampleFactory;

final class ResponsiblePersonExampleFactoryTest extends TestCase
{
    private const FULL_OPTIONS = [
        'code' => 'EU_REP_01',
        'name' => 'EU Representative GmbH',
        'role' => ResponsiblePersonInterface::ROLE_IMPORTER,
        'street' => '10 Friedrichstraße',
        'postcode' => '10117',
        'city' => 'Berlin',
        'country_code' => 'DE',
        'email' => 'contact@eurep.de',
        'phone' => '+49 30 123456',
    ];

    public function testItCreatesAResponsiblePersonWithAllOptions(): void
    {
        $rp = new ResponsiblePerson();

        /** @var FactoryInterface<ResponsiblePersonInterface>&\PHPUnit\Framework\MockObject\MockObject $factory */
        $factory = $this->createMock(FactoryInterface::class);
        $factory->expects(self::once())->method('createNew')->willReturn($rp);

        $exampleFactory = new ResponsiblePersonExampleFactory($factory);
        $result = $exampleFactory->create(self::FULL_OPTIONS);

        self::assertSame('EU_REP_01', $result->getCode());
        self::assertSame('EU Representative GmbH', $result->getName());
        self::assertSame(ResponsiblePersonInterface::ROLE_IMPORTER, $result->getRole());
        self::assertSame('10 Friedrichstraße', $result->getStreet());
        self::assertSame('10117', $result->getPostcode());
        self::assertSame('Berlin', $result->getCity());
        self::assertSame('DE', $result->getCountryCode());
        self::assertSame('contact@eurep.de', $result->getEmail());
        self::assertSame('+49 30 123456', $result->getPhone());
    }

    public function testItAcceptsAuthorisedRepresentativeRole(): void
    {
        $rp = new ResponsiblePerson();

        /** @var FactoryInterface<ResponsiblePersonInterface>&\PHPUnit\Framework\MockObject\MockObject $factory */
        $factory = $this->createMock(FactoryInterface::class);
        $factory->expects(self::once())->method('createNew')->willReturn($rp);

        $exampleFactory = new ResponsiblePersonExampleFactory($factory);
        $result = $exampleFactory->create(array_merge(self::FULL_OPTIONS, [
            'role' => ResponsiblePersonInterface::ROLE_AUTHORISED_REPRESENTATIVE,
        ]));

        self::assertSame(ResponsiblePersonInterface::ROLE_AUTHORISED_REPRESENTATIVE, $result->getRole());
    }

    public function testPhoneIsNullWhenExplicitlySetToNull(): void
    {
        $rp = new ResponsiblePerson();

        /** @var FactoryInterface<ResponsiblePersonInterface>&\PHPUnit\Framework\MockObject\MockObject $factory */
        $factory = $this->createMock(FactoryInterface::class);
        $factory->expects(self::once())->method('createNew')->willReturn($rp);

        $exampleFactory = new ResponsiblePersonExampleFactory($factory);
        $result = $exampleFactory->create(array_merge(self::FULL_OPTIONS, ['phone' => null]));

        self::assertNull($result->getPhone());
    }
}
