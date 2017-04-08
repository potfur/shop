<?php
declare (strict_types = 1);

namespace Dumplie\UserInterafce\Symfony\ShopBundle\Page\Customer;

use Dumplie\UserInterafce\Symfony\ShopBundle\Page\BasePage;

abstract class AddressChangePage extends BasePage
{
    protected $form;

    public function fillForm(string $name, string $street, string $postCode, string $city, string $countryCode = 'GB') : BasePage
    {
        $this->form = $this->getCrawler()->filter("form[name=\"checkout_address\"]")->form([
            'checkout_address[name]' => $name,
            'checkout_address[street]' => $street,
            'checkout_address[postCode]' => $postCode,
            'checkout_address[city]' => $city,
            'checkout_address[countryCode]' => $countryCode,
        ], 'POST');

        return $this;
    }

    /**
     * @return CheckoutPage
     */
    public function pressSaveButton() : CheckoutPage
    {
        $this->client->submit($this->form);

        $status = $this->client->getResponse()->getStatusCode();
        if ($status === 302) {
            $this->client->followRedirect();
            return new CheckoutPage($this->client, $this);
        }

        throw new \RuntimeException(sprintf("Unexpected status code: %d", $status));
    }
}
