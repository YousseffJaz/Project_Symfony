<?php

namespace App\Controller\Admin;

use App\Entity\Admin;
use App\Entity\PasswordUpdate;
use App\Form\PasswordUpdateType;
use App\Form\AdminAccountType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Form\UserType;

class AdminAccountController extends AbstractController
{
    #[Route('/', name: 'admin_account_login')]
    public function login(AuthenticationUtils $utils): Response
    {
        $error = $utils->getLastAuthenticationError();
        $username = $utils->getLastUsername();

        return $this->render('admin/account/login.html.twig', [
            'hasError' => $error !== null,
            'username' => $username
        ]);
    }

    #[Route('/logout', name: 'admin_account_logout')]
    public function logout(): void
    {
        // This method can be empty - it will be intercepted by the logout key on your firewall
    }

    #[Route('/admin/profile', name: 'admin_profile')]
    #[IsGranted('ROLE_ADMIN')]
    public function profile(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        /** @var Admin $admin */
        $admin = $this->getUser();
        $passwordUpdate = new PasswordUpdate();

        $formPassword = $this->createForm(PasswordUpdateType::class, $passwordUpdate);
        $form = $this->createForm(AdminAccountType::class, $admin);
        $form->handleRequest($request);
        $formPassword->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($admin);
            $entityManager->flush();

            $this->addFlash(
                'success',
                "Les données du profil ont été modifiées avec succès !"
            );
        }

        if ($formPassword->isSubmitted() && $formPassword->isValid()) {
            $newPassword = $passwordUpdate->getNewPassword();
            $hash = $passwordHasher->hashPassword($admin, $newPassword);

            $admin->setHash($hash);
            $entityManager->persist($admin);
            $entityManager->flush();

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
