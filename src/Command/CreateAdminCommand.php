<?php

namespace App\Command;

use App\Entity\Admin;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Helper\QuestionHelper;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Crée un administrateur avec le rôle ROLE_SUPER_ADMIN',
    hidden: false
)]
class CreateAdminCommand extends Command
{
    private SymfonyStyle $io;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setHelp('Cette commande permet de créer un administrateur avec tous les droits');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);
        /** @var QuestionHelper $questionHelper */
        $questionHelper = $this->getHelper('question');

        $this->io->title('Création d\'un utilisateur administrateur');

        $question = new Question('Entrez le prénom de l\'administrateur : ');
        $firstName = $questionHelper->ask($input, $output, $question);

        $question = new Question('Entrez l\'email de l\'administrateur : ');
        $email = $questionHelper->ask($input, $output, $question);

        $question = new Question('Entrez le mot de passe de l\'administrateur : ');
        $question->setHidden(true);
        $question->setHiddenFallback(false);
        $password = $questionHelper->ask($input, $output, $question);

        $admin = new Admin();
        $admin->setFirstName($firstName);
        $admin->setEmail($email);
        $admin->setRole('ROLE_SUPER_ADMIN');
        $admin->setArchive(false);
        $admin->setStatistics(true);
        $admin->setInvoices(true);
        $admin->setHistories(true);
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