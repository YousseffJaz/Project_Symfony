<?php

namespace App\Controller\Admin;

use App\Entity\Flux;
use App\Form\AdminCashflowType;
use App\Repository\FluxRepository;
use App\Repository\OrderRepository;
use App\Repository\NoteRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class AdminCashFlowController extends AbstractController
{
  /**
   * Permet d'afficher les cashflows
   *
   * @Route("/admin/cashflow", name="admin_cashflow_index")
   * @Security("is_granted('ROLE_ADMIN')")
   */
  public function index(FluxRepository $cashflowRepo, OrderRepository $orderRepo, NoteRepository $noteRepo) {
    $cashflows = $cashflowRepo->findBy([ 'type' => 0 ], ['createdAt' => "DESC"]);
    $notes = $noteRepo->findAll();
    $notPaid = 0;
    $orders = $orderRepo->findByNotNote();

    if ($notes) {
      foreach ($notes as $note) {
        foreach ($note->getTransactions() as $key => $transaction) {
          if ($key == 0) {
            $notPaid = $notPaid + $transaction->getAmount();
          } elseif ($transaction->getInvoice()) {
            $remaining = $transaction->getInvoice()->getTotal() - $transaction->getInvoice()->getPaid();
            $notPaid = $notPaid + $remaining;
          } elseif ($transaction->getAmount() > 0) {
            $notPaid = $notPaid + $transaction->getAmount();
          }
        }
      }
    }

    foreach ($orders as $item) {
      $notPaid = $notPaid + ($item->getTotal() - $item->getPaid());
    }

    return $this->render('admin/cashflow/index.html.twig', [
      'cashflows' => $cashflows,
      'notPaid' => $notPaid,
    ]);
  }


   /**
   * Permet d'ajouter un cashflow
   *
   * @Route("/admin/cashflow/new", name="admin_cashflow_new")
   * @Security("is_granted('ROLE_ADMIN')")
   * 
   */
   public function new(Request $request, ObjectManager $manager) {
    $cashflow = new Flux();
    $form = $this->createForm(AdminCashflowType::class, $cashflow);
    $form->handleRequest($request);

    if($form->isSubmitted() && $form->isValid()) {
      $cashflow->setType(0);
      $manager->persist($cashflow);
      $manager->flush();

      $this->addFlash(
        'success',
        "Un nouveau compte à été ajouté !"
      );

      return $this->redirectToRoute('admin_cashflow_index');
    }

    return $this->render('admin/cashflow/new.html.twig', [
      'form' => $form->createView()
    ]);
  }



   /**
   * Permet d'éditer un cashflow
   *
   * @Route("/admin/cashflow/edit/{id}", name="admin_cashflow_edit")
   * @Security("is_granted('ROLE_ADMIN')")
   * 
   */
   public function edit(Flux $cashflow, Request $request, ObjectManager $manager) {
    $form = $this->createForm(AdminCashflowType::class, $cashflow);
    $form->handleRequest($request);

    if($form->isSubmitted() && $form->isValid()) {
      $manager->flush();

      $this->addFlash(
        'success',
        "Le compte a été modifié !"
      );

      return $this->redirectToRoute('admin_cashflow_index');
    }

    return $this->render('admin/cashflow/edit.html.twig', [
      'form' => $form->createView(),
      'cashflow' => $cashflow
    ]);
  }


  /**
   * Permet de supprimer un cashflow
   *
   * @Route("/admin/cashflow/delete/{id}", name="admin_cashflow_delete")
   * @Security("is_granted('ROLE_ADMIN')")
   */
  public function deleteFlux(Flux $cashflow, ObjectManager $manager)
  {
    $manager->remove($cashflow);
    $manager->flush();

    $this->addFlash(
      'success',
      "Le compte a été supprimé !"
    );

    return $this->redirectToRoute("admin_cashflow_index");
  }
}