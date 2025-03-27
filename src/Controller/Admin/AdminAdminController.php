<?php

namespace App\Controller\Admin;

use App\Entity\Admin;
use App\Form\AdminRegistrationType;
use App\Form\AdminAdminType;
use App\Repository\StockListRepository;
use App\Repository\AdminRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\Admin\AdminArchiveService;

class AdminAdminController extends AbstractController
{
    #[Route('/admin/admin', name: 'admin_admin_index')]
    #[IsGranted('ROLE_SUPER_ADMIN', message: "Vous n'avez pas le droit d'accéder à cette page")]
    public function index(AdminRepository $adminRepo): Response
    {
        $admins = $adminRepo->findBy(['archive' => false]);

        return $this->render('admin/admin/index.html.twig', [
            'admins' => $admins
        ]);
    }

    #[Route('/admin/admin/new', name: 'admin_admin_new')]
    #[IsGranted('ROLE_SUPER_ADMIN', message: "Vous n'avez pas le droit d'accéder à cette page")]
    public function new(Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $passwordHasher, StockListRepository $stockRepo): Response
    {
        $admin = new Admin();
        $form = $this->createForm(AdminRegistrationType::class, $admin);
        $form->handleRequest($request);
        $stocks = $stockRepo->findAllStockName();

        if($form->isSubmitted() && $form->isValid()) {
            $stockList = $request->request->get('stockList');
            $hash = $passwordHasher->hashPassword($admin, $admin->getHash());
            $admin->setHash($hash);

            if ($stockList) {
                $admin->setStockList($stockList);
            }

            $manager->persist($admin);
            $manager->flush();

            $this->addFlash('success', "Un nouveau administrateur à été ajouté !");

            return $this->redirectToRoute('admin_admin_index');
        }

        return $this->render('admin/admin/new.html.twig', [
            'form' => $form->createView(),
            'stocks' => $stocks,
        ]);
    }

    #[Route('/admin/admin/edit/{id}', name: 'admin_admin_edit')]
    #[IsGranted('ROLE_SUPER_ADMIN', message: "Vous n'avez pas le droit d'accéder à cette page")]
    public function edit(Admin $admin, Request $request, EntityManagerInterface $manager, StockListRepository $stockRepo, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(AdminAdminType::class, $admin);
        $form->handleRequest($request);
        $stocks = $stockRepo->findAllStockName();

        if($form->isSubmitted() && $form->isValid()) {
            $stockList = $request->request->get('stockList');

            if ($admin->getHash()) {
                $hash = $passwordHasher->hashPassword($admin, $admin->getHash());
                $admin->setHash($hash);
            }

            if ($stockList) {
                $admin->setStockList($stockList);
            }
            
            $manager->flush();

            $this->addFlash('success', "L'administrateur '{$admin->getFirstName()}' a été modifié !");

            return $this->redirectToRoute("admin_admin_index");
        }

        return $this->render('admin/admin/edit.html.twig', [
            'stocks' => $stocks,
            'admin' => $admin,
            'form' => $form->createView()
        ]);
    }

    #[Route('/admin/admin/archive/{id}', name: 'admin_admin_archive')]
    #[IsGranted('ROLE_SUPER_ADMIN', message: "Vous n'avez pas le droit d'accéder à cette page")]
    public function archive(
        Admin $admin,
        AdminArchiveService $archiveService
    ): Response {
        try {
            if (!$archiveService->canBeArchived($admin)) {
                throw new \RuntimeException("Cet administrateur ne peut pas être archivé actuellement.");
            }

            $archiveService->archiveAdmin($admin);

            $this->addFlash('success', "L'administrateur a été archivé avec succès.");
        } catch (\RuntimeException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('admin_admin_index');
    }
}