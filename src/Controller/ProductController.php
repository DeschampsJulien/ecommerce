<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController
{
    #[Route('/products', name: 'app_products')]
    public function index(ProductRepository $productRepository, Request $request): Response
    {
        $priceRange = $request->query->get('price');

        $criteria = [];
        if ($priceRange) {
            switch ($priceRange) {
                case '10-29':
                    $criteria['min'] = 10; $criteria['max'] = 29; break;
                case '29-35':
                    $criteria['min'] = 29; $criteria['max'] = 35; break;
                case '35-50':
                    $criteria['min'] = 35; $criteria['max'] = 50; break;
            }
        }

        if (!empty($criteria)) {
            $products = $productRepository->createQueryBuilder('p')
                ->where('p.price >= :min')
                ->andWhere('p.price <= :max')
                ->setParameter('min', $criteria['min'])
                ->setParameter('max', $criteria['max'])
                ->getQuery()
                ->getResult();
        } else {
            $products = $productRepository->findAll();
        }

        return $this->render('product/index.html.twig', [
            'products' => $products,
            'selectedRange' => $priceRange,
        ]);
    }

    #[Route('/product/{id}', name: 'product_show')]
    public function show(Product $product, Request $request, CartService $cartService): Response
    {
        // Ajouter au panier
        if ($request->isMethod('POST')) {
            $size = $request->request->get('size');
            $quantity = (int) $request->request->get('quantity', 1);

            $cartService->add($product, $size, $quantity);

            $this->addFlash('success', 'Produit ajouté au panier !');

            return $this->redirectToRoute('app_cart');
        }

        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }
}