<?php

namespace App\Controller\Admin;

use App\Entity\Flux;
use App\Form\AdminExpenseType;
use App\Repository\TransactionRepository;
use App\Repository\FluxRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class AdminExpenseController extends AbstractController
{
  /**
   * Permet d'afficher les dépenses
   *
   * @Route("/admin/expense", name="admin_expense_index")
   * @Security("is_granted('ROLE_ADMIN')")
   */
  public function index(Request $request, FluxRepository $expenseRepo, TransactionRepository $transactionRepo) {
    $expenses = $expenseRepo->findBy([ 'type' => 1 ], ['createdAt' => "DESC"]);

    return $this->render('admin/expense/index.html.twig', [
      'expenses' => $expenses,
    ]);
  }


   /**
   * Permet d'ajouter une dépense
   *
   * @Route("/admin/expense/new", name="admin_expense_new")
   * @Security("is_granted('ROLE_ADMIN')")
   * 
   */
   public function new(FluxRepository $expenseRepo, Request $request, ObjectManager $manager) {
    $expense = new Flux();
    $form = $this->createForm(AdminExpenseType::class, $expense);
    $form->handleRequest($request);

    if($form->isSubmitted() && $form->isValid()) {
      $expense->setType(1);
      $manager->persist($expense);
      $manager->flush();

      $this->addFlash(
        'success',
        "Une nouvelle dépense à été ajoutée !"
      );

      return $this->redirectToRoute('admin_expense_index');
    }

    return $this->render('admin/expense/new.html.twig', [
      'form' => $form->createView()
    ]);
  }


   /**
   * Permet d'éditer une expense
   *
   * @Route("/admin/expense/edit/{id}", name="admin_expense_edit")
   * @Security("is_granted('ROLE_ADMIN')")
   * 
   */
   public function edit(Flux $expense, FluxRepository $expenseRepo, Request $request, ObjectManager $manager) {
    $form = $this->createForm(AdminExpenseType::class, $expense);
    $form->handleRequest($request);

    if($form->isSubmitted() && $form->isValid()) {
      $manager->flush();

      $this->addFlash(
        'success',
        "La dépense a été modifiée !"
      );

      return $this->redirectToRoute('admin_expense_index');
    }

    return $this->render('admin/expense/edit.html.twig', [
      'form' => $form->createView(),
      'expense' => $expense
    ]);
  }


  /**
   * Permet de supprimer une dépense
   *
   * @Route("/admin/expense/delete/{id}", name="admin_expense_delete")
   * @Security("is_granted('ROLE_ADMIN')")
   */
  public function delete(Flux $expense, FluxRepository $repo, ObjectManager $manager)
  {
    $manager->remove($expense);
    $manager->flush();

    $this->addFlash(
      'success',
      "La dépense a été supprimée !"
    );

    return $this->redirectToRoute("admin_expense_index");
  }
}