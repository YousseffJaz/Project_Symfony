<?php

namespace App\Tests\Controller;

use App\Entity\Admin;
use App\Repository\AdminRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminAccountControllerTest extends WebTestCase
{
    public function testAccessLoginPage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Connexion à l\'administration');
    }

    public function testLoginWithBadCredentials(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => 'fake@email.com',
            '_password' => 'fakepassword'
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/');
        $client->followRedirect();
        
        $this->assertSelectorExists('.alert.alert-danger');
    }

    public function testSuccessfulLogin(): void
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine')->getManager();

        // Créer un admin de test
        $admin = new Admin();
        $admin->setEmail('test@test.com')
              ->setFirstName('Test')
              ->setHash($client->getContainer()->get(UserPasswordHasherInterface::class)->hashPassword(
                  $admin,
                  'password123'
              ));

        $entityManager->persist($admin);
        $entityManager->flush();

        // Test de connexion
        $crawler = $client->request('GET', '/');

        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => 'test@test.com',
            '_password' => 'password123'
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/admin');

        // Nettoyage
        $entityManager->remove($admin);
        $entityManager->flush();
    }

    public function testPasswordUpdate(): void
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine')->getManager();
        $adminRepository = $entityManager->getRepository(Admin::class);

        // Créer un admin de test
        $admin = new Admin();
        $admin->setEmail('test@test.com')
              ->setFirstName('Test')
              ->setHash($client->getContainer()->get(UserPasswordHasherInterface::class)->hashPassword(
                  $admin,
                  'password123'
              ));

        $entityManager->persist($admin);
        $entityManager->flush();

        // Connexion
        $client->loginUser($admin);

        // Accès à la page de profil
        $crawler = $client->request('GET', '/admin/profile');
        $this->assertResponseIsSuccessful();

        // Soumission du formulaire de changement de mot de passe
        $form = $crawler->selectButton('Modifier le mot de passe')->form([
            'password_update[newPassword]' => 'newPassword123',
            'password_update[confirmPassword]' => 'newPassword123'
        ]);

        $client->submit($form);
        
        // Vérification de la redirection et du message de succès
        $this->assertResponseRedirects('/admin/profile');
        $client->followRedirect();
        
        $this->assertSelectorExists('.alert.alert-success');

        // Vérification que le mot de passe a bien été changé
        $updatedAdmin = $adminRepository->find($admin->getId());
        $passwordHasher = $client->getContainer()->get(UserPasswordHasherInterface::class);
        
        $this->assertTrue($passwordHasher->isPasswordValid($updatedAdmin, 'newPassword123'));
        $this->assertFalse($passwordHasher->isPasswordValid($updatedAdmin, 'password123'));

        // Nettoyage
        $entityManager->remove($admin);
        $entityManager->flush();
    }
} 