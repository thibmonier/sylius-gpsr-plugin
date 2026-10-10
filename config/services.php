<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sylius\Bundle\GridBundle\Form\Type\Filter\BooleanFilterType;
use Tmonier\SyliusGpsrPlugin\EventListener\AdminMenuListener;
use Tmonier\SyliusGpsrPlugin\Fixture\Factory\ManufacturerExampleFactory;
use Tmonier\SyliusGpsrPlugin\Fixture\Factory\ProductSafetyExampleFactory;
use Tmonier\SyliusGpsrPlugin\Fixture\Factory\ResponsiblePersonExampleFactory;
use Tmonier\SyliusGpsrPlugin\Fixture\ManufacturerFixture;
use Tmonier\SyliusGpsrPlugin\Fixture\ProductSafetyFixture;
use Tmonier\SyliusGpsrPlugin\Fixture\ResponsiblePersonFixture;
use Tmonier\SyliusGpsrPlugin\Form\EventSubscriber\ProductSafetyFormSubscriber;
use Tmonier\SyliusGpsrPlugin\Form\Extension\ProductTypeExtension;
use Tmonier\SyliusGpsrPlugin\Form\Type\ManufacturerType;
use Tmonier\SyliusGpsrPlugin\Form\Type\ProductSafetyTranslationType;
use Tmonier\SyliusGpsrPlugin\Form\Type\ProductSafetyType;
use Tmonier\SyliusGpsrPlugin\Form\Type\ResponsiblePersonType;
use Tmonier\SyliusGpsrPlugin\Grid\Filter\GpsrDataMissingFilter;
use Tmonier\SyliusGpsrPlugin\Resolver\ProductSafetyResolver;
use Tmonier\SyliusGpsrPlugin\Resolver\ProductSafetyResolverInterface;
use Tmonier\SyliusGpsrPlugin\Twig\ProductSafetyExtension;

return static function (ContainerConfigurator $container): void {
    $parameters = $container->parameters();
    $parameters->set('tmonier_gpsr.form.validation_groups', ['sylius']);

    $services = $container->services();

    // Forms
    $services
        ->set('tmonier_gpsr.form.type.manufacturer', ManufacturerType::class)
        ->args([
            param('tmonier_gpsr.model.manufacturer.class'),
            param('tmonier_gpsr.form.validation_groups'),
            param('tmonier_gpsr.model.responsible_person.class'),
        ])
        ->tag('form.type')
    ;

    $services
        ->set('tmonier_gpsr.form.type.responsible_person', ResponsiblePersonType::class)
        ->args([
            param('tmonier_gpsr.model.responsible_person.class'),
            param('tmonier_gpsr.form.validation_groups'),
        ])
        ->tag('form.type')
    ;

    $services
        ->set('tmonier_gpsr.form.type.product_safety', ProductSafetyType::class)
        ->args([
            param('tmonier_gpsr.model.product_safety.class'),
            param('tmonier_gpsr.form.validation_groups'),
            param('tmonier_gpsr.model.manufacturer.class'),
            param('tmonier_gpsr.model.responsible_person.class'),
        ])
        ->tag('form.type')
    ;

    $services
        ->set('tmonier_gpsr.form.type.product_safety_translation', ProductSafetyTranslationType::class)
        ->args([
            param('tmonier_gpsr.model.product_safety_translation.class'),
            param('tmonier_gpsr.form.validation_groups'),
        ])
        ->tag('form.type')
    ;

    $services
        ->set('tmonier_gpsr.form.event_subscriber.product_safety', ProductSafetyFormSubscriber::class)
        ->args([
            service('tmonier_gpsr.repository.product_safety'),
            service('tmonier_gpsr.factory.product_safety'),
            service('tmonier_gpsr.manager.product_safety'),
            ProductSafetyType::class,
        ])
    ;

    $services
        ->set('tmonier_gpsr.form.extension.product', ProductTypeExtension::class)
        ->args([service('tmonier_gpsr.form.event_subscriber.product_safety')])
        ->tag('form.type_extension')
    ;

    // Read side
    $services
        ->set('tmonier_gpsr.resolver.product_safety', ProductSafetyResolver::class)
        ->args([
            service('tmonier_gpsr.repository.product_safety'),
            service('sylius.context.locale'),
            service('sylius.translation_locale_provider'),
        ])
    ;
    $services->alias(ProductSafetyResolverInterface::class, 'tmonier_gpsr.resolver.product_safety');

    $services
        ->set('tmonier_gpsr.twig.extension.product_safety', ProductSafetyExtension::class)
        ->args([service('tmonier_gpsr.resolver.product_safety')])
        ->tag('twig.extension')
    ;

    // Admin
    $services
        ->set('tmonier_gpsr.grid.filter.data_missing', GpsrDataMissingFilter::class)
        ->args([param('tmonier_gpsr.model.product_safety.class')])
        ->tag('sylius.grid_filter', [
            'type' => GpsrDataMissingFilter::TYPE,
            'form_type' => BooleanFilterType::class,
            'template' => '@TmonierSyliusGpsrPlugin/admin/grid/filter/data_missing.html.twig',
        ])
    ;

    $services
        ->set('tmonier_gpsr.listener.admin_menu', AdminMenuListener::class)
        ->tag('kernel.event_listener', ['event' => 'sylius.menu.admin.main'])
    ;

    // Fixtures
    $services
        ->set('tmonier_gpsr.fixture.example_factory.responsible_person', ResponsiblePersonExampleFactory::class)
        ->args([service('tmonier_gpsr.factory.responsible_person')])
    ;
    $services
        ->set('tmonier_gpsr.fixture.example_factory.manufacturer', ManufacturerExampleFactory::class)
        ->args([
            service('tmonier_gpsr.factory.manufacturer'),
            service('tmonier_gpsr.repository.responsible_person'),
        ])
    ;
    $services
        ->set('tmonier_gpsr.fixture.example_factory.product_safety', ProductSafetyExampleFactory::class)
        ->args([
            service('tmonier_gpsr.factory.product_safety'),
            service('sylius.repository.product'),
            service('tmonier_gpsr.repository.manufacturer'),
            service('tmonier_gpsr.repository.responsible_person'),
        ])
    ;

    $services
        ->set('tmonier_gpsr.fixture.responsible_person', ResponsiblePersonFixture::class)
        ->args([
            service('tmonier_gpsr.manager.responsible_person'),
            service('tmonier_gpsr.fixture.example_factory.responsible_person'),
        ])
        ->tag('sylius_fixtures.fixture')
    ;
    $services
        ->set('tmonier_gpsr.fixture.manufacturer', ManufacturerFixture::class)
        ->args([
            service('tmonier_gpsr.manager.manufacturer'),
            service('tmonier_gpsr.fixture.example_factory.manufacturer'),
        ])
        ->tag('sylius_fixtures.fixture')
    ;
    $services
        ->set('tmonier_gpsr.fixture.product_safety', ProductSafetyFixture::class)
        ->args([
            service('tmonier_gpsr.manager.product_safety'),
            service('tmonier_gpsr.fixture.example_factory.product_safety'),
        ])
        ->tag('sylius_fixtures.fixture')
    ;
};
