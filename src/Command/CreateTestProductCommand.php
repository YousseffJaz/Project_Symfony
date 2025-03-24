<?php

namespace App\Command;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Repository\CategoryRepository;

class CreateTestProductCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CategoryRepository $categoryRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:create-test-product')
            ->setDescription('Crée un produit test pour vérifier l\'indexation Elasticsearch');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Récupérer une catégorie (par exemple, 'Accessoires')
        $category = $this->categoryRepository->findOneBy(['name' => 'Accessoires']);
        
        if (!$category) {
            $output->writeln('Erreur: Catégorie "Accessoires" non trouvée');
            return Command::FAILURE;
        }

        // Créer un nouveau produit
        $product = new Product();
        $product->setTitle('Clavier Test Mécanique RGB');
        $product->setPrice(129.99);
        $product->setPurchasePrice(89.99);
        $product->setAlert(10);
        $product->setDigital(false);
        $product->setArchive(false);
        $product->setCategory($category);

        // Persister le produit
        $this->entityManager->persist($product);
        $this->entityManager->flush();

        $output->writeln('Produit créé avec succès ! ID: ' . $product->getId());
        
        return Command::SUCCESS;
    }
} 