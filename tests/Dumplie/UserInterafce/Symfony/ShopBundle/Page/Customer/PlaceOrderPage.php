<?php
declare (strict_types = 1);

namespace Dumplie\UserInterafce\Symfony\ShopBundle\Page\Customer;

use Dumplie\UserInterafce\Symfony\ShopBundle\Page\BasePage;

class PlaceOrderPage extends BasePage
{
    public function getUrl(): string
    {
        return '/checkout/place';
    }
}
