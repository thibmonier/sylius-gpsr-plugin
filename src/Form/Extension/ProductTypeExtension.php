<?php

declare(strict_types=1);

namespace Tmonier\SyliusGpsrPlugin\Form\Extension;

use Sylius\Bundle\AdminBundle\Form\Type\ProductType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Tmonier\SyliusGpsrPlugin\Form\EventSubscriber\ProductSafetyFormSubscriber;

/**
 * Adds the "Product safety" sub-form to the admin product form (create and update).
 */
final class ProductTypeExtension extends AbstractTypeExtension
{
    public function __construct(private readonly ProductSafetyFormSubscriber $productSafetyFormSubscriber)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventSubscriber($this->productSafetyFormSubscriber);
    }

    public static function getExtendedTypes(): iterable
    {
        return [ProductType::class];
    }
}
