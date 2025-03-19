<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\AdminUserType;
use App\Repository\UserRepository;
use App\Repository\InfoRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/admin/users')]
class AdminUserController extends AbstractController
{
    #[Route('', name: 'admin_user_index')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(UserRepository $userRepo): Response
    {
        $users = $userRepo->findBy([], ["createdAt" => "DESC"]);

        return $this->render('admin/user/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/add', name: 'admin_user_add')]
    public function addUser(Request $request, EntityManagerInterface $manager, UserRepository $userRepo, UserPasswordHasherInterface $hasher): Response
    {
        if ($content = $request->getContent()) {
            $param = json_decode($content, true);

            if ($param) {
                $user = $userRepo->findOneByPseudo($param['username']);

                if (!$user) {
                    $user = new User();
                    $user->setPseudo($param['username']);
                    if ($user instanceof \Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface) {
                        $hash = $hasher->hashPassword($user, $param['password']);
                        $user->setHash($hash);
                    } else {
                        return $this->json("Erreur de configuration de l'utilisateur", 500);
                    }

                    if (isset($param['phone'])) {
                        $user->setPhone($param['phone']);
                    }

                    if (isset($param['pushToken'])) {
                        $user->setPushToken($param['pushToken']);
                    }

                    $manager->persist($user);
                    $manager->flush();

                    return $this->json($user, 200);
                } else {
                    return $this->json("Un utilisateur existe avec ce pseudo", 404);
                }
            }
        }

        return $this->json("Une erreur est survenue", 404);
    }

    #[Route('/edit/{id}', name: 'admin_user_edit')]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(User $user, Request $request, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(AdminUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->flush();

            $this->addFlash(
                'success',
                "L'utilisateur '{$user->getPseudo()}' a été modifié !"
            );

            return $this->redirectToRoute("admin_user_index");
        }

        return $this->render('admin/user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    #[Route('/push/{id}', name: 'admin_push_device_user')]
    #[IsGranted('ROLE_ADMIN')]
    public function pushDevice(User $user): Response
    {
        if ($user->getPushToken()) {
            return $this->json(['token' => $user->getPushToken()], 200);
        }
        return $this->json(['message' => "L'utilisateur n'a pas de token push"], 404);
    }

    #[Route('/note/{id}', name: 'admin_user_note')]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function note(Request $request, EntityManagerInterface $manager, User $user): Response
    {
        if ($content = $request->getContent()) {
            $param = json_decode($content, true);
            if ($param) {
                $user->setNote($param['note']);
                $manager->flush();
                return $this->json($user, 200);
            }
        }
        return $this->json("Une erreur est survenue", 404);
    }

    #[Route('/delete/{id}', name: 'admin_user_delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(User $user, EntityManagerInterface $manager): Response
    {
        $manager->remove($user);
        $manager->flush();

        $this->addFlash(
            'success',
            "L'utilisateur a été supprimé !"
        );

        return $this->redirectToRoute('admin_user_index');
    }
}