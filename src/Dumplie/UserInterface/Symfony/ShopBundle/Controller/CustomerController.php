<?php

declare (strict_types = 1);

namespace Dumplie\UserInterface\Symfony\ShopBundle\Controller;

use Dumplie\Inventory\Application\Services;
use Dumplie\UserInterface\Symfony\ShopBundle\Form\Customer\ProductType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomerController extends Controller
{
    /**
     * @Route("/", name="dumplie_customer_homepage")
     */
    public function homepageAction() : Response
    {
        $inventory = $this->get(Services::INVENTORY_APPLICATION_QUERY)->findAll(10, 0);

        return $this->render(':customer/homepage:index.html.twig', [
            'inventory' => $inventory
        ]);
    }

    /**
     * @Route("/product/{sku}", name="dumplie_customer_product_details")
     */
    public function detailsAction(Request $request, string $sku) : Response
    {
        $product = $this->get(Services::INVENTORY_APPLICATION_QUERY)->getBySku($sku);

        $form = $this->createForm(ProductType::class, ['sku' => $product->sku()], [
            'action' => $this->generateUrl('dumplie_cart_add'),
        ]);
        $form->handleRequest($request);

        return $this->render(':customer/product:details.html.twig', [
            'product' => $product,
            'form' => $form->createView()
        ]);
    }
}
