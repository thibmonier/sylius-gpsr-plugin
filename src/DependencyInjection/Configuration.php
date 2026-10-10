<?php

declare(strict_types=1);

namespace Tmonier\SyliusGpsrPlugin\DependencyInjection;

use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Sylius\Bundle\ResourceBundle\SyliusResourceBundle;
use Sylius\Resource\Factory\Factory;
use Sylius\Resource\Factory\TranslatableFactory;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Tmonier\SyliusGpsrPlugin\Entity\Manufacturer;
use Tmonier\SyliusGpsrPlugin\Entity\ManufacturerInterface;
use Tmonier\SyliusGpsrPlugin\Entity\ProductSafety;
use Tmonier\SyliusGpsrPlugin\Entity\ProductSafetyInterface;
use Tmonier\SyliusGpsrPlugin\Entity\ProductSafetyTranslation;
use Tmonier\SyliusGpsrPlugin\Entity\ProductSafetyTranslationInterface;
use Tmonier\SyliusGpsrPlugin\Entity\ResponsiblePerson;
use Tmonier\SyliusGpsrPlugin\Entity\ResponsiblePersonInterface;
use Tmonier\SyliusGpsrPlugin\Form\Type\ManufacturerType;
use Tmonier\SyliusGpsrPlugin\Form\Type\ProductSafetyTranslationType;
use Tmonier\SyliusGpsrPlugin\Form\Type\ProductSafetyType;
use Tmonier\SyliusGpsrPlugin\Form\Type\ResponsiblePersonType;
use Tmonier\SyliusGpsrPlugin\Repository\ProductSafetyRepository;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('tmonier_sylius_gpsr');
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('driver')->defaultValue(SyliusResourceBundle::DRIVER_DOCTRINE_ORM)->end()
                ->arrayNode('resources')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->append($this->resourceNode('manufacturer', Manufacturer::class, ManufacturerInterface::class, Factory::class, ManufacturerType::class))
                        ->append($this->resourceNode('responsible_person', ResponsiblePerson::class, ResponsiblePersonInterface::class, Factory::class, ResponsiblePersonType::class))
                        ->append(
                            $this->resourceNode('product_safety', ProductSafety::class, ProductSafetyInterface::class, TranslatableFactory::class, ProductSafetyType::class, ProductSafetyRepository::class)
                                ->append($this->resourceNode('translation', ProductSafetyTranslation::class, ProductSafetyTranslationInterface::class, Factory::class, ProductSafetyTranslationType::class)),
                        )
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }

    private function resourceNode(
        string $name,
        string $model,
        string $interface,
        string $factory,
        string $form,
        ?string $repository = null,
    ): ArrayNodeDefinition {
        $node = new ArrayNodeDefinition($name);
        $classes = $node
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('classes')
                    ->addDefaultsIfNotSet()
                    ->children()
        ;

        $classes
            ->scalarNode('model')->defaultValue($model)->cannotBeEmpty()->end()
            ->scalarNode('interface')->defaultValue($interface)->cannotBeEmpty()->end()
            ->scalarNode('controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
            ->scalarNode('factory')->defaultValue($factory)->end()
            ->scalarNode('form')->defaultValue($form)->cannotBeEmpty()->end()
        ;

        $repositoryNode = $classes->scalarNode('repository');
        if (null !== $repository) {
            $repositoryNode->defaultValue($repository);
        }
        $repositoryNode->cannotBeEmpty();

        return $node;
    }
}
