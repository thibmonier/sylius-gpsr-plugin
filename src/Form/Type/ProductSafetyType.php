<?php

declare(strict_types=1);

namespace Tmonier\SyliusGpsrPlugin\Form\Type;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Sylius\Bundle\ResourceBundle\Form\Type\ResourceTranslationsType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Tmonier\SyliusGpsrPlugin\Entity\ManufacturerInterface;
use Tmonier\SyliusGpsrPlugin\Entity\ResponsiblePersonInterface;

final class ProductSafetyType extends AbstractResourceType
{
    /** @param string[] $validationGroups */
    public function __construct(
        string $dataClass,
        array $validationGroups,
        private readonly string $manufacturerClass,
        private readonly string $responsiblePersonClass,
    ) {
        parent::__construct($dataClass, $validationGroups);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('manufacturer', EntityType::class, [
                'label' => 'tmonier_gpsr.form.product_safety.manufacturer',
                'class' => $this->manufacturerClass,
                'choice_label' => static fn (ManufacturerInterface $manufacturer): string => sprintf('%s (%s)', $manufacturer->getName(), $manufacturer->getCode()),
                'required' => false,
                'placeholder' => 'tmonier_gpsr.ui.none',
            ])
            ->add('responsiblePerson', EntityType::class, [
                'label' => 'tmonier_gpsr.form.product_safety.responsible_person',
                'help' => 'tmonier_gpsr.form.product_safety.responsible_person_help',
                'class' => $this->responsiblePersonClass,
                'choice_label' => static fn (ResponsiblePersonInterface $person): string => sprintf('%s (%s)', $person->getName(), $person->getCode()),
                'required' => false,
                'placeholder' => 'tmonier_gpsr.form.product_safety.manufacturer_default',
            ])
            ->add('typeDesignation', TextType::class, [
                'label' => 'tmonier_gpsr.form.product_safety.type_designation',
                'help' => 'tmonier_gpsr.form.product_safety.type_designation_help',
                'required' => false,
            ])
            ->add('otherIdentifier', TextType::class, [
                'label' => 'tmonier_gpsr.form.product_safety.other_identifier',
                'help' => 'tmonier_gpsr.form.product_safety.other_identifier_help',
                'required' => false,
            ])
            ->add('translations', ResourceTranslationsType::class, [
                'label' => 'sylius.ui.translations',
                'entry_type' => ProductSafetyTranslationType::class,
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'tmonier_gpsr_product_safety';
    }
}
