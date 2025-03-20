<?php

namespace App\Controller\Admin;

use App\Entity\Note;
use App\Entity\Transaction;
use App\Form\AdminTransactionType;
use App\Repository\TransactionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/transactions')]
class AdminTransactionController extends AbstractController
{
    #[Route('', name: 'admin_transaction_index')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(Request $request, TransactionRepository $repo): Response
    {
        $transactions = $repo->findAll();

        return $this->render('admin/transaction/index.html.twig', [
            'transactions' => $transactions,
        ]);
    }

    #[Route('/note/{id}/transactions/new', name: 'admin_transaction_new')]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Note $note, Request $request, EntityManagerInterface $manager, TransactionRepository $transactionRepo): Response
    {
        $transaction = new Transaction();
        $form = $this->createForm(AdminTransactionType::class, $transaction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($note->getInvoice()->toArray() && $transaction->getAmount() < 0) {
                $tAmount = abs($transaction->getAmount());

                foreach ($note->getInvoice()->toArray() as $key => $invoice) {
                    if ($invoice->getStatus() === 0 || $invoice->getStatus() === 1) {
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
            } elseif ($transaction->getAmount() < 0) {
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

            return $this->redirectToRoute('admin_note_edit', ['id' => $note->getId()]);
        }

        return $this->render('admin/transaction/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/delete/{id}', name: 'admin_transaction_delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Transaction $transaction, EntityManagerInterface $manager, TransactionRepository $transactionRepo): Response
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