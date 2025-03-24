<?php

namespace App\Controller;

use App\Document\Note;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NoteController extends AbstractController
{
    #[Route('/api/notes', name: 'create_note', methods: ['POST'])]
    public function createNote(Request $request, DocumentManager $dm): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $note = new Note();
        $note->setTitle($data['title']);
        $note->setContent($data['content']);

        $dm->persist($note);
        $dm->flush();

        return $this->json([
            'id' => $note->getId(),
            'title' => $note->getTitle(),
            'content' => $note->getContent(),
            'createdAt' => $note->getCreatedAt()
        ]);
    }

    #[Route('/api/notes', name: 'get_notes', methods: ['GET'])]
    public function getNotes(DocumentManager $dm): JsonResponse
    {
        $notes = $dm->getRepository(Note::class)->findAll();
        $data = [];

        foreach ($notes as $note) {
            $data[] = [
                'id' => $note->getId(),
                'title' => $note->getTitle(),
                'content' => $note->getContent(),
                'createdAt' => $note->getCreatedAt()
            ];
        }

        return $this->json($data);
    }
} 