<?php

namespace App\Controller\Admin;

use App\Entity\Folder;
use App\Entity\Upload;
use App\Repository\FolderRepository;
use App\Form\AdminFolderType;
use App\Repository\UploadRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class AdminFolderController extends AbstractController
{
  /**
   * Permet d'afficher les dossiers clients
   *
   * @Route("/admin/folders", name="admin_folder_index")
   * @Security("is_granted('ROLE_ADMIN')")
   */
  public function index(Request $request, FolderRepository $folderRepo) {
    $folders = $folderRepo->findBy(["type" => 0], ['name' => "ASC"]);

    return $this->render('admin/folder/index.html.twig', [
      'folders' => $folders,
      'type' => 0
    ]);
  }
  
  /**
   * Permet d'afficher les dossiers collections
   *
   * @Route("/admin/folders/collections", name="admin_folder_collections")
   * @Security("is_granted('ROLE_ADMIN')")
   */
  public function collections(Request $request, FolderRepository $folderRepo) {
    $folders = $folderRepo->findBy(["type" => 1], ['name' => "ASC"]);

    return $this->render('admin/folder/index.html.twig', [
      'folders' => $folders,
      'type' => 1
    ]);
  }
  
  /**
   * Permet de rechercher un dossier clients
   *
   * @Route("/admin/folders/search", name="admin_folder_search")
   * @Security("is_granted('ROLE_ADMIN')")
   */
  public function search(Request $request, FolderRepository $folderRepo) {
    return $this->render('admin/folder/search.html.twig');
  }
  

  /**
   * Permet de rechercher un dossier collections
   *
   * @Route("/admin/folders/search/files", name="admin_folder_files")
   * @Security("is_granted('ROLE_ADMIN')")
   */
  public function files(Request $request, FolderRepository $folderRepo, UploadRepository $uploadRepo) {
    $search = $request->query->get('search');
    $uploads = $uploadRepo->search($search); $array = [];

    if ($uploads) {
      return $this->json($uploads, 200);
    }

    return $this->json(false, 404);
  }


   /**
   * Permet d'ajouter un dossier dans clients
   *
   * @Route("/admin/folders/new", name="admin_folder_new")
   * @Security("is_granted('ROLE_ADMIN')")
   * 
   */
   public function new(Request $request, ObjectManager $manager) {
    return $this->render('admin/folder/new.html.twig', [ 'type' => 0]);
  }


   /**
   * Permet d'ajouter un dossier dans collections
   *
   * @Route("/admin/folders/collections/new", name="admin_folder_collections_new")
   * @Security("is_granted('ROLE_ADMIN')")
   * 
   */
   public function new2(Request $request, ObjectManager $manager) {
    return $this->render('admin/folder/new.html.twig', [ 'type' => 1]);
  }



  /**
   * Permet d'éditer un dossier
   *
   * @Route("/admin/folders/edit/{id}", name="admin_folder_edit")
   * @Security("is_granted('ROLE_ADMIN')")
   * 
   */
  public function edit(Folder $folder, Request $request, ObjectManager $manager) {
    $form = $this->createForm(AdminFolderType::class, $folder);
    $form->handleRequest($request);

    if($form->isSubmitted() && $form->isValid()) {
      $manager->flush();

      $this->addFlash(
        'success',
        "Le dossier est modifié !"
      );
    }


    return $this->render('admin/folder/edit.html.twig', [
      'folder' => $folder,
      'form' => $form->createView()
    ]);
  }


  /**
   * Permet d'uploader un fichier dans un dossier
   *
   * @Route("/admin/folder/upload", name="admin_folder_upload")
   * @Security("is_granted('ROLE_ADMIN')")
   */
  public function upload(Request $request, ObjectManager $manager, FolderRepository $folderRepo, UploadRepository $uploadRepo) {
    $file = $request->files->get('file');
    $folder = $request->request->get('folder');

    if (!$file) {
      return $this->json("Le fichier est introuvable !", 404);
    }

    if (!$folder) {
      return $this->json("Le dossier est introuvable !", 404);
    } else {
      $folder = $folderRepo->findOneById($folder);
    }

    $upload = $uploadRepo->findOneByFilename($file->getClientOriginalName());

    if ($upload) {
      return $this->json("Le fichier existe déjà !", 404);
    }

    $filepath = $this->getParameter('uploads_directory') . '/';
    $file->move($filepath, $file->getClientOriginalName());

    $upload = new Upload();
    $upload->setFilename($file->getClientOriginalName());
    $upload->setName($file->getClientOriginalName());

    if ($folder) {
      $upload->setFolder($folder);
    }

    $manager->persist($upload);
    $manager->flush();

    $array = [ 'id' => $upload->getId(), 'filename' => $upload->getFilename() ];

    return $this->json($array);
  }


  /**
   * Permet de renommer un fichier
   *
   * @Route("/admin/folder/rename/{id}", name="admin_folder_rename")
   * @Security("is_granted('ROLE_ADMIN')")
   */
  public function rename(Upload $upload, Request $request, ObjectManager $manager, FolderRepository $folderRepo, UploadRepository $uploadRepo) {
    $text = $request->query->get('text');

    if ($text) {
      $upload->setName($text);
      $manager->flush();

      return $this->json(true);
    }

    return $this->json(false);
  }



  /**
   * Permet d'uploader des dossiers dans clients
   *
   * @Route("/admin/folder/uploads/all", name="admin_folder_uploads_all")
   * @Security("is_granted('ROLE_ADMIN')")
   */
  public function uploadsAll(Request $request, ObjectManager $manager, FolderRepository $folderRepo, UploadRepository $uploadRepo) {
    $name = $request->request->get('name');
    $file = $request->files->get('file');

    if (!$file) {
      return $this->json("Le fichier est introuvable !", 404);
    }

    if (!$name) {
      return $this->json("Il n'y a pas de dossier !", 404);
    }

      // $upload = $uploadRepo->findOneByFilename($file->getClientOriginalName());

      // if ($upload) {
      //     return $this->json("Le fichier existe déjà !", 404);
      // }

    $filepath = $this->getParameter('uploads_directory') . '/';
    $file->move($filepath, $file->getClientOriginalName());

    $upload = new Upload();
    $upload->setFilename($file->getClientOriginalName());
    $upload->setName($file->getClientOriginalName());

    $folder = $folderRepo->findOneByName($name);

    if (!$folder) {
      $folder = new Folder();
      $folder->setName($name);
      $folder->setType(0);
      $manager->persist($folder);
    }

    $upload->setFolder($folder);
    $manager->persist($upload);
    $manager->flush();

    return $this->json($file->getClientOriginalName());
  }



  /**
   * Permet d'uploader des dossiers dans collections
   *
   * @Route("/admin/folder/collections/uploads/all", name="admin_folder_uploads_collections_all")
   * @Security("is_granted('ROLE_ADMIN')")
   */
  public function uploadsAll2(Request $request, ObjectManager $manager, FolderRepository $folderRepo, UploadRepository $uploadRepo) {
    $name = $request->request->get('name');
    $file = $request->files->get('file');

    if (!$file) {
      return $this->json("Le fichier est introuvable !", 404);
    }

    if (!$name) {
      return $this->json("Il n'y a pas de dossier !", 404);
    }

      // $upload = $uploadRepo->findOneByFilename($file->getClientOriginalName());

      // if ($upload) {
      //     return $this->json("Le fichier existe déjà !", 404);
      // }

    $filepath = $this->getParameter('uploads_directory') . '/';
    $file->move($filepath, $file->getClientOriginalName());

    $upload = new Upload();
    $upload->setFilename($file->getClientOriginalName());
    $upload->setName($file->getClientOriginalName());

    $folder = $folderRepo->findOneByName($name);

    if (!$folder) {
      $folder = new Folder();
      $folder->setName($name);
      $folder->setType(1);
      $manager->persist($folder);
    }

    $upload->setFolder($folder);
    $manager->persist($upload);
    $manager->flush();

    return $this->json($file->getClientOriginalName());
  }


  /**
   * Permet de supprimer un fichier
   *
   * @Route("/admin/folder/upload/delete", name="admin_folder_upload_delete")
   * @Security("is_granted('ROLE_ADMIN')")
   */
  public function deleteUpload(Request $request, ObjectManager $manager, UploadRepository $repo) {
    $filename = $request->query->get('filename');

    if ($filename) {
      $upload = $repo->findOneByFilename($filename);
      $manager->remove($upload);
      $manager->flush();
    }

    return $this->json(true, 200);
  }


  /**
   * Permet de supprimer un dossier
   *
   * @Route("/admin/folders/delete/{id}", name="admin_folder_delete")
   * @Security("is_granted('ROLE_ADMIN')")
   */
  public function deleteFolder(Folder $folder, ObjectManager $manager) {
    if ($folder->getUpload()) {
      foreach ($folder->getUpload() as $upload) {
        $manager->remove($upload);
      }   
    }

    $manager->remove($folder);
    $manager->flush();

    $this->addFlash(
      'success',
      "Le dossier a été supprimée !"
    );

    return $this->redirectToRoute("admin_folder_index");
  }
}