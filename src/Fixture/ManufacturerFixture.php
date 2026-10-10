<?php

declare(strict_types=1);

namespace Tmonier\SyliusGpsrPlugin\Fixture;

use Sylius\Bundle\CoreBundle\Fixture\AbstractResourceFixture;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

final class ManufacturerFixture extends AbstractResourceFixture
{
    public function getName(): string
    {
        return 'tmonier_gpsr_manufacturer';
    }

    protected function configureResourceNode(ArrayNodeDefinition $resourceNode): void
    {
        $resourceNode
            ->children()
                ->scalarNode('code')->cannotBeEmpty()->end()
                ->scalarNode('name')->cannotBeEmpty()->end()
                ->scalarNode('trade_mark')->end()
                ->scalarNode('street')->cannotBeEmpty()->end()
                ->scalarNode('postcode')->cannotBeEmpty()->end()
                ->scalarNode('city')->cannotBeEmpty()->end()
                ->scalarNode('country_code')->cannotBeEmpty()->end()
                ->scalarNode('email')->cannotBeEmpty()->end()
                ->scalarNode('website')->end()
                ->scalarNode('default_responsible_person')->end()
        ;
    }
}
