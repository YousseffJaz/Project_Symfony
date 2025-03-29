<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Entity\Variant;
use App\Form\AdminVariantType;
use App\Repository\VariantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/variants')]
class AdminVariantController extends AbstractController
{
    #[Route('', name: 'admin_variant_index')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(VariantRepository $variantRepo): Response
    {
        $variants = $variantRepo->findBy(['archive' => false], ['title' => 'ASC']);

        return $this->render('admin/variant/index.html.twig', [
            'variants' => $variants,
        ]);
    }

    #[Route('/product/{id}/variants/new', name: 'admin_variant_new')]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Product $product, Request $request, EntityManagerInterface $manager): Response
    {
        $variant = new Variant();
        $form = $this->createForm(AdminVariantType::class, $variant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $variant->setProduct($product);
            $manager->persist($variant);
            $manager->flush();

            $this->addFlash(
                'success',
                'Un nouveau variant à été ajouté !'
            );

            return $this->redirectToRoute('admin_product_edit', ['id' => $product->getId()]);
        }

        return $this->render('admin/variant/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/edit/{id}', name: 'admin_variant_edit')]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Variant $variant, Request $request, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(AdminVariantType::class, $variant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->flush();

            $this->addFlash(
                'success',
                'Le variant a été modifié !'
            );

            return $this->redirectToRoute('admin_product_edit', ['id' => $variant->getProduct()->getId()]);
        }

        return $this->render('admin/variant/edit.html.twig', [
            'form' => $form->createView(),
            'variant' => $variant,
        ]);
    }

    #[Route('/autocomplete/variants', name: 'admin_variant_autocomplete')]
    #[IsGranted('ROLE_ADMIN')]
    public function autocomplete(VariantRepository $repo, Request $request): Response
    {
        $keyword = strtolower($request->query->get('keyword'));
        $stockList = $request->query->get('stockList');
        $variants = $repo->filter($keyword, $stockList);

        return $this->json($variants, 200);
    }

    #[Route('/delete/{id}', name: 'admin_variant_delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Variant $variant, EntityManagerInterface $manager): Response
    {
        $variant->setArchive(true);
        $manager->flush();

        $this->addFlash(
            'success',
            'Le variant a été supprimé !'
        );

        return $this->redirectToRoute('admin_variant_index');
    }
}
