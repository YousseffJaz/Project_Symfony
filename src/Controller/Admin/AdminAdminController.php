<?php

namespace App\Controller\Admin;

use App\Entity\Admin;
use App\Form\AdminRegistrationType;
use App\Form\AdminAdminType;
use App\Repository\PriceListRepository;
use App\Repository\StockListRepository;
use App\Repository\AdminRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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
    public function new(Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $passwordHasher, PriceListRepository $priceRepo, StockListRepository $stockRepo): Response
    {
        $admin = new Admin();
        $form = $this->createForm(AdminRegistrationType::class, $admin);
        $form->handleRequest($request);
        $prices = $priceRepo->findPriceListName();
        $stocks = $stockRepo->findAllStockName();

        if($form->isSubmitted() && $form->isValid()) {
            $priceList = $request->request->get('priceList');
            $stockList = $request->request->get('stockList');
            $hash = $passwordHasher->hashPassword($admin, $admin->getHash());
            $admin->setHash($hash);

            if ($priceList) {
                $admin->setPriceList($priceList);
            }

            if ($stockList) {
                $admin->setStockList($stockList);
            }

            $manager->persist($admin);
            $manager->flush();

            $this->addFlash(
                'success',
                "Un nouveau administrateur à été ajouté !"
            );

            return $this->redirectToRoute('admin_admin_index');
        }

        return $this->render('admin/admin/new.html.twig', [
            'form' => $form->createView(),
            'prices' => $prices,
            'stocks' => $stocks,
        ]);
    }

    #[Route('/admin/admin/edit/{id}', name: 'admin_admin_edit')]
    #[IsGranted('ROLE_SUPER_ADMIN', message: "Vous n'avez pas le droit d'accéder à cette page")]
    public function edit(Admin $admin, Request $request, EntityManagerInterface $manager, PriceListRepository $priceRepo, StockListRepository $stockRepo, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(AdminAdminType::class, $admin);
        $form->handleRequest($request);
        $prices = $priceRepo->findPriceListName();
        $stocks = $stockRepo->findAllStockName();

        if($form->isSubmitted() && $form->isValid()) {
            $stockList = $request->request->get('stockList');
            $priceList = $request->request->get('priceList');

            if ($admin->getHash()) {
                $hash = $passwordHasher->hashPassword($admin, $admin->getHash());
                $admin->setHash($hash);
            }

            if ($priceList) {
                $admin->setPriceList($priceList);
            }

            if ($stockList) {
                $admin->setStockList($stockList);
            }
            
            $manager->flush();

            $this->addFlash(
                'success',
                "L'administrateur '{$admin->getFirstName()}' a été modifié !"
            );

            return $this->redirectToRoute("admin_admin_index");
        }

        return $this->render('admin/admin/edit.html.twig', [
            'prices' => $prices,
            'stocks' => $stocks,
            'admin' => $admin,
            'form' => $form->createView()
        ]);
    }

    #[Route('/admin/admin/archive/{id}', name: 'admin_admin_archive')]
    #[IsGranted('ROLE_SUPER_ADMIN', message: "Vous n'avez pas le droit d'accéder à cette page")]
    public function archive(Admin $admin, Request $request, EntityManagerInterface $manager): Response
    {
        $admin->setArchive(true);
        $email = $this->randomString(10) . "@gmail.com";

        $admin->setEmail($email);
        $admin->setHash("$2y$13$9LylwaPvvQbbrggFEZ3thgvdfaDgeoEIgd7TPpPJbrVghyKeBvgly");
        $manager->flush();

        $this->addFlash(
            'success',
            "L'administrateur a été supprimé !"
        );

        return $this->redirectToRoute('admin_admin_index');
    }

    private function randomString(int $length): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}