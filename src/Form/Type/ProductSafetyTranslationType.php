<?php

declare(strict_types=1);

namespace Tmonier\SyliusGpsrPlugin\Form\Type;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

final class ProductSafetyTranslationType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('warnings', TextareaType::class, [
                'label' => 'tmonier_gpsr.form.product_safety.warnings',
                'help' => 'tmonier_gpsr.form.product_safety.warnings_help',
                'required' => false,
                'attr' => ['rows' => 4],
            ])
            ->add('safetyInformation', TextareaType::class, [
                'label' => 'tmonier_gpsr.form.product_safety.safety_information',
                'help' => 'tmonier_gpsr.form.product_safety.safety_information_help',
                'required' => false,
                'attr' => ['rows' => 4],
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'tmonier_gpsr_product_safety_translation';
    }
}
