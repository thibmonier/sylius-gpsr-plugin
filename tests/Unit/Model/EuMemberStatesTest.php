<?php

declare(strict_types=1);

namespace Tests\Tmonier\SyliusGpsrPlugin\Unit\Model;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Intl\Countries;
use Tmonier\SyliusGpsrPlugin\Model\EuMemberStates;

final class EuMemberStatesTest extends TestCase
{
    public function testItListsTheTwentySevenMemberStates(): void
    {
        self::assertCount(27, EuMemberStates::CODES);
        self::assertSame(EuMemberStates::CODES, array_values(array_unique(EuMemberStates::CODES)));

        foreach (EuMemberStates::CODES as $code) {
            self::assertTrue(Countries::exists($code), $code);
        }
    }

    /** @return iterable<string, array{string|null, bool}> */
    public static function countries(): iterable
    {
        yield 'France' => ['FR', true];
        yield 'Greece uses its ISO code' => ['GR', true];
        yield 'lower case' => ['de', true];
        yield 'United Kingdom' => ['GB', false];
        yield 'Norway (EEA, not EU)' => ['NO', false];
        yield 'Switzerland' => ['CH', false];
        yield 'China' => ['CN', false];
        yield 'United States' => ['US', false];
        yield 'none' => [null, false];
    }

    #[DataProvider('countries')]
    public function testItTellsWhetherACountryIsAMemberState(?string $code, bool $expected): void
    {
        self::assertSame($expected, EuMemberStates::contains($code));
    }
}
