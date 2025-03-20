<?php

namespace App\Controller\Admin;

use App\Entity\Preorder;
use App\Repository\PreorderRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminPreOrderControler extends AbstractController
{
  /**
   * Permet d'afficher les pré-commandes
   */
  #[Route('/admin/preorder', name: 'admin_preorder_index')]
  #[IsGranted('ROLE_ADMIN')]
  public function index(Request $request, PreorderRepository $preorderRepo) {
    $preorders = $preorderRepo->findAll();

    return $this->render('admin/preorder/index.html.twig', [
      'preorders' => $preorders,
    ]);
  }

  /**
   * Permet de supprimer une pré-commande
   */
  #[Route('/admin/preorder/delete/{id}', name: 'admin_preorder_delete')]
  #[IsGranted('ROLE_ADMIN')]
  public function deletePreorder(Preorder $preorder, PreorderRepository $repo, EntityManagerInterface $manager) {
    $manager->remove($preorder);
    $manager->flush();

    $this->addFlash(
      'success',
      "Le compte a été supprimé !"
    );

    return $this->redirectToRoute("admin_preorder_index");
  }
}