<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Form\AdminCategoryType;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class AdminCategoryController extends AbstractController
{
  /**
   * Permet d'afficher les catégories
   *
   * @Route("/admin/categories", name="admin_category_index")
   * @Security("is_granted('ROLE_ADMIN')")
   */
  public function index(CategoryRepository $repo) {
    $categories = $repo->findAll();

    return $this->render('admin/category/index.html.twig', [
      'categories' => $categories
    ]);
  }


   /**
   * Permet d'ajouter une catégorie
   *
   * @Route("/admin/categories/new", name="admin_category_new")
   * @Security("is_granted('ROLE_ADMIN')")
   * 
   */
   public function new(Request $request, ObjectManager $manager, UserPasswordEncoderInterface $encoder) {
    $category = new Category();
    $form = $this->createForm(AdminCategoryType::class, $category);
    $form->handleRequest($request);

    if($form->isSubmitted() && $form->isValid()) {
      $manager->persist($category);
      $manager->flush();

      $this->addFlash(
        'success',
        "La catégorie à été ajoutée !"
      );

      return $this->redirectToRoute('admin_category_index');
    }

    return $this->render('admin/category/new.html.twig', [
      'form' => $form->createView()
    ]);
  }


   /**
   * Permet d'éditer une categorie
   *
   * @Route("/admin/categories/edit/{id}", name="admin_category_edit")
   * @Security("is_granted('ROLE_ADMIN')")
   * 
   */
   public function edit(Category $category, Request $request, ObjectManager $manager, UserPasswordEncoderInterface $encoder) {
    $form = $this->createForm(AdminCategoryType::class, $category);
    $form->handleRequest($request);

    if($form->isSubmitted() && $form->isValid()) {
      $manager->flush();

      $this->addFlash(
        'success',
        "La categorie a été modifiée !"
      );

      return $this->redirectToRoute('admin_category_index');
    }

    return $this->render('admin/category/edit.html.twig', [
      'form' => $form->createView()
    ]);
  }


  /**
   * Permet de supprimer une categorie
   *
   * @Route("/admin/categories/delete/{id}", name="admin_category_delete")
   * @Security("is_granted('ROLE_ADMIN')")
   */
  public function delete(Category $category, ObjectManager $manager) {
    foreach ($category->getProduct() as $product) {
      $product->setCategory(null);
    }   
    
    $manager->remove($category);
    $manager->flush();

    $this->addFlash(
      'success',
      "La catégorie a été supprimée !"
    );

    return $this->redirectToRoute("admin_category_index");
  }
}