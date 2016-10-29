<?php
declare (strict_types = 1);

namespace Dumplie\UserInterafce\Symfony\ShopBundle\Page\Customer;

use Dumplie\UserInterafce\Symfony\ShopBundle\Page\BasePage;

class ProductDetailsPage extends BasePage
{
    private $form;

    public function getUrl() : string
    {
        return '/product/DUMPLIE_SKU';
    }

    public function fillForm(string $sku, int $quantity) : ProductDetailsPage
    {
        $this->form = $this->getCrawler()->filter("form[name=\"product\"]")->form(
            [
                'product[sku]' => $sku,
                'product[quantity]' => $quantity,
            ],
            'POST'
        );

        return $this;
    }

    public function pressAddButton() : CartPage
    {
        $this->client->submit($this->form);

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
