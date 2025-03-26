<?php

namespace App\Tests\Service\Admin;

use App\Entity\Admin;
use App\Service\Admin\AdminArchiveService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AdminArchiveServiceTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;
    private TokenStorageInterface $tokenStorage;
    private AdminArchiveService $adminArchiveService;
    private TokenInterface $token;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->token = $this->createMock(TokenInterface::class);

        $this->adminArchiveService = new AdminArchiveService(
            $this->entityManager,
            $this->passwordHasher,
            $this->tokenStorage
        );
    }

    public function testArchiveAdmin(): void
    {
        // Arrange
        $admin = new Admin();
        $admin->setEmail('test@example.com');
        $admin->setFirstName('John');
        $admin->setLastName('Doe');
        $admin->setPhone('0123456789');
        $admin->setIsActive(true);
        $admin->setArchive(false);

        // Utilisation de la réflexion pour définir l'ID
        $reflectionClass = new \ReflectionClass(Admin::class);
        $property = $reflectionClass->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($admin, 1);

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($this->token);

        $this->token->expects($this->once())
            ->method('getUser')
            ->willReturn(null);

        $this->passwordHasher->expects($this->once())
            ->method('hashPassword')
            ->willReturn('hashed_password');

        $this->entityManager->expects($this->once())
            ->method('flush');

        // Act
        $this->adminArchiveService->archiveAdmin($admin);

        // Assert
        $this->assertTrue($admin->isArchive());
        $this->assertFalse($admin->isActive());
        $this->assertEquals('archived_1@archived.local', $admin->getEmail());
        $this->assertEquals('Archived', $admin->getFirstName());
        $this->assertEquals('User', $admin->getLastName());
        $this->assertNull($admin->getPhone());
        $this->assertNotNull($admin->getArchivedAt());
    }

    public function testCannotArchiveOwnAccount(): void
    {
        // Arrange
        $currentAdmin = new Admin();
        
        // Utilisation de la réflexion pour définir l'ID
        $reflectionClass = new \ReflectionClass(Admin::class);
        $property = $reflectionClass->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($currentAdmin, 1);

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($this->token);

        $this->token->expects($this->once())
            ->method('getUser')
            ->willReturn($currentAdmin);

        // Assert
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Vous ne pouvez pas archiver votre propre compte.');

        // Act
        $this->adminArchiveService->archiveAdmin($currentAdmin);
    }

    public function testCanBeArchived(): void
    {
        // Arrange
        $admin = new Admin();

        // Act
        $result = $this->adminArchiveService->canBeArchived($admin);

        // Assert
        $this->assertTrue($result);
    }
} 