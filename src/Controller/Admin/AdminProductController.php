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
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class AdminProductController extends AbstractController
{
  /**
   * Permet d'afficher les produits
   *
   * @Route("/admin/products", name="admin_product_index")
   * @Security("is_granted('ROLE_ADMIN')")
   */
  public function index(Request $request, ProductRepository $productRepo, StockListRepository $stockRepo, ObjectManager $manager)
  {
    $quantity = $request->request->get('quantity');
    $productId = $request->request->get('productId');
    
    if ($productId && $quantity) {
      for ($i = 0; $i < sizeof($productId); $i++) {
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


   /**
   * Permet d'ajouter un produit
   *
   * @Route("/admin/products/new", name="admin_product_new")
   * @Security("is_granted('ROLE_ADMIN')")
   * 
   */
   public function new(Request $request, ObjectManager $manager, UserPasswordEncoderInterface $encoder) {
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


   /**
   * Permet d'éditer un produit
   *
   * @Route("/admin/products/edit/{id}", name="admin_product_edit")
   * @Security("is_granted('ROLE_ADMIN')")
   * 
   */
   public function edit(Product $product, Request $request, ObjectManager $manager, UserPasswordEncoderInterface $encoder, VariantRepository $variantRepo, StockListRepository $stockListRepo, LineItemRepository $lineItemRepo) {
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


  /**
   * Permet de supprimer un produit
   *
   * @Route("/admin/products/delete/{id}", name="admin_product_delete")
   * @Security("is_granted('ROLE_ADMIN')")
   */
  public function delete(Product $product, ObjectManager $manager)  {
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