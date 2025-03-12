<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Entity\Variant;
use App\Form\AdminVariantType;
use App\Repository\VariantRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class AdminVariantController extends AbstractController
{
  /**
   * Permet d'afficher les variants
   *
   * @Route("/admin/variants", name="admin_variant_index")
   * @Security("is_granted('ROLE_ADMIN')")
   */
  public function index(VariantRepository $variantRepo)
  {
    $variants = $variantRepo->findBy(['archive' => false], ['title' => "ASC"]);

    return $this->render('admin/variant/index.html.twig', [
      'variants' => $variants
    ]);
  }


  /**
   * Permet d'imprimer la grille tarifaire
   *
   * @Route("/admin/variants/print", name="admin_variant_print")
   * @Security("is_granted('ROLE_ADMIN')")
   */
  public function print(Request $request, VariantRepository $variantRepo)
  {
    $priceLists = $request->request->get('priceTitle');
    $variants = $variantRepo->findBy(['archive' => false], ['title' => "ASC"]);

    return $this->render('admin/variant/print.html.twig', [
      'variants' => $variants,
      'priceLists' => $priceLists,
    ]);
  }


   /**
   * Permet d'ajouter un variant
   *
   * @Route("/admin/product/{id}/variants/new", name="admin_variant_new")
   * @Security("is_granted('ROLE_ADMIN')")
   * 
   */
   public function new(Product $product, Request $request, ObjectManager $manager, UserPasswordEncoderInterface $encoder) {
    $variant = new Variant();
    $form = $this->createForm(AdminVariantType::class, $variant);
    $form->handleRequest($request);

    if($form->isSubmitted() && $form->isValid()) {
      $variant->setProduct($product);

      foreach($variant->getPriceLists() as $price){
        $price->setVariant($variant);
        $manager->persist($price);
      }

      $manager->persist($variant);
      $manager->flush();

      $this->addFlash(
        'success',
        "Un nouveau variant à été ajouté !"
      );

      return $this->redirectToRoute('admin_product_edit', [ 'id' => $product->getId() ]);
    }

    return $this->render('admin/variant/new.html.twig', [
      'form' => $form->createView()
    ]);
  }


   /**
   * Permet d'éditer un variant
   *
   * @Route("/admin/variants/edit/{id}", name="admin_variant_edit")
   * @Security("is_granted('ROLE_ADMIN')")
   * 
   */
   public function edit(Variant $variant, Request $request, ObjectManager $manager, UserPasswordEncoderInterface $encoder) {
    $form = $this->createForm(AdminVariantType::class, $variant);
    $form->handleRequest($request);

    if($form->isSubmitted() && $form->isValid()) {
      foreach($variant->getPriceLists() as $price){
        $price->setVariant($variant);
        $manager->persist($price);
      }
      
      $manager->flush();

      $this->addFlash(
        'success',
        "Le variant a été modifié !"
      );

      return $this->redirectToRoute('admin_product_edit', [ 'id' => $variant->getProduct()->getId() ]);
    }

    return $this->render('admin/variant/edit.html.twig', [
      'form' => $form->createView(),
      'variant' => $variant,
    ]);
  }


   /**
   * Autocomplete variants
   *
   * @Route("/admin/variants/autocomplete/variants", name="admin_variant_autocomplete")
   * @Security("is_granted('ROLE_ADMIN')")
   * 
   */
   public function autocomplete(VariantRepository $repo, Request $request, ObjectManager $manager, UserPasswordEncoderInterface $encoder) {
    $keyword = strtolower($request->query->get('keyword')); 
    $stockList = $request->query->get('stockList'); 
    $variants = $repo->filter($keyword, $stockList);

    return $this->json($variants, 200);
  }


  /**
   * Permet de supprimer un variant
   *
   * @Route("/admin/variants/delete/{id}", name="admin_variant_delete")
   * @Security("is_granted('ROLE_ADMIN')")
   */
  public function delete(Variant $variant, ObjectManager $manager)
  {
    $variant->setArchive(true);
    $manager->flush();

    $this->addFlash(
      'success',
      "Le variant a été supprimé !"
    );

    return $this->redirectToRoute("admin_variant_index");
  }
}