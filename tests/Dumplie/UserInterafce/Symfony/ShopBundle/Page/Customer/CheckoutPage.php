<?php
declare (strict_types = 1);

namespace Dumplie\UserInterafce\Symfony\ShopBundle\Page\Customer;

use Dumplie\UserInterafce\Symfony\ShopBundle\Page\BasePage;

class CheckoutPage extends BasePage
{
    public function getUrl(): string
    {
        return '/checkout';
    }

    public function followChangeBillingAddressLink(): CheckoutBillingAddressChangePage
    {
        $link = $this->getCrawler()->filter("a[href$='checkout/billing-address']")->link();
        $this->client->click($link);

        return new CheckoutBillingAddressChangePage($this->client, $this);
    }

    public function followChangeShippingAddressLink(): CheckoutShippingAddressChangePage
    {
        $link = $this->getCrawler()->filter("a[href$='checkout/shipping-address']")->link();
        $this->client->click($link);

        return new CheckoutShippingAddressChangePage($this->client, $this);
    }

    public function followPlaceOrderLink(): PlaceOrderPage
    {
        $link = $this->getCrawler()->filter("a[href$='checkout/place']")->link();
        $this->client->click($link);

        return new PlaceOrderPage($this->client, $this);
    }

}
