<?php

namespace App\Tests\Controller;

use App\Entity\Admin;
use App\Repository\AdminRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class AdminDashboardControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    public function testAccessDeniedForNonAuthenticatedUsers(): void
    {
        $this->client->request('GET', '/admin/');
        
        $this->assertResponseRedirects('/login');
    }

    public function testDashboardAccessibleByAdmin(): void
    {
        // Get the admin user from repository
        $adminRepository = static::getContainer()->get(AdminRepository::class);
        $testAdmin = $adminRepository->findOneByEmail('admin@example.com');

        // Log in the admin
        $this->client->loginUser($testAdmin);

        // Make request to dashboard
        $this->client->request('GET', '/admin/');

        // Assert successful response
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Tableau de bord');
        
        // Vérifier que les variables globales Twig sont définies
        $this->assertArrayHasKey('totalOrders', $this->client->getContainer()->get('twig')->getGlobals());
        $this->assertArrayHasKey('totalProducts', $this->client->getContainer()->get('twig')->getGlobals());
        $this->assertArrayHasKey('totalAdmins', $this->client->getContainer()->get('twig')->getGlobals());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        
        // Avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }
} 