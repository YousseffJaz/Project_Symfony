<?php

namespace App\Controller\Admin;

use App\Entity\Admin;
use App\Form\AdminRegistrationType;
use App\Form\AdminAdminType;
use App\Repository\PriceListRepository;
use App\Repository\StockListRepository;
use App\Repository\AdminRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class AdminAdminController extends AbstractController
{
  /**
   * Permet d'afficher les administrateurs
   *
   * @Route("/admin/admin", name="admin_admin_index")
   * @Security("user.getRole() == 'ROLE_SUPER_ADMIN'", message="Vous n'avez pas le droit d'accéder à cette page")
   */
  public function index(AdminRepository $adminRepo) {
    $admins = $adminRepo->findBy(['archive' => false]);

    return $this->render('admin/admin/index.html.twig', [
      'admins' => $admins
    ]);
  }


   /**
   * Permet d'ajouter un administrateur
   *
   * @Route("/admin/admin/new", name="admin_admin_new")
   * @Security("user.getRole() == 'ROLE_SUPER_ADMIN'", message="Vous n'avez pas le droit d'accéder à cette page")
   * 
   */
   public function new(Request $request, ObjectManager $manager, UserPasswordEncoderInterface $encoder, PriceListRepository $priceRepo, StockListRepository $stockRepo) {
    $admin = new Admin();
    $form = $this->createForm(AdminRegistrationType::class, $admin);
    $form->handleRequest($request);
    $prices = $priceRepo->findPriceListName();
    $stocks = $stockRepo->findAllStockName();

    if($form->isSubmitted() && $form->isValid()) {
      $priceList = $request->request->get('priceList');
      $stockList = $request->request->get('stockList');
      $hash = $encoder->encodePassword($admin, $admin->getHash());
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


  /**
   * Permet de modifier un administrateur
   *
   * @Route("/admin/admin/edit/{id}", name="admin_admin_edit")
   * @Security("user.getRole() == 'ROLE_SUPER_ADMIN'", message="Vous n'avez pas le droit d'accéder à cette page")
   * 
   */
  public function edit(Admin $admin, Request $request, ObjectManager $manager, PriceListRepository $priceRepo, StockListRepository $stockRepo, UserPasswordEncoderInterface $encoder) {

    $form = $this->createForm(AdminAdminType::class, $admin);
    $form->handleRequest($request);
    $prices = $priceRepo->findPriceListName();
    $stocks = $stockRepo->findAllStockName();

    if($form->isSubmitted() && $form->isValid()) {
      $stockList = $request->request->get('stockList');
      $priceList = $request->request->get('priceList');

      if ($admin->getHash()) {
        $hash = $encoder->encodePassword($admin, $admin->getHash());
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

  /**
   * Permet de supprimer un administrateur
   *
   * @Route("/admin/admin/archive/{id}", name="admin_admin_archive")
   * @Security("user.getRole() == 'ROLE_SUPER_ADMIN'", message="Vous n'avez pas le droit d'accéder à cette page")
   * 
   */
  public function archive(Admin $admin, Request $request, ObjectManager $manager) {
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

  private function randomString(int $length) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
      $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
  }
}