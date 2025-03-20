<?php

namespace App\Controller\Admin;

use App\Entity\Task;
use App\Entity\Admin;
use App\Form\AdminTaskType;
use App\Repository\TaskRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/task')]
class AdminTaskController extends AbstractController
{
    private function getAdmin(): Admin
    {
        /** @var Admin */
        return $this->getUser();
    }

    #[Route('', name: 'admin_task_index')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(Request $request, TaskRepository $taskRepo): Response
    {
        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            $tasks = $taskRepo->findBy([], ['complete' => 'ASC', 'createdAt' => 'DESC']);
        } else {
            $tasks = $taskRepo->findBy(['admin' => $this->getUser()], ['complete' => 'ASC', 'createdAt' => 'DESC']);
        }

        return $this->render('admin/task/index.html.twig', [
            'tasks' => $tasks,
        ]);
    }

    #[Route('/new', name: 'admin_task_new')]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $manager): Response
    {
        $task = new Task();
        $form = $this->createForm(AdminTaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $task->setCreatedBy($this->getAdmin());
            $manager->persist($task);
            $manager->flush();

            $this->addFlash(
                'success',
                "Une nouvelle tâche a été ajoutée !"
            );

            return $this->redirectToRoute('admin_task_index');
        }

        return $this->render('admin/task/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/edit/{id}', name: 'admin_task_edit')]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Task $task, TaskRepository $taskRepo, Request $request, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(AdminTaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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

    #[Route('/complete/{id}', name: 'admin_task_complete')]
    #[IsGranted('ROLE_ADMIN')]
    public function complete(Task $task, TaskRepository $taskRepo, Request $request, EntityManagerInterface $manager): Response
    {
        $task->setComplete(true);
        $task->setCompleteBy($this->getAdmin());
        $task->setUpdatedAt(new \DateTime('now', timezone_open('Europe/Paris')));
        $manager->flush();

        $this->addFlash(
            'success',
            "Le message a été validé !"
        );

        return $this->redirectToRoute('admin_task_index');
    }

    #[Route('/delete/{id}', name: 'admin_task_delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteTask(Task $task, TaskRepository $repo, EntityManagerInterface $manager): Response
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