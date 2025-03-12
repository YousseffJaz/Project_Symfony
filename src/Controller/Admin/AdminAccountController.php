<?php

namespace App\Controller\Admin;

use App\Entity\PasswordUpdate;
use App\Form\PasswordUpdateType;
use App\Form\AdminAccountType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class AdminAccountController extends AbstractController
{
  /**
   * @Route("/", name="admin_account_login")
   */
  public function login(AuthenticationUtils $utils)
  {
    $error = $utils->getLastAuthenticationError();
    $username = $utils->getLastUsername();

    return $this->render('admin/account/login.html.twig', [
      'hasError' => $error !== null,
      'username' => $username
    ]);
  }

  /**
   * @Route("/logout", name="admin_account_logout")
   */
  public function logout() {
      // ...
  }


  /**
   * Permet de modifier le profil
   *
   * @Route("admin/account/profile", name="admin_account_profile")
   * @Security("is_granted('ROLE_ADMIN')")
   * 
   */
  public function profile(Request $request, ObjectManager $manager, UserPasswordEncoderInterface $encoder) {
    $admin = $this->getUser();
    $passwordUpdate = new PasswordUpdate();

    $formPassword = $this->createForm(PasswordUpdateType::class, $passwordUpdate);
    $form = $this->createForm(AdminAccountType::class, $admin);

    $form->handleRequest($request);
    $formPassword->handleRequest($request);

    if($form->isSubmitted() && $form->isValid()) {
      $manager->persist($admin);
      $manager->flush();

      $this->addFlash(
        'success',
        "Les données du profil ont été modifiées avec succès !"
      );
    }

    if($formPassword->isSubmitted() && $formPassword->isValid()) {
      $newPassword = $passwordUpdate->getNewPassword();
      $hash = $encoder->encodePassword($admin, $newPassword);

      $admin->setHash($hash);
      $manager->persist($admin);
      $manager->flush();

      $this->addFlash(
        'success',
        "Votre mot de passe a bien été modifié !"
      );
    }  

    return $this->render('admin/account/profile.html.twig', [
      'form' => $form->createView(),
      'formPassword' => $formPassword->createView(),
    ]);
  }
}
