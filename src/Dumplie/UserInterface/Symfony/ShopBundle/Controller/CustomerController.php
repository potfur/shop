<?php

declare (strict_types = 1);

namespace Dumplie\UserInterface\Symfony\ShopBundle\Controller;

use Dumplie\Customer\Application\Command\AddToCart;
use Dumplie\Customer\Application\Command\ChangeBillingAddress;
use Dumplie\Customer\Application\Command\ChangeShippingAddress;
use Dumplie\Customer\Application\Command\CreateCart;
use Dumplie\Customer\Application\Command\NewCheckout;
use Dumplie\Customer\Application\Command\PlaceOrder;
use Dumplie\Customer\Application\Command\RemoveFromCart;
use Dumplie\Customer\Application\Exception\QueryException;
use Dumplie\Customer\Application\Query\Result\Cart;
use Dumplie\Customer\Application\Services as CustomerServices;
use Dumplie\Customer\Domain\CartId;
use Dumplie\Customer\Domain\OrderId;
use Dumplie\Inventory\Application\Services as InventoryServices;
use Dumplie\SharedKernel\Application\Services;
use Dumplie\UserInterface\Symfony\ShopBundle\Form\Customer\CartItemType;
use Dumplie\UserInterface\Symfony\ShopBundle\Form\Customer\CheckoutAddressType;
use Dumplie\UserInterface\Symfony\ShopBundle\Form\Customer\ProductType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomerController extends Controller
{
    /**
     * @Route("/", name="dumplie_customer_homepage")
     */
    public function homepageAction(): Response
    {
        $inventory = $this->get(InventoryServices::INVENTORY_APPLICATION_QUERY)->findAll(10, 0);

        return $this->render(':customer/homepage:index.html.twig', [
            'inventory' => $inventory
        ]);
    }

    /**
     * @Route("/product/{sku}", name="dumplie_customer_product_details")
     */
    public function detailsAction(Request $request, string $sku): Response
    {
        $product = $this->get(InventoryServices::INVENTORY_APPLICATION_QUERY)->getBySku($sku);

        $form = $this->createForm(ProductType::class, ['sku' => $product->sku()], [
            'action' => $this->generateUrl('dumplie_customer_cart_add_product'),
        ]);
        $form->handleRequest($request);

        return $this->render(':customer/product:details.html.twig', [
            'product' => $product,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/cart", name="dumplie_customer_cart_add_product")
     * @Method({"POST"})
     */
    public function addProductAction(Request $request)
    {
        $form = $this->createForm(ProductType::class);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $cartId = $this->ensureCartId();

            $command = new AddToCart(
                $form->get('sku')->getData(),
                $form->get('quantity')->getData(),
                $cartId
            );

            $this->get(Services::KERNEL_COMMAND_BUS)->handle($command);
        }

        return $this->redirect($this->generateUrl('dumplie_customer_cart'));
    }

    /**
     * @Route("/cart/{sku}", name="dumplie_customer_cart_remove_product")
     * @Method({"DELETE"})
     */
    public function removeProductAction(Request $request, string $sku)
    {
        $cartId = $this->getCartId();

        $command = new RemoveFromCart($cartId, $sku);
        $this->get(Services::KERNEL_COMMAND_BUS)->handle($command);

        return $this->redirect($this->generateUrl('dumplie_customer_cart'));
    }

    /**
     * @Route("/cart", name="dumplie_customer_cart")
     * @Method({"GET"})
     */
    public function cartAction()
    {
        try {
            $cart = $this->get(CustomerServices::CUSTOMER_CART_QUERY)->getById($this->getCartId());
        } catch (QueryException $e) {
            $cart = new Cart($this->getParameter('dumplie_currency'));
        }

        return $this->render(':customer/cart:index.html.twig', [
            'cart' => $cart,
            'items' => $this->itemForms($cart)
        ]);
    }

    /**
     * @Route("/checkout/new", name="dumplie_customer_checkout_new")
     * @Method({"GET", "POST"})
     */
    public function newCheckoutAction(Request $request): Response
    {
        $form = $this->createForm(CheckoutAddressType::class, [], [
            'action' => $this->generateUrl('dumplie_customer_checkout_new'),
        ]);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $command = new NewCheckout(
                $this->getCartId(),
                $form->get('name')->getData(),
                $form->get('street')->getData(),
                $form->get('postCode')->getData(),
                $form->get('city')->getData(),
                $form->get('countryCode')->getData()
            );

            $this->get(Services::KERNEL_COMMAND_BUS)->handle($command);

            return $this->redirect($this->generateUrl('dumplie_customer_checkout'));
        }

        $cart = $this->get(CustomerServices::CUSTOMER_CART_QUERY)->getById($this->getCartId());
        return $this->render(':customer/checkout:new.html.twig', [
            'cart' => $cart,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/checkout", name="dumplie_customer_checkout")
     * @Method({"GET"})
     */
    public function checkoutAction(Request $request): Response
    {
        try {
            $cartId = $this->getCartId();
        } catch (QueryException $e) {
            return $this->redirect($this->generateUrl('dumplie_customer_cart'));
        }

        $cart = $this->get(CustomerServices::CUSTOMER_CART_QUERY)->getById($this->getCartId());
        if ($cart->isEmpty()) {
            return $this->redirect($this->generateUrl('dumplie_customer_cart'));
        }

        try {
            $checkout = $this->get(CustomerServices::CUSTOMER_CHECKOUT_QUERY)->getById($cartId);
            return $this->render(':customer/checkout:index.html.twig', [
                'cart' => $cart,
                'checkout' => $checkout,
            ]);
        } catch (QueryException $e) {
            return $this->redirect($this->generateUrl('dumplie_customer_checkout_new'));
        }
    }

    /**
     * @Route("/checkout/billing-address", name="dumplie_customer_checkout_billing_address")
     * @Method({"GET", "POST"})
     */
    public function changeBillingAddressAction(Request $request): Response
    {
        $cartId = $this->getCartId();
        $checkout = $this->get(CustomerServices::CUSTOMER_CHECKOUT_QUERY)->getById($cartId);
        $address = $checkout->billingAddress();

        $form = $this->createForm(CheckoutAddressType::class, [
            'name' => $address->getName(),
            'street' => $address->getStreet(),
            'postCode' => $address->getPostCode(),
            'city' => $address->getCity(),
            'countryCode' => $address->getCountryCode()
        ], [
            'action' => $this->generateUrl('dumplie_customer_checkout_billing_address'),
        ]);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $command = new ChangeBillingAddress(
                $cartId,
                $form->get('name')->getData(),
                $form->get('street')->getData(),
                $form->get('postCode')->getData(),
                $form->get('city')->getData(),
                $form->get('countryCode')->getData()
            );

            $this->get(Services::KERNEL_COMMAND_BUS)->handle($command);

            return $this->redirect($this->generateUrl('dumplie_customer_checkout'));
        }

        return $this->render(':customer/checkout:address.billing.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/checkout/shipping-address", name="dumplie_customer_checkout_shipping_address")
     * @Method({"GET", "POST"})
     */
    public function changeShippingAddressAction(Request $request): Response
    {
        $cartId = $this->getCartId();
        $checkout = $this->get(CustomerServices::CUSTOMER_CHECKOUT_QUERY)->getById($cartId);
        $address = $checkout->shippingAddress();

        $form = $this->createForm(CheckoutAddressType::class, [
            'name' => $address->getName(),
            'street' => $address->getStreet(),
            'postCode' => $address->getPostCode(),
            'city' => $address->getCity(),
            'countryCode' => $address->getCountryCode()
        ], [
            'action' => $this->generateUrl('dumplie_customer_checkout_shipping_address'),
        ]);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $command = new ChangeShippingAddress(
                $cartId,
                $form->get('name')->getData(),
                $form->get('street')->getData(),
                $form->get('postCode')->getData(),
                $form->get('city')->getData(),
                $form->get('countryCode')->getData()
            );

            $this->get(Services::KERNEL_COMMAND_BUS)->handle($command);

            return $this->redirect($this->generateUrl('dumplie_customer_checkout'));
        }

        return $this->render(':customer/checkout:address.shipping.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/checkout/place", name="dumplie_customer_checkout_place")
     * @Method({"GET"})
     */
    public function placeOrderAction(): Response
    {
        $command = new PlaceOrder(
            $this->getCartId(),
            (string) OrderId::generate()
        );

        $this->get(Services::KERNEL_COMMAND_BUS)->handle($command);

        return $this->render(':customer/checkout:placed.html.twig');
    }

    /**
     * @param Cart $cart
     * @return FormView[]
     */
    private function itemForms(Cart $cart): array
    {
        $itemForms = [];
        foreach ($cart->items() as $item) {
            $form = $this->createForm(
                CartItemType::class,
                [
                    'sku' => $item->sku(),
                    'metadata' => $item->metadata(),
                    'quantity' => $item->quantity(),
                    'price' => $item->price(),
                    'currency' => $item->currency(),
                ],
                [
                    'action' => $this->generateUrl('dumplie_customer_cart_remove_product', ['sku' => $item->sku()]),
                    'method' => 'DELETE'
                ]
            );

            $itemForms[] = $form->createView();
        }

        return $itemForms;
    }

    /**
     * @return string
     * @throws QueryException
     */
    private function getCartId(): string
    {
        $cartId = $this->get('session')->get('cartId');

        if (null !== $cartId && $this->get(CustomerServices::CUSTOMER_CART_QUERY)->doesCartWithIdExist($cartId)) {
            return $cartId;
        }

        throw QueryException::cartNotFound($cartId);
    }

    /**
     * @return string
     */
    private function ensureCartId(): string
    {
        try {
            return $this->getCartId();
        } catch (QueryException $e) {
            $cartId = CartId::generate();
            $command = new CreateCart((string)$cartId, $this->getParameter('dumplie_currency'));

            $this->get(Services::KERNEL_COMMAND_BUS)->handle($command);
            $this->get('session')->set('cartId', (string)$cartId);

            return (string)$cartId;
        }
    }
}
