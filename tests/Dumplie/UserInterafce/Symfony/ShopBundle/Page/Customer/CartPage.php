<?php
declare (strict_types = 1);

namespace Dumplie\UserInterafce\Symfony\ShopBundle\Page\Customer;

use Dumplie\UserInterafce\Symfony\ShopBundle\Page\BasePage;

class CartPage extends BasePage
{
    public function getUrl(): string
    {
        return '/cart';
    }

    public function pressRemoveProductButton(string $sku): CartPage
    {
        $form = $this->getCrawler()->filter("form[name=\"cart_item\"]")->form(
            [
                'cart_item[sku]' => $sku,
            ],
            'POST'
        );

        $this->client->submit($form);

        $status = $this->client->getResponse()->getStatusCode();
        if ($status !== 302) {
            throw new \RuntimeException(sprintf("Unexpected status code: %d", $status));
        }

        $redirect = new CartPage($this->client, $this);
        $location = $this->client->getResponse()->headers->get('location');
        if ($location !== $redirect->getUrl()) {
            throw new \RuntimeException(sprintf("Unexpected redirect url: %s", $location));
        }

        return $redirect;
    }
}
