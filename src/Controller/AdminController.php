<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Stock;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Repository\OrderRepository;
use App\Repository\OrderItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'app_admin')]
    public function index(ProductRepository $productRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $products = $productRepository->findAll();

        return $this->render('admin/index.html.twig', [
            'products' => $products,
            'controller_name' => 'AdminController'
        ]);
    }

    #[Route('/dashboard', name: 'app_admin_dashboard')]
    public function dashboard(
        ProductRepository $productRepository,
        OrderRepository $orderRepository,
        OrderItemRepository $orderItemRepository
    ): Response {

        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $orders = $orderRepository->findAll();

        $totalRevenue = array_sum(array_map(
            fn($o) => $o->getTotal(),
            $orders
        ));

        return $this->render('admin/dashboard.html.twig', [
            'totalProducts' => count($productRepository->findAll()),
            'totalOrders' => count($orders),
            'totalRevenue' => $totalRevenue,
            'topProducts' => $orderItemRepository->findTopProducts()
        ]);
    }

    #[Route('/product/new', name: 'admin_product_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($product);

            // Ajouter stock par défaut pour toutes les tailles
            foreach (['XS','S','M','L','XL'] as $size) {
                $stock = new Stock();
                $stock->setSize($size);
                $stock->setQuantity(2); // stock initial
                $stock->setProduct($product);
                $em->persist($stock);
            }

            $em->flush();

            $this->addFlash('success', 'Produit ajouté avec succès.');

            return $this->redirectToRoute('app_admin');
        }

        return $this->render('admin/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/product/{id}/edit', name: 'admin_product_edit')]
    public function edit(Product $product, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Produit modifié avec succès.');
            return $this->redirectToRoute('app_admin');
        }

        return $this->render('admin/edit.html.twig', [
            'form' => $form->createView(),
            'product' => $product
        ]);
    }

    #[Route('/product/{id}/delete', name: 'admin_product_delete', methods: ['POST'])]
    public function delete(Product $product, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $em->remove($product);
        $em->flush();

        $this->addFlash('success', 'Produit supprimé avec succès.');

        return $this->redirectToRoute('app_admin');
    }

    #[Route('/stock/{id}', name: 'admin_stock_update', methods: ['POST'])]
    public function updateStock(Stock $stock, Request $request, EntityManagerInterface $em)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $quantity = (int) $request->request->get('quantity');
        $stock->setQuantity($quantity);

        $em->flush();

        return $this->redirectToRoute('admin_product_edit', [
            'id' => $stock->getProduct()->getId()
        ]);
    }
}