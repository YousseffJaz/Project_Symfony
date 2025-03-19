<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Form\AdminCategoryType;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/categories')]
class AdminCategoryController extends AbstractController
{
    #[Route('', name: 'admin_category_index')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(CategoryRepository $repo): Response {
        $categories = $repo->findAll();

        return $this->render('admin/category/index.html.twig', [
            'categories' => $categories
        ]);
    }

    #[Route('/new', name: 'admin_category_new')]
    #[IsGranted('ROLE_ADMIN')]
    public function new(
        Request $request,
        EntityManagerInterface $manager
    ): Response {
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

    #[Route('/edit/{id}', name: 'admin_category_edit')]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(
        Category $category,
        Request $request,
        EntityManagerInterface $manager
    ): Response {
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

    #[Route('/delete/{id}', name: 'admin_category_delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Category $category, EntityManagerInterface $manager): Response {
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