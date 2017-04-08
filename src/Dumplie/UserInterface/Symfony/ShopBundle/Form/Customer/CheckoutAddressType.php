<?php

declare (strict_types = 1);

namespace Dumplie\UserInterface\Symfony\ShopBundle\Form\Customer;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

final class CheckoutAddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, [
            'label' => 'customer.checkout.address.form.name.label',
            'constraints' => [
                new NotBlank(),
            ]
        ]);

        $builder->add('street', TextType::class, [
            'label' => 'customer.checkout.address.form.street.label',
            'constraints' => [
                new NotBlank(),
            ]
        ]);

        $builder->add('postCode', TextType::class, [
            'label' => 'customer.checkout.address.form.postCode.label',
            'constraints' => [
                new NotBlank(),
            ]
        ]);

        $builder->add('city', TextType::class, [
            'label' => 'customer.checkout.address.form.city.label',
            'constraints' => [
                new NotBlank(),
            ]
        ]);

        $builder->add('countryCode', CountryType::class, [
            'label' => 'customer.checkout.address.form.countryCode.label',
            'constraints' => [
                new NotBlank(),
            ]
        ]);
    }
}
