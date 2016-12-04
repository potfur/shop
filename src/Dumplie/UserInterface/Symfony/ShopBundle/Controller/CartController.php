<?php

namespace Dumplie\UserInterface\Symfony\ShopBundle\Controller;

use Dumplie\Customer\Application\Command\AddToCart;
use Dumplie\Customer\Application\Command\CreateCart;
use Dumplie\Customer\Application\Command\RemoveFromCart;
use Dumplie\Customer\Application\Exception\QueryException;
use Dumplie\Customer\Application\Query\Result\Cart;
use Dumplie\Customer\Application\Services as CustomerServices;
use Dumplie\Customer\Domain\CartId;
use Dumplie\SharedKernel\Application\Services;
use Dumplie\UserInterface\Symfony\ShopBundle\Form\Customer\CartItemType;
use Dumplie\UserInterface\Symfony\ShopBundle\Form\Customer\ProductType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;

class CartController extends Controller
{
    /**
     * @Route("/cart", name="dumplie_cart_add")
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

        return $this->redirect($this->generateUrl('dumplie_cart'));
    }

    /**
     * @Route("/cart/{sku}", name="dumplie_cart_remove")
     * @Method({"DELETE"})
     */
    public function removeProductAction(Request $request, string $sku)
    {
        $cartId = $this->getCartId();

        $command = new RemoveFromCart($cartId, $sku);
        $this->get(Services::KERNEL_COMMAND_BUS)->handle($command);

        return $this->redirect($this->generateUrl('dumplie_cart'));
    }

    /**
     * @Route("/cart", name="dumplie_cart")
     * @Method({"GET"})
     */
    public function cartAction()
    {
        try {
            $cart = $this->get(CustomerServices::CUSTOMER_CART_QUERY)->getById($this->getCartId());
        } catch (QueryException $e) {
            $cart = new Cart($this->getParameter('dumplie_currency'));
        }

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
                    'action' => $this->generateUrl('dumplie_cart_remove', ['sku' => $item->sku()]),
                    'method' => 'DELETE'
                ]
            );

            $itemForms[] = $form->createView();
        }

        return $this->render(':customer/cart:index.html.twig', [
            'cart' => $cart,
            'itemForms' => $itemForms
        ]);
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
    private function ensureCartId() : string
    {
        try {
            return $this->getCartId();
        } catch (QueryException $e) {
            $cartId = CartId::generate();
            $command = new CreateCart($cartId, $this->getParameter('dumplie_currency'));

            $this->get(Services::KERNEL_COMMAND_BUS)->handle($command);
            $this->get('session')->set('cartId', (string) $cartId);

            return (string) $cartId;
        }
    }
}
