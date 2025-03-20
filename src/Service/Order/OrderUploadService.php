<?php

namespace App\Service\Order;

use App\Entity\Upload;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class OrderUploadService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private string $uploadDir
    ) {
    }

    public function handleUpload(UploadedFile $file, Order $order): Upload
    {
        $upload = new Upload();
        $fileName = md5(uniqid()) . '.' . $file->guessExtension();
        
        $file->move($this->uploadDir, $fileName);
        
        $upload->setFilename($fileName);
        $upload->setName($file->getClientOriginalName());
        $upload->setFile($file);
        $upload->setOrder($order);
        
        $this->entityManager->persist($upload);
        $this->entityManager->flush();
        
        return $upload;
    }

    public function deleteUpload(Upload $upload): void
    {
        $filePath = $this->uploadDir . '/' . $upload->getFilename();
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        $this->entityManager->remove($upload);
        $this->entityManager->flush();
    }
} 