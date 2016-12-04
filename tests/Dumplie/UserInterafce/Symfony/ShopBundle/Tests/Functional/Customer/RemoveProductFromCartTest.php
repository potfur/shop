<?php

declare (strict_types = 1);

namespace Dumplie\UserInterafce\Symfony\ShopBundle\Tests\Functional\Customer;

use Dumplie\UserInterafce\Symfony\ShopBundle\Page\Customer\CartPage;

class RemoveProductFromCartTest extends CustomerTestCase
{
    function test_successful_remove_from_cart_cart()
    {
        $cartId = $this->customerContext->createNewCartWithProducts('USD', ['DUMPLIE_SKU']);
        $this->client->getContainer()->get('session')->set('cartId', (string) $cartId);

        (new CartPage($this->client))
            ->open()
            ->pressRemoveProductButton("DUMPLIE_SKU")
            ->shouldBeRedirectedTo("/cart");

        $cartId = $this->client->getContainer()->get('session')->get('cartId');
        $totalQuantity = $this->query()->getById($cartId)->totalQuantity();

        $this->assertTrue($totalQuantity == 0);
    }
}
