<?php

declare (strict_types = 1);

namespace Dumplie\UserInterafce\Symfony\ShopBundle\Tests\Functional\Customer;

use Dumplie\UserInterafce\Symfony\ShopBundle\Page\Customer\NewCheckoutPage;

class FillNewCheckoutTest extends CustomerTestCase
{
    function test_fill_new_checkout()
    {
        $cartId = $this->customerContext->createNewCartWithProducts('USD', ['DUMPLIE_SKU']);
        $this->client->getContainer()->get('session')->set('cartId', (string)$cartId);

        (new NewCheckoutPage($this->client))
            ->open()
            ->fillForm(
                'Joe Dean Anderson',
                'Street Avenue',
                '10-10',
                'Somewhereshire',
                'GB'
            )
            ->pressSaveButton()
            ->shouldBeRedirectedTo("/checkout");

        $checkout = $this->checkoutQuery()->getById((string) $cartId);

        $this->assertEquals(
            (string) $checkout->billingAddress(),
            (string) $checkout->shippingAddress()
        );

        $this->assertEquals(
            (string) $checkout->billingAddress(),
            'Joe Dean Anderson, 10-10 Somewhereshire, Street Avenue, GB'
        );
    }
}
