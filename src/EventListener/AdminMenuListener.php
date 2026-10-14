<?php

declare(strict_types=1);

namespace Tmonier\SyliusGpsrPlugin\EventListener;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class AdminMenuListener
{
    public function __invoke(MenuBuilderEvent $event): void
    {
        $catalog = $event->getMenu()->getChild('catalog');
        if (!$catalog instanceof \Knp\Menu\ItemInterface) {
            return;
        }

        $catalog
            ->addChild('tmonier_gpsr_manufacturers', ['route' => 'tmonier_gpsr_admin_manufacturer_index'])
            ->setLabel('tmonier_gpsr.ui.manufacturers')
            ->setLabelAttribute('icon', 'tabler:building-factory-2')
        ;

        $catalog
            ->addChild('tmonier_gpsr_responsible_persons', ['route' => 'tmonier_gpsr_admin_responsible_person_index'])
            ->setLabel('tmonier_gpsr.ui.responsible_persons')
            ->setLabelAttribute('icon', 'tabler:shield-check')
        ;
    }
}
