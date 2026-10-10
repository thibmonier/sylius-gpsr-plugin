<?php

declare(strict_types=1);

namespace Tmonier\SyliusGpsrPlugin\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Postal and electronic address shared by manufacturers and responsible persons.
 */
final class OperatorAddressFields
{
    private function __construct()
    {
    }

    public static function add(FormBuilderInterface $builder): void
    {
        $builder
            ->add('street', TextType::class, ['label' => 'sylius.form.address.street'])
            ->add('postcode', TextType::class, ['label' => 'sylius.form.address.postcode'])
            ->add('city', TextType::class, ['label' => 'sylius.form.address.city'])
            ->add('countryCode', CountryType::class, [
                'label' => 'sylius.form.address.country',
                'placeholder' => 'sylius.form.country.select',
            ])
            ->add('email', EmailType::class, [
                'label' => 'tmonier_gpsr.form.email',
                'help' => 'tmonier_gpsr.form.email_help',
            ])
        ;
    }
}
