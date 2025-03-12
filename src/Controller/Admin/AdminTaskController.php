<?php

namespace App\Controller\Admin;

use App\Entity\Task;
use App\Form\AdminTaskType;
use App\Repository\TaskRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class AdminTaskController extends AbstractController
{
  /**
   * Permet d'afficher les taches
   *
   * @Route("/admin/task", name="admin_task_index")
   * @Security("is_granted('ROLE_ADMIN')")
   */
  public function index(Request $request, TaskRepository $taskRepo)
  {
    if ($this->getUser()->getRole() == "ROLE_SUPER_ADMIN") {
      $tasks = $taskRepo->findBy([], [ 'complete' => 'ASC', 'createdAt' => 'DESC']);
    } else {
      $tasks = $taskRepo->findBy(['admin' => $this->getUser()], [ 'complete' => 'ASC', 'createdAt' => 'DESC']);
    }

    return $this->render('admin/task/index.html.twig', [
      'tasks' => $tasks,
    ]);
  }


   /**
   * Permet d'ajouter une tache
   *
   * @Route("/admin/task/new", name="admin_task_new")
   * @Security("is_granted('ROLE_ADMIN')")
   * 
   */
   public function new(TaskRepository $taskRepo, Request $request, ObjectManager $manager) {
    $task = new Task();
    $form = $this->createForm(AdminTaskType::class, $task);
    $form->handleRequest($request);

    if($form->isSubmitted() && $form->isValid()) {
      $task->setCreatedBy($this->getUser());
      $manager->persist($task);
      $manager->flush();

      $this->addFlash(
        'success',
        "Un nouveau message à été ajouté !"
      );

      return $this->redirectToRoute('admin_task_index');
    }

    return $this->render('admin/task/new.html.twig', [
      'form' => $form->createView()
    ]);
  }



   /**
   * Permet d'éditer une task
   *
   * @Route("/admin/task/edit/{id}", name="admin_task_edit")
   * @Security("is_granted('ROLE_ADMIN')")
   * 
   */
   public function edit(Task $task, TaskRepository $taskRepo, Request $request, ObjectManager $manager) {
    $form = $this->createForm(AdminTaskType::class, $task);
    $form->handleRequest($request);

    if($form->isSubmitted() && $form->isValid()) {
      $manager->flush();

      $this->addFlash(
        'success',
        "Le message a été modifié !"
      );

      return $this->redirectToRoute('admin_task_index');
    }

    return $this->render('admin/task/edit.html.twig', [
      'form' => $form->createView(),
      'task' => $task
    ]);
  }


   /**
   * Permet de completer une tache
   *
   * @Route("/admin/task/complete/{id}", name="admin_task_complete")
   * @Security("is_granted('ROLE_ADMIN')")
   * 
   */
   public function complete(Task $task, TaskRepository $taskRepo, Request $request, ObjectManager $manager) {
    $task->setComplete(true);
    $task->setCompleteBy($this->getUser());
    $task->setUpdatedAt(new \DateTime('now', timezone_open('Europe/Paris')));
    $manager->flush();

    $this->addFlash(
      'success',
      "Le message a été validé !"
    );

    return $this->redirectToRoute('admin_task_index');
  }


  /**
   * Permet de supprimer une tache
   *
   * @Route("/admin/task/delete/{id}", name="admin_task_delete")
   * @Security("is_granted('ROLE_ADMIN')")
   */
  public function deleteTask(Task $task, TaskRepository $repo, ObjectManager $manager)
  {
    $manager->remove($task);
    $manager->flush();

    $this->addFlash(
      'success',
      "Le message a été supprimé !"
    );

    return $this->redirectToRoute("admin_task_index");
  }
}