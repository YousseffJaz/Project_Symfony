<?php

namespace App\Controller\Admin;

use App\Entity\Flux;
use App\Form\AdminExpenseType;
use App\Repository\TransactionRepository;
use App\Repository\FluxRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/expense')]
class AdminExpenseController extends AbstractController
{
    #[Route('', name: 'admin_expense_index')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(Request $request, FluxRepository $expenseRepo, TransactionRepository $transactionRepo): Response
    {
        $expenses = $expenseRepo->findBy(['type' => true], ['createdAt' => "DESC"]);

        return $this->render('admin/expense/index.html.twig', [
            'expenses' => $expenses,
        ]);
    }

    #[Route('/new', name: 'admin_expense_new')]
    #[IsGranted('ROLE_ADMIN')]
    public function new(FluxRepository $expenseRepo, Request $request, EntityManagerInterface $manager): Response
    {
        $expense = new Flux();
        $form = $this->createForm(AdminExpenseType::class, $expense);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $expense->setType(true);
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

    #[Route('/edit/{id}', name: 'admin_expense_edit')]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Flux $expense, FluxRepository $expenseRepo, Request $request, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(AdminExpenseType::class, $expense);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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

    #[Route('/delete/{id}', name: 'admin_expense_delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Flux $expense, FluxRepository $repo, EntityManagerInterface $manager): Response
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