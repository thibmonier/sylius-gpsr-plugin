<?php

declare(strict_types=1);

namespace Tmonier\SyliusGpsrPlugin\Form\Type;

use Sylius\Bundle\ResourceBundle\Form\EventSubscriber\AddCodeFormSubscriber;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Tmonier\SyliusGpsrPlugin\Entity\ResponsiblePersonInterface;

final class ManufacturerType extends AbstractResourceType
{
    /** @param string[] $validationGroups */
    public function __construct(string $dataClass, array $validationGroups, private readonly string $responsiblePersonClass)
    {
        parent::__construct($dataClass, $validationGroups);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->addEventSubscriber(new AddCodeFormSubscriber())
            ->add('name', TextType::class, [
                'label' => 'tmonier_gpsr.form.manufacturer.name',
                'help' => 'tmonier_gpsr.form.manufacturer.name_help',
            ])
            ->add('tradeMark', TextType::class, [
                'label' => 'tmonier_gpsr.form.manufacturer.trade_mark',
                'required' => false,
            ])
        ;

        OperatorAddressFields::add($builder);

        $builder
            ->add('website', UrlType::class, [
                'label' => 'tmonier_gpsr.form.website',
                'required' => false,
                'default_protocol' => 'https',
            ])
            ->add('defaultResponsiblePerson', EntityType::class, [
                'label' => 'tmonier_gpsr.form.manufacturer.default_responsible_person',
                'help' => 'tmonier_gpsr.form.manufacturer.default_responsible_person_help',
                'class' => $this->responsiblePersonClass,
                'choice_label' => static fn (ResponsiblePersonInterface $person): string => sprintf('%s (%s)', $person->getName(), $person->getCode()),
                'required' => false,
                'placeholder' => 'tmonier_gpsr.ui.none',
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'tmonier_gpsr_manufacturer';
    }
}
