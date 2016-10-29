<?php

declare (strict_types = 1);

namespace Dumplie\UserInterface\Symfony\ShopBundle\Form\Customer;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

final class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('sku', HiddenType::class, [
            'constraints' => [
                new NotBlank(),
            ]
        ]);
        $builder->add('quantity', IntegerType::class, [
            'label' => 'product.cart.form.quantity.label',
            'data' => 1,
            'constraints' => [
                new GreaterThanOrEqual(['value' => 1]),
            ]
        ]);
    }
}
