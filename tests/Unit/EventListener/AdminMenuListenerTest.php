<?php

declare(strict_types=1);

namespace Tests\Tmonier\SyliusGpsrPlugin\Unit\EventListener;

use Knp\Menu\ItemInterface;
use PHPUnit\Framework\TestCase;
use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;
use Tmonier\SyliusGpsrPlugin\EventListener\AdminMenuListener;

final class AdminMenuListenerTest extends TestCase
{
    public function testItAddsManufacturersAndResponsiblePersonsMenuItems(): void
    {
        $manufacturersItem = $this->createMock(ItemInterface::class);
        $manufacturersItem->expects(self::once())
            ->method('setLabel')
            ->with('tmonier_gpsr.ui.manufacturers')
            ->willReturn($manufacturersItem);
        $manufacturersItem->expects(self::once())
            ->method('setLabelAttribute')
            ->with('icon', 'tabler:building-factory-2')
            ->willReturn($manufacturersItem);

        $responsiblePersonsItem = $this->createMock(ItemInterface::class);
        $responsiblePersonsItem->expects(self::once())
            ->method('setLabel')
            ->with('tmonier_gpsr.ui.responsible_persons')
            ->willReturn($responsiblePersonsItem);
        $responsiblePersonsItem->expects(self::once())
            ->method('setLabelAttribute')
            ->with('icon', 'tabler:shield-check')
            ->willReturn($responsiblePersonsItem);

        $catalog = $this->createMock(ItemInterface::class);
        $catalog->expects(self::exactly(2))
            ->method('addChild')
            ->willReturnMap([
                ['tmonier_gpsr_manufacturers', ['route' => 'tmonier_gpsr_admin_manufacturer_index'], $manufacturersItem],
                ['tmonier_gpsr_responsible_persons', ['route' => 'tmonier_gpsr_admin_responsible_person_index'], $responsiblePersonsItem],
            ]);

        $menu = $this->createMock(ItemInterface::class);
        $menu->expects(self::once())
            ->method('getChild')
            ->with('catalog')
            ->willReturn($catalog);

        $event = $this->createMock(MenuBuilderEvent::class);
        $event->expects(self::once())
            ->method('getMenu')
            ->willReturn($menu);

        $listener = new AdminMenuListener();
        $listener($event);
    }

    public function testItDoesNothingWhenCatalogMenuIsMissing(): void
    {
        $menu = $this->createMock(ItemInterface::class);
        $menu->expects(self::once())
            ->method('getChild')
            ->with('catalog')
            ->willReturn(null);

        $event = $this->createMock(MenuBuilderEvent::class);
        $event->expects(self::once())
            ->method('getMenu')
            ->willReturn($menu);

        $listener = new AdminMenuListener();
        $listener($event);
    }
}
