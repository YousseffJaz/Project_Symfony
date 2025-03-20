<?php

namespace App\Controller\Admin;

use App\Entity\Transaction;
use App\Entity\Note;
use App\Form\AdminNoteType;
use App\Repository\NoteRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/notes')]
class AdminNoteController extends AbstractController
{
    #[Route('', name: 'admin_note_index')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(NoteRepository $noteRepo): Response
    {
        $notes = $noteRepo->findAll();

        return $this->render('admin/note/index.html.twig', [
            'notes' => $notes,
        ]);
    }

    #[Route('/new', name: 'admin_note_new')]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $manager): Response
    {
        $note = new Note();
        $form = $this->createForm(AdminNoteType::class, $note);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($note);

            $transaction = new Transaction();
            $transaction->setNote($note);
            $transaction->setAmount($note->getAmount());
            $transaction->setComment("Création de la note");
            
            $manager->persist($transaction);
            $manager->flush();

            $this->addFlash(
                'success',
                "Une nouvelle note à été ajouté !"
            );

            return $this->redirectToRoute('admin_note_index');
        }

        return $this->render('admin/note/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/edit/{id}', name: 'admin_note_edit')]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Note $note, Request $request, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(AdminNoteType::class, $note);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->flush();

            $this->addFlash(
                'success',
                "La note a été modifiée !"
            );

            return $this->redirectToRoute('admin_note_index');
        }

        return $this->render('admin/note/edit.html.twig', [
            'form' => $form->createView(),
            'note' => $note
        ]);
    }

    #[Route('/delete/{id}', name: 'admin_note_delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteNote(Note $note, EntityManagerInterface $manager): Response
    {
        if ($note->getTransactions()) {
            foreach ($note->getTransactions() as $transaction) {
                if ($transaction->getInvoice()) {
                    $transaction->setInvoice(null); 
                }
                $manager->remove($transaction);
            }   
        }

        if ($note->getInvoice()) {
            foreach ($note->getInvoice() as $invoice) {
                if ($invoice) {
                    $invoice->setNote2(null);
                    $manager->flush();
                }
            }   
        }

        $manager->remove($note);
        $manager->flush();

        $this->addFlash(
            'success',
            "La note a été supprimée !"
        );

        return $this->redirectToRoute("admin_note_index");
    }
}