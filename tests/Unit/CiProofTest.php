<?php

declare(strict_types=1);

namespace Tests\Tmonier\SyliusGpsrPlugin\Unit;

use PHPUnit\Framework\TestCase;

/** Throwaway (US-314, T-314-01): a red unit suite, to see the non-unit step still run. Removed by the next commit. */
final class CiProofTest extends TestCase
{
    public function testTheUnitSuiteIsRed(): void
    {
        self::assertSame('red', getenv('US314_PROOF') ?: 'green');
    }
}
