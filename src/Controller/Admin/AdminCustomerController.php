<?php

namespace App\Controller\Admin;

use App\Entity\Customer;
use App\Form\AdminCustomerType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/customer')]
class AdminCustomerController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('', name: 'admin_customer_index')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(): Response
    {
        $customers = $this->entityManager->getRepository(Customer::class)
            ->createQueryBuilder('c')
            ->addSelect('o')
            ->leftJoin('c.orders', 'o')
            ->orderBy('c.lastname', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('admin/customer/index.html.twig', [
            'customers' => $customers
        ]);
    }

    #[Route('/new', name: 'admin_customer_new')]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request): Response
    {
        $customer = new Customer();
        $form = $this->createForm(AdminCustomerType::class, $customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($customer);
            $this->entityManager->flush();

            $this->addFlash('success', 'Le client a été créé avec succès !');

            return $this->redirectToRoute('admin_customer_index');
        }

        return $this->render('admin/customer/new.html.twig', [
            'form' => $form->createView()
        ]);
    }
} 