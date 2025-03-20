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
        $this->assertSelectorTextContains('h1', 'Dashboard');

        // Assert all required statistics are present in the response
        $crawler = $this->client->getCrawler();
        $this->assertArrayHasKey('todayOrdersCount', $this->client->getContainer()->get('twig')->getGlobals());
        $this->assertArrayHasKey('todayOrdersTotal', $this->client->getContainer()->get('twig')->getGlobals());
        $this->assertArrayHasKey('pendingOrders', $this->client->getContainer()->get('twig')->getGlobals());
        $this->assertArrayHasKey('processingOrders', $this->client->getContainer()->get('twig')->getGlobals());
        $this->assertArrayHasKey('deliveredOrders', $this->client->getContainer()->get('twig')->getGlobals());
        $this->assertArrayHasKey('canceledOrders', $this->client->getContainer()->get('twig')->getGlobals());
        $this->assertArrayHasKey('monthlyRevenue', $this->client->getContainer()->get('twig')->getGlobals());
        $this->assertArrayHasKey('lowStockProducts', $this->client->getContainer()->get('twig')->getGlobals());
        $this->assertArrayHasKey('pendingTasks', $this->client->getContainer()->get('twig')->getGlobals());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        
        // Avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }
} 