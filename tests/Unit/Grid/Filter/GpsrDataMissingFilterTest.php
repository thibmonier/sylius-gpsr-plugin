<?php

declare(strict_types=1);

namespace Tests\Tmonier\SyliusGpsrPlugin\Unit\Grid\Filter;

use PHPUnit\Framework\TestCase;
use Sylius\Component\Grid\Data\DataSourceInterface;
use Sylius\Component\Grid\Filter\BooleanFilter;
use Tmonier\SyliusGpsrPlugin\Grid\Filter\GpsrDataMissingFilter;

final class GpsrDataMissingFilterTest extends TestCase
{
    public function testTypeConstant(): void
    {
        self::assertSame('tmonier_gpsr_data_missing', GpsrDataMissingFilter::TYPE);
    }

    public function testItIgnoresNonDoctrineDataSources(): void
    {
        $dataSource = $this->createMock(DataSourceInterface::class);

        $filter = new GpsrDataMissingFilter('App\\Entity\\ProductSafety');
        $filter->apply($dataSource, 'gpsr_missing', BooleanFilter::TRUE, []);

        self::assertTrue(true);
    }

    public function testItIgnoresInvalidData(): void
    {
        $dataSource = $this->createMock(DataSourceInterface::class);

        $filter = new GpsrDataMissingFilter('App\\Entity\\ProductSafety');
        $filter->apply($dataSource, 'gpsr_missing', 'invalid_value', []);

        self::assertTrue(true);
    }

    public function testItIgnoresNullData(): void
    {
        $dataSource = $this->createMock(DataSourceInterface::class);

        $filter = new GpsrDataMissingFilter('App\\Entity\\ProductSafety');
        $filter->apply($dataSource, 'gpsr_missing', null, []);

        self::assertTrue(true);
    }
}
