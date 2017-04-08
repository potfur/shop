<?php

declare (strict_types = 1);

namespace Dumplie\UserInterafce\Symfony\ShopBundle\Tests\Functional\Customer;

use Dumplie\UserInterafce\Symfony\ShopBundle\Page\Customer\CheckoutPage;

class GoToCheckoutBillingAddressChangeTest extends CustomerTestCase
{
    function test_go_to_new_checkout()
    {
        $cartId = $this->customerContext->createNewCartWithProducts('USD', ['DUMPLIE_SKU']);
        $this->client->getContainer()->get('session')->set('cartId', (string) $cartId);
        $this->customerContext->createNewCheckoutFromCart($cartId);

        (new CheckoutPage($this->client))
            ->open()
            ->followChangeBillingAddressLink()
            ->shouldBeRedirectedTo("/checkout/billing-address");
    }
}
