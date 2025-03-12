<?php

namespace App\Controller\Admin;

use App\Entity\Note;
use App\Entity\Transaction;
use App\Form\AdminTransactionType;
use App\Repository\ProductRepository;
use App\Repository\TransactionRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class AdminTransactionController extends AbstractController
{
  /**
   * Permet d'afficher les transactions
   *
   * @Route("/admin/transactions", name="admin_transaction_index")
   * @Security("is_granted('ROLE_ADMIN')")
   */
  public function index(Request $request, TransactionRepository $repo)
  {
    $transactions = $repo->findAll();

    return $this->render('admin/transaction/index.html.twig', [
      'transactions' => $transactions,
    ]);
  }


   /**
   * Permet d'ajouter une transaction
   *
   * @Route("/admin/note/{id}/transactions/new", name="admin_transaction_new")
   * @Security("is_granted('ROLE_ADMIN')")
   * 
   */
   public function new(Note $note, Request $request, ObjectManager $manager, TransactionRepository $transactionRepo) {
    $transaction = new Transaction();
    $form = $this->createForm(AdminTransactionType::class, $transaction);
    $form->handleRequest($request);

    if($form->isSubmitted() && $form->isValid()) {
      if($note->getInvoice()->toArray() && $transaction->getAmount() < 0) {
        $tAmount = abs($transaction->getAmount());

        foreach ($note->getInvoice()->toArray()as $key => $invoice) {
          if ($invoice->getStatus() == 0 || $invoice->getStatus() == 1) {
            $iAmount = $invoice->getTotal() - $invoice->getPaid();
            if ($tAmount >= $iAmount) {
              // commande payé entierement
              if ($invoice->getPaid() > 0) {
                $invoice->setPaid($invoice->getPaid() + $iAmount);
              } else {
                $invoice->setPaid($iAmount);
              }
              $invoice->setPaymentMethod(0);
              $invoice->setStatus(2);
              $tAmount = $tAmount - $iAmount;
            } else {
              if ($invoice->getPaid() > 0) {
                $invoice->setPaid($invoice->getPaid() + $tAmount);
              } else {
                $invoice->setPaid($tAmount);
              }
              $invoice->setPaymentMethod(0);
              $invoice->setStatus(1);
              $tAmount = 0;
            }
          }
          if ($key === array_key_last($note->getInvoice()->toArray())) {
            $fTransaction = $transactionRepo->findBy(['note' => $note, 'comment' => "Création de la note"]);

            if ($fTransaction) {
              $fTransaction[0]->setAmount($fTransaction[0]->getAmount() - $tAmount); 
            }
          }
        }
      } else if ($transaction->getAmount() < 0) {
        $fTransaction = $transactionRepo->findBy(['note' => $note, 'comment' => "Création de la note"]);

        if ($fTransaction) {
          $fTransaction[0]->setAmount($fTransaction[0]->getAmount() + $transaction->getAmount()); 
        }
      } 

      $transaction->setNote($note);
      $manager->persist($transaction);
      $manager->flush();

      $this->addFlash(
        'success',
        "Une nouvelle transaction à été ajouté !"
      );

      return $this->redirectToRoute('admin_note_edit', [ 'id' => $note->getId() ]);
    }

    return $this->render('admin/transaction/new.html.twig', [
      'form' => $form->createView()
    ]);
  }


  /**
   * Permet de supprimer une transaction
   *
   * @Route("/admin/transactions/delete/{id}", name="admin_transaction_delete")
   * @Security("is_granted('ROLE_ADMIN')")
   */
  public function delete(Request $request, Transaction $transaction, ObjectManager $manager, TransactionRepository $transactionRepo)
  {
    if ($transaction->getAmount() < 0) {
      $firstTransaction = $transactionRepo->findBy(['note' => $transaction->getNote(), 'comment' => "Création de la note"]);

      if ($firstTransaction) {
        $firstTransaction[0]->setAmount($firstTransaction[0]->getAmount() - $transaction->getAmount()); 
      }
    }

    if ($transaction->getInvoice()) {
      $manager->remove($transaction->getInvoice());
    }


    $manager->remove($transaction);
    $manager->flush();

    $this->addFlash(
      'success',
      "La transaction a été supprimée !"
    );

    $referer = $request->headers->get('referer');
    return $this->redirect($referer);
  }
}