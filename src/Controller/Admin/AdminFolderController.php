<?php

namespace App\Controller\Admin;

use App\Entity\Folder;
use App\Entity\Upload;
use App\Repository\FolderRepository;
use App\Form\AdminFolderType;
use App\Repository\UploadRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/folders')]
class AdminFolderController extends AbstractController
{
    #[Route('', name: 'admin_folder_index')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(Request $request, FolderRepository $folderRepo): Response
    {
        $folders = $folderRepo->findBy(["type" => 0], ['name' => "ASC"]);

        return $this->render('admin/folder/index.html.twig', [
            'folders' => $folders,
            'type' => 0
        ]);
    }

    #[Route('/collections', name: 'admin_folder_collections')]
    #[IsGranted('ROLE_ADMIN')]
    public function collections(Request $request, FolderRepository $folderRepo): Response
    {
        $folders = $folderRepo->findBy(["type" => 1], ['name' => "ASC"]);

        return $this->render('admin/folder/index.html.twig', [
            'folders' => $folders,
            'type' => 1
        ]);
    }

    #[Route('/search', name: 'admin_folder_search')]
    #[IsGranted('ROLE_ADMIN')]
    public function search(Request $request, FolderRepository $folderRepo): Response
    {
        return $this->render('admin/folder/search.html.twig');
    }

    #[Route('/search/files', name: 'admin_folder_files')]
    #[IsGranted('ROLE_ADMIN')]
    public function files(Request $request, FolderRepository $folderRepo, UploadRepository $uploadRepo): Response
    {
        $search = $request->query->get('search');
        $uploads = $uploadRepo->search($search);
        $array = [];

        if ($uploads) {
            return $this->json($uploads, 200);
        }

        return $this->json(false, 404);
    }

    #[Route('/new', name: 'admin_folder_new')]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $manager): Response
    {
        return $this->render('admin/folder/new.html.twig', ['type' => 0]);
    }

    #[Route('/collections/new', name: 'admin_folder_collections_new')]
    #[IsGranted('ROLE_ADMIN')]
    public function new2(Request $request, EntityManagerInterface $manager): Response
    {
        return $this->render('admin/folder/new.html.twig', ['type' => 1]);
    }

    #[Route('/edit/{id}', name: 'admin_folder_edit')]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Folder $folder, Request $request, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(AdminFolderType::class, $folder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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

    #[Route('/upload', name: 'admin_folder_upload')]
    #[IsGranted('ROLE_ADMIN')]
    public function upload(Request $request, EntityManagerInterface $manager, FolderRepository $folderRepo, UploadRepository $uploadRepo): Response
    {
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

        $array = ['id' => $upload->getId(), 'filename' => $upload->getFilename()];

        return $this->json($array);
    }

    #[Route('/rename/{id}', name: 'admin_folder_rename')]
    #[IsGranted('ROLE_ADMIN')]
    public function rename(Upload $upload, Request $request, EntityManagerInterface $manager): Response
    {
        $text = $request->query->get('text');

        if ($text) {
            $upload->setName($text);
            $manager->flush();

            return $this->json(true);
        }

        return $this->json(false);
    }

    #[Route('/uploads/all', name: 'admin_folder_uploads_all')]
    #[IsGranted('ROLE_ADMIN')]
    public function uploadsAll(Request $request, EntityManagerInterface $manager, FolderRepository $folderRepo, UploadRepository $uploadRepo): Response
    {
        $name = $request->request->get('name');
        $file = $request->files->get('file');

        if (!$file) {
            return $this->json("Le fichier est introuvable !", 404);
        }

        if (!$name) {
            return $this->json("Il n'y a pas de dossier !", 404);
        }

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

    #[Route('/collections/uploads/all', name: 'admin_folder_uploads_collections_all')]
    #[IsGranted('ROLE_ADMIN')]
    public function uploadsAll2(Request $request, EntityManagerInterface $manager, FolderRepository $folderRepo, UploadRepository $uploadRepo): Response
    {
        $name = $request->request->get('name');
        $file = $request->files->get('file');

        if (!$file) {
            return $this->json("Le fichier est introuvable !", 404);
        }

        if (!$name) {
            return $this->json("Il n'y a pas de dossier !", 404);
        }

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

    #[Route('/upload/delete/{id}', name: 'admin_folder_upload_delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteUpload(Request $request, EntityManagerInterface $manager, UploadRepository $repo): Response
    {
        $id = $request->request->get('id');
        $upload = $repo->findOneById($id);

        if ($upload) {
            $manager->remove($upload);
            $manager->flush();

            return $this->json(true);
        }

        return $this->json(false);
    }

    #[Route('/delete/{id}', name: 'admin_folder_delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteFolder(Folder $folder, EntityManagerInterface $manager): Response
    {
        $manager->remove($folder);
        $manager->flush();

        $this->addFlash(
            'success',
            "Le dossier a été supprimé !"
        );

        return $this->redirectToRoute("admin_folder_index");
    }
}