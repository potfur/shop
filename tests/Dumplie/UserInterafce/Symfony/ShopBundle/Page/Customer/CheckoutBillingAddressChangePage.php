<?php
declare (strict_types = 1);

namespace Dumplie\UserInterafce\Symfony\ShopBundle\Page\Customer;

class CheckoutBillingAddressChangePage extends AddressChangePage
{
    public function getUrl(): string
    {
        return '/checkout/billing-address';
    }
}
