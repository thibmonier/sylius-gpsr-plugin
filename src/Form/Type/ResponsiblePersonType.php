<?php

declare(strict_types=1);

namespace Tmonier\SyliusGpsrPlugin\Form\Type;

use Sylius\Bundle\ResourceBundle\Form\EventSubscriber\AddCodeFormSubscriber;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Tmonier\SyliusGpsrPlugin\Entity\ResponsiblePersonInterface;

final class ResponsiblePersonType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->addEventSubscriber(new AddCodeFormSubscriber())
            ->add('name', TextType::class, ['label' => 'tmonier_gpsr.form.responsible_person.name'])
            ->add('role', ChoiceType::class, [
                'label' => 'tmonier_gpsr.form.responsible_person.role',
                'choices' => array_combine(
                    array_map(static fn (string $role): string => 'tmonier_gpsr.ui.role.' . $role, ResponsiblePersonInterface::ROLES),
                    ResponsiblePersonInterface::ROLES,
                ),
                'placeholder' => 'tmonier_gpsr.form.responsible_person.select_role',
            ])
        ;

        OperatorAddressFields::add($builder);

        $builder->add('phone', TelType::class, [
            'label' => 'sylius.form.address.phone_number',
            'required' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'tmonier_gpsr_responsible_person';
    }
}
