<?php

namespace App\Controller\Admin;

use App\Entity\Variant;
use App\Entity\Preorder;
use App\Entity\Product;
use App\Form\AdminProductType;
use App\Repository\LineItemRepository;
use App\Repository\VariantRepository;
use App\Repository\ProductRepository;
use App\Repository\StockListRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/products')]
class AdminProductController extends AbstractController
{
    #[Route('', name: 'admin_product_index')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(
        Request $request,
        ProductRepository $productRepo,
        StockListRepository $stockRepo,
        EntityManagerInterface $manager
    ): Response {
        $quantity = $request->request->all('quantity');
        $productId = $request->request->all('productId');
        
        if (is_array($productId) && is_array($quantity) && count($productId) > 0 && count($quantity) > 0) {
            for ($i = 0; $i < count($productId); $i++) {
                if (isset($quantity[$i]) && isset($productId[$i])) {
                    $product = $productRepo->findOneById($productId[$i]);
                    $estimation = (int) $quantity[$i];

                    if ($product && $estimation > 0) {
                        $preorder = new Preorder();
                        $preorder->setTitle($product->getTitle());
                        $preorder->setQuantity($estimation);
                        $preorder->setProduct($product);
                        $manager->persist($preorder);
                        $manager->flush();
                    }
                }
            }

            $this->addFlash(
                'success',
                "Les produits à commander ont été ajoutés !"
            );
        }

        $products = $productRepo->findBy(['archive' => false], ['title' => "ASC"]);

        foreach ($products as $product) {
            if ($product->getAlert()) {
                $stocks = $stockRepo->findStockInfAlert($product, $product->getAlert());

                if ($stocks) {
                    foreach ($stocks as $stock) {
                        if ($stock->getQuantity() == 1) {
                            $this->addFlash(
                                'error',
                                "{$product->getTitle()} dans le stock {$stock->getName()} est disponible en 1 exemplaire !"
                            );
                        } else {
                            $this->addFlash(
                                'error',
                                "{$product->getTitle()} dans le stock {$stock->getName()} est disponible en {$stock->getQuantity()} exemplaires !"
                            );
                        }
                    }
                }
            }
        }

        return $this->render('admin/product/index.html.twig', [
            'products' => $products
        ]);
    }

    #[Route('/new', name: 'admin_product_new')]
    #[IsGranted('ROLE_ADMIN')]
    public function new(
        Request $request,
        EntityManagerInterface $manager,
        UserPasswordHasherInterface $hasher
    ): Response {
        $product = new Product();
        $form = $this->createForm(AdminProductType::class, $product);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            foreach($product->getStockLists() as $stock) {
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

            $manager->persist($variant);
            $manager->flush();

            $this->addFlash(
                'success',
                "Un nouveau produit à été ajouté !"
            );

            return $this->redirectToRoute('admin_product_edit', [ 'id' => $product->getId() ]);
        }

        return $this->render('admin/product/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/edit/{id}', name: 'admin_product_edit')]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(
        Product $product,
        Request $request,
        EntityManagerInterface $manager,
        UserPasswordHasherInterface $hasher,
        VariantRepository $variantRepo,
        StockListRepository $stockListRepo,
        LineItemRepository $lineItemRepo
    ): Response {
        $form = $this->createForm(AdminProductType::class, $product);
        $form->handleRequest($request);
        $variants = $variantRepo->findBy(['archive' => false, 'product' => $product], ['title' => "ASC"]);
        
        if($form->isSubmitted() && $form->isValid()) {
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

            foreach($product->getStockLists() as $stock) {
                if ($product->getDigital()) {
                    $stock->setQuantity(1000000000);
                }
                $stock->setProduct($product);
                $manager->persist($stock);
            }

            $manager->flush();

            $this->addFlash(
                'success',
                "Le produit a été modifié !"
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
    public function delete(Product $product, EntityManagerInterface $manager): Response {
        $product->setArchive(true);
        $variants = $product->getVariants();

        foreach ($variants as $variant) {
            $variant->setArchive(true);
        }

        $manager->flush();

        $this->addFlash(
            'success',
            "Le produit a été supprimé !"
        );

        return $this->redirectToRoute("admin_product_index");
    }
}