<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Transaction;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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

    #[Route('/delete/{id}', name: 'admin_transaction_delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Transaction $transaction, EntityManagerInterface $manager): Response
    {
        if ($transaction->getInvoice()) {
            $manager->remove($transaction->getInvoice());
        }

        $manager->remove($transaction);
        $manager->flush();

        $this->addFlash(
            'success',
            'La transaction a été supprimée !'
        );

        $referer = $request->headers->get('referer');

        return $this->redirect($referer);
    }
}
