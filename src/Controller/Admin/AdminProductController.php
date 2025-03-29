<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Entity\Variant;
use App\Form\AdminProductType;
use App\Repository\LineItemRepository;
use App\Repository\ProductRepository;
use App\Repository\StockListRepository;
use App\Repository\VariantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/products')]
class AdminProductController extends AbstractController
{
    #[Route('', name: 'admin_product_index')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(ProductRepository $repo)
    {
        $products = $repo->findAll();

        return $this->render('admin/product/index.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/new', name: 'admin_product_new')]
    #[IsGranted('ROLE_ADMIN')]
    public function new(
        Request $request,
        EntityManagerInterface $manager,
    ): Response {
        $product = new Product();
        $form = $this->createForm(AdminProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($product->getStockLists() as $stock) {
                if ($product->getDigital()) {
                    $stock->setQuantity(1000000000);
                }
                $stock->setProduct($product);
                $manager->persist($stock);
            }

            $manager->persist($product);

            $variant = new Variant();
            $variant->setTitle($product->getTitle());
            $variant->setProduct($product);
            $variant->setPrice($product->getPrice());

            $manager->persist($variant);
            $manager->flush();

            $this->addFlash(
                'success',
                'Un nouveau produit à été ajouté !'
            );

            return $this->redirectToRoute('admin_product_edit', ['id' => $product->getId()]);
        }

        return $this->render('admin/product/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/edit/{id}', name: 'admin_product_edit')]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(
        Product $product,
        Request $request,
        EntityManagerInterface $manager,
        VariantRepository $variantRepo,
        StockListRepository $stockListRepo,
        LineItemRepository $lineItemRepo,
    ): Response {
        $form = $this->createForm(AdminProductType::class, $product);
        $form->handleRequest($request);
        $variants = $variantRepo->findBy(['archive' => false, 'product' => $product], ['title' => 'ASC']);

        if ($form->isSubmitted() && $form->isValid()) {
            $previousStocks = $stockListRepo->findByProduct($product);

            if ($previousStocks) {
                foreach ($previousStocks as $previousStock) {
                    $found = false;
                    foreach ($product->getStockLists() as $stock) {
                        if ($previousStock->getName() == $stock->getName()) {
                            $found = true;
                            break;
                        }
                    }

                    if (!$found) {
                        $lineItems = $lineItemRepo->findByStock($previousStock);

                        if ($lineItems) {
                            foreach ($lineItems as $lineItem) {
                                $lineItem->setStock(null);
                                $manager->flush();
                            }
                        }

                        $manager->remove($previousStock);
                    }
                }
            }

            foreach ($product->getStockLists() as $stock) {
                if ($product->getDigital()) {
                    $stock->setQuantity(1000000000);
                }
                $stock->setProduct($product);
                $manager->persist($stock);
            }

            $manager->flush();

            $this->addFlash(
                'success',
                'Le produit a été modifié !'
            );

            return $this->redirectToRoute('admin_product_index');
        }

        return $this->render('admin/product/edit.html.twig', [
            'form' => $form->createView(),
            'product' => $product,
            'variants' => $variants,
        ]);
    }

    #[Route('/delete/{id}', name: 'admin_product_delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Product $product, EntityManagerInterface $manager): Response
    {
        $product->setArchive(true);
        $variants = $product->getVariants();

        foreach ($variants as $variant) {
            $variant->setArchive(true);
        }

        $manager->flush();

        $this->addFlash(
            'success',
            'Le produit a été supprimé !'
        );

        return $this->redirectToRoute('admin_product_index');
    }

    #[Route('/admin/products/variant/delete', name: 'admin_product_variant_delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteVariant(Request $request, VariantRepository $variantRepo, EntityManagerInterface $manager): Response
    {
        $id = $request->query->get('id');
        if ($id) {
            $variant = $variantRepo->find($id);
            if ($variant) {
                $manager->remove($variant);
                $manager->flush();
            }
        }

        return $this->json(true);
    }

    #[Route('/admin/products/stock', name: 'admin_product_stock')]
    #[IsGranted('ROLE_ADMIN')]
    public function stock(Request $request, StockListRepository $stockRepo, ProductRepository $productRepo): Response
    {
        $productId = $request->query->get('id');
        if ($productId) {
            $product = $productRepo->find($productId);
            if ($product) {
                $stocks = $stockRepo->findByProduct($product);

                return $this->render('admin/product/stock.html.twig', [
                    'stocks' => $stocks,
                    'product' => $product,
                ]);
            }
        }

        return $this->redirectToRoute('admin_product_index');
    }
}
