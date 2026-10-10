<?php

declare(strict_types=1);

namespace Tmonier\SyliusGpsrPlugin\Grid\Filter;

use Sylius\Bundle\GridBundle\Doctrine\ORM\DataSource;
use Sylius\Component\Grid\Data\DataSourceInterface;
use Sylius\Component\Grid\Filter\BooleanFilter;
use Sylius\Component\Grid\Filtering\FilterInterface;
use Tmonier\SyliusGpsrPlugin\Model\EuMemberStates;

/**
 * Products grid filter: "yes" keeps the products whose GPSR data is incomplete, "no" the
 * complete ones. Complete means a manufacturer is set and, when the manufacturer is established
 * outside the Union, a responsible person is resolved (product override or manufacturer default).
 *
 * Only works with the Doctrine ORM grid driver; other data sources are left untouched.
 */
final class GpsrDataMissingFilter implements FilterInterface
{
    public const TYPE = 'tmonier_gpsr_data_missing';

    private const ALIAS = 'tmonier_gpsr_ps';

    public function __construct(private readonly string $productSafetyClass)
    {
    }

    public function apply(DataSourceInterface $dataSource, string $name, $data, array $options): void
    {
        if (!$dataSource instanceof DataSource || !in_array($data, [BooleanFilter::TRUE, BooleanFilter::FALSE], true)) {
            return;
        }

        $queryBuilder = $dataSource->getQueryBuilder();
        $rootAlias = $queryBuilder->getRootAliases()[0];

        $complete = sprintf(
            'EXISTS (SELECT %1$s.id FROM %2$s %1$s INNER JOIN %1$s.manufacturer %1$s_m LEFT JOIN %1$s_m.defaultResponsiblePerson %1$s_drp ' .
            'WHERE %1$s.product = %3$s AND (%1$s_m.countryCode IN (:%1$s_eu) OR %1$s.responsiblePerson IS NOT NULL OR %1$s_drp.id IS NOT NULL))',
            self::ALIAS,
            $this->productSafetyClass,
            $rootAlias,
        );

        $dataSource->restrict(BooleanFilter::TRUE === $data ? sprintf('NOT %s', $complete) : $complete);
        $queryBuilder->setParameter(self::ALIAS . '_eu', EuMemberStates::CODES);
    }
}
