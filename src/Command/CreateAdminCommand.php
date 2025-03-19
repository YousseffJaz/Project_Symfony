<?php

namespace App\Command;

use App\Entity\Admin;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Console\Question\Question;

class CreateAdminCommand extends Command
{
    protected static $defaultName = 'app:create-admin';

    private $entityManager;
    private $passwordHasher;
    private $io;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    protected function configure()
    {
        $this
            ->setDescription('Crée un utilisateur administrateur initial')
            ->setHelp('Cette commande permet de créer un utilisateur administrateur avec les droits nécessaires');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $this->io->title('Création d\'un utilisateur administrateur');

        $helper = $this->getHelper('question');

        $question = new Question('Entrez le prénom de l\'administrateur : ');
        $firstName = $helper->ask($input, $output, $question);

        $question = new Question('Entrez l\'email de l\'administrateur : ');
        $email = $helper->ask($input, $output, $question);

        $question = new Question('Entrez le mot de passe de l\'administrateur : ');
        $question->setHidden(true);
        $question->setHiddenFallback(false);
        $password = $helper->ask($input, $output, $question);

        $admin = new Admin();
        $admin->setFirstName($firstName);
        $admin->setEmail($email);
        $admin->setRole('ROLE_ADMIN');
        $admin->setArchive(false);
        $admin->setStatistics(true);
        $admin->setInvoices(true);
        $admin->setHistories(true);
        $admin->setFolders(true);
        $admin->setProducts(true);
        $admin->setAccounting(true);

        $hashedPassword = $this->passwordHasher->hashPassword($admin, $password);
        $admin->setHash($hashedPassword);

        $this->entityManager->persist($admin);
        $this->entityManager->flush();

        $this->io->success('L\'administrateur a été créé avec succès !');

        return Command::SUCCESS;
    }
} 