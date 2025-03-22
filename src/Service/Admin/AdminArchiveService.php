<?php

namespace App\Service\Admin;

use App\Entity\Admin;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminArchiveService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private TokenStorageInterface $tokenStorage
    ) {
    }

    public function archiveAdmin(Admin $admin): void
    {
        // Vérifier que l'admin n'est pas l'utilisateur courant
        $currentUser = $this->tokenStorage->getToken()?->getUser();
        if ($currentUser instanceof Admin && $currentUser->getId() === $admin->getId()) {
            throw new \RuntimeException('Vous ne pouvez pas archiver votre propre compte.');
        }

        // Sauvegarder la date d'archivage
        $admin->setArchivedAt(new \DateTimeImmutable());
        
        // Marquer comme archivé
        $admin->setArchive(true);
        
        // Anonymiser les données personnelles
        $admin->setEmail('archived_' . $admin->getId() . '@archived.local');
        $admin->setFirstName('Archived');
        $admin->setLastName('User');
        $admin->setPhone(null);
        
        // Désactiver le compte
        $admin->setIsActive(false);
        
        // Révoquer tous les tokens de connexion existants
        $admin->setHash($this->passwordHasher->hashPassword(
            $admin,
            bin2hex(random_bytes(32)) // Mot de passe aléatoire impossible à deviner
        ));

        $this->entityManager->flush();
    }

    public function canBeArchived(Admin $admin): bool
    {
        // Vérifier si l'admin peut être archivé
        // Par exemple, s'il n'a pas de commandes en cours
        return true; // À adapter selon vos besoins
    }
} 