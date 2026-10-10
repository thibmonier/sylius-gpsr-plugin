<?php

declare(strict_types=1);

namespace Tmonier\SyliusGpsrPlugin\Model;

/**
 * The 27 Member States of the European Union (ISO 3166-1 alpha-2).
 *
 * EEA EFTA states (Iceland, Liechtenstein, Norway) apply the GPSR through the EEA Agreement but
 * are deliberately not listed: the plugin only treats EU-27 manufacturers as "established in the
 * Union". Northern Ireland ("XI") is not an ISO code and is not covered either.
 */
final class EuMemberStates
{
    public const CODES = [
        'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR', 'GR', 'HR', 'HU',
        'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK',
    ];

    private function __construct()
    {
    }

    public static function contains(?string $countryCode): bool
    {
        return null !== $countryCode && in_array(strtoupper($countryCode), self::CODES, true);
    }
}
