<?php

namespace App\Controller\Admin;


use App\Entity\User;
use App\Form\AdminUserType;
use App\Repository\UserRepository;
use App\Repository\InfoRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class AdminUserController extends AbstractController
{

  /**
   * Permet d'afficher les utilisateurs de l'app
   *
   * @Route("/admin/users", name="admin_user_index")
   * @Security("is_granted('ROLE_ADMIN')")
   */
  public function index(UserRepository $userRepo)
  {
    $users = $userRepo->findBy([], [ "createdAt" => "DESC" ]);

    return $this->render('admin/user/index.html.twig', [
      'users' => $users,
    ]);
  }


  /**
   * Permet d'ajouter un nouvel utilisateur
   *
  * @Route("/user/add", name="admin_user_add")
  */
  public function addUser(Request $request, ObjectManager $manager, UserRepository $userRepo, UserPasswordEncoderInterface $encoder) {

    if ($content = $request->getContent()) {
      $param = json_decode($content, true);

      if ($param) {
        $user = $userRepo->findOneByPseudo($param['username']);

        if (!$user) {
          $user = new User();
          $user->setPseudo($param['username']);
          $hash = $encoder->encodePassword($user, $param['password']);
          $user->setHash($hash);

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


  /**
   * Permet de modifier un utilisateur
   *
   * @Route("/admin/users/edit/{id}", name="admin_user_edit")
   * @Security("is_granted('ROLE_ADMIN')")
   * 
   */
  public function edit(User $user, Request $request, ObjectManager $manager) {

    $form = $this->createForm(AdminUserType::class, $user);
    $form->handleRequest($request);

    if($form->isSubmitted() && $form->isValid()) {
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


  /**
   * Permet de mettre une note à un utilisateur
   *
  * @Route("/admin/users/note/{id}", name="admin_user_note")
   * @Security("user.getRole() == 'ROLE_SUPER_ADMIN'", message="Vous n'avez pas le droit d'accéder à cette page")
  */
  public function note(Request $request, ObjectManager $manager, User $user)
  {
    $note = $request->request->get('note');

    $user->setNote($note);
    $manager->flush();

    return $this->json(true);
  }


  /**
   * Permet de supprimer un utilisateur
   *
   * @Route("/admin/users/delete/{id}", name="admin_user_delete")
   * @Security("user.getRole() == 'ROLE_SUPER_ADMIN'", message="Vous n'avez pas le droit d'accéder à cette page")
   */
  public function delete(User $user, ObjectManager $manager)
  {
    $manager->remove($user);
    $manager->flush();

    $this->addFlash(
      'success',
      "L'utilisateur a été supprimé !"
    );

    return $this->redirectToRoute("admin_user_index");
  }
}