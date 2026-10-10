<?php

declare(strict_types=1);

namespace Tmonier\SyliusGpsrPlugin\Fixture;

use Sylius\Bundle\CoreBundle\Fixture\AbstractResourceFixture;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

final class ProductSafetyFixture extends AbstractResourceFixture
{
    public function getName(): string
    {
        return 'tmonier_gpsr_product_safety';
    }

    protected function configureResourceNode(ArrayNodeDefinition $resourceNode): void
    {
        $resourceNode
            ->children()
                ->scalarNode('product')->cannotBeEmpty()->end()
                ->scalarNode('manufacturer')->end()
                ->scalarNode('responsible_person')->end()
                ->scalarNode('type_designation')->end()
                ->scalarNode('other_identifier')->end()
                ->arrayNode('translations')
                    ->useAttributeAsKey('locale')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('warnings')->end()
                            ->scalarNode('safety_information')->end()
                        ->end()
                    ->end()
                ->end()
        ;
    }
}
