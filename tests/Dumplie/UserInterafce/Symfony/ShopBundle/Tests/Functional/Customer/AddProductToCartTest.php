<?php

declare (strict_types = 1);

namespace Dumplie\UserInterafce\Symfony\ShopBundle\Tests\Functional\Customer;

use Dumplie\UserInterafce\Symfony\ShopBundle\Page\Customer\ProductDetailsPage;

class AddProductToCartTest extends CustomerTestCase
{
    function test_successful_addition_to_cart()
    {
        (new ProductDetailsPage($this->client))
            ->open()
            ->fillForm("DUMPLIE_SKU", 2)
            ->pressAddButton()
            ->shouldBeRedirectedTo("/cart");

        $cartId = $this->client->getContainer()->get('session')->get('cartId');
        $totalQuantity = $this->cartQuery()->getById($cartId)->totalQuantity();

        $this->assertTrue($totalQuantity == 2);
    }
}
