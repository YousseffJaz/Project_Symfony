<?php

namespace App\Controller\Admin;

use App\Entity\Transaction;
use App\Entity\Note;
use App\Form\AdminNoteType;
use App\Repository\TransactionRepository;
use App\Repository\ProductRepository;
use App\Repository\NoteRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class AdminNoteController extends AbstractController
{
  /**
   * Permet d'afficher les notes
   *
   * @Route("/admin/notes", name="admin_note_index")
   * @Security("is_granted('ROLE_ADMIN')")
   */
  public function index(NoteRepository $noteRepo) {
    $notes = $noteRepo->findAll();

    return $this->render('admin/note/index.html.twig', [
      'notes' => $notes,
    ]);
  }


   /**
   * Permet d'ajouter une note
   *
   * @Route("/admin/notes/new", name="admin_note_new")
   * @Security("is_granted('ROLE_ADMIN')")
   * 
   */
   public function new(Request $request, ObjectManager $manager) {
    $note = new Note();
    $form = $this->createForm(AdminNoteType::class, $note);
    $form->handleRequest($request);

    if($form->isSubmitted() && $form->isValid()) {
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



   /**
   * Permet d'éditer une note
   *
   * @Route("/admin/notes/edit/{id}", name="admin_note_edit")
   * @Security("is_granted('ROLE_ADMIN')")
   * 
   */
   public function edit(Note $note, Request $request, ObjectManager $manager) {
    $form = $this->createForm(AdminNoteType::class, $note);
    $form->handleRequest($request);

    if($form->isSubmitted() && $form->isValid()) {
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


  /**
   * Permet de supprimer un note
   *
   * @Route("/admin/notes/delete/{id}", name="admin_note_delete")
   * @Security("is_granted('ROLE_ADMIN')")
   */
  public function deleteNote(Note $note, ObjectManager $manager) {
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