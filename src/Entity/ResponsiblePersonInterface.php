<?php

declare(strict_types=1);

namespace Tmonier\SyliusGpsrPlugin\Entity;

/**
 * Economic operator established in the Union responsible for a product (Art. 16 GPSR,
 * Art. 4(1) of Regulation (EU) 2019/1020).
 */
interface ResponsiblePersonInterface extends AddressableInterface
{
    public const ROLE_IMPORTER = 'importer';

    public const ROLE_AUTHORISED_REPRESENTATIVE = 'authorised_representative';

    public const ROLE_FULFILMENT_SERVICE_PROVIDER = 'fulfilment_service_provider';

    public const ROLES = [
        self::ROLE_IMPORTER,
        self::ROLE_AUTHORISED_REPRESENTATIVE,
        self::ROLE_FULFILMENT_SERVICE_PROVIDER,
    ];

    public function getRole(): ?string;

    public function setRole(?string $role): void;

    public function getPhone(): ?string;

    public function setPhone(?string $phone): void;
}
