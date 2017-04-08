<?php

declare (strict_types = 1);

namespace Dumplie\UserInterafce\Symfony\ShopBundle\Tests\Functional\Customer;

use Dumplie\UserInterafce\Symfony\ShopBundle\Page\Customer\CheckoutBillingAddressChangePage;

class ChangeCheckoutBillingAddressTest extends CustomerTestCase
{
    function test_change_checkout_billing_address()
    {
        $cartId = $this->customerContext->createNewCartWithProducts('USD', ['DUMPLIE_SKU']);
        $this->client->getContainer()->get('session')->set('cartId', (string)$cartId);
        $this->customerContext->createNewCheckoutFromCart($cartId);

        (new CheckoutBillingAddressChangePage($this->client))
            ->open()
            ->fillForm(
                'Sam Drabulock',
                'Different place',
                '10-40',
                'London'
            )
            ->pressSaveButton()
            ->shouldBeRedirectedTo("/checkout");

        $checkout = $this->checkoutQuery()->getById((string) $cartId);

        $this->assertEquals(
            (string) $checkout->billingAddress(),
            'Sam Drabulock, 10-40 London, Different place, GB'
        );
    }
}
