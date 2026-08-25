<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Crée un compte administrateur en environnement qualif/prod, sans jamais
 * écrire de mot de passe en dur dans le code (contrairement à AppFixtures,
 * dev/test uniquement).
 *
 * L'email et le mot de passe sont saisis de façon interactive et masquée.
 * Ne rien logger : le mot de passe ne doit jamais apparaître dans un
 * historique de commandes ou une sortie CI.
 */
#[AsCommand(
    name: 'app:create-admin',
    description: 'Crée un compte administrateur de façon interactive et sécurisée (sans mot de passe en dur).',
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ValidatorInterface $validator,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::OPTIONAL, 'Email de l\'administrateur (sinon demandé de façon interactive)')
            ->setHelp('Le mot de passe est toujours saisi de façon interactive et masquée, jamais passé en argument (pour éviter qu\'il ne reste dans l\'historique bash / les logs CI).');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $email = $input->getArgument('email');
        if (!$email) {
            $emailQuestion = new Question('Email de l\'administrateur : ');
            $email = $helper->ask($input, $output, $emailQuestion);
        }

        $emailErrors = $this->validator->validate($email, [new Assert\NotBlank(), new Assert\Email()]);
        if (count($emailErrors) > 0) {
            $io->error((string) $emailErrors);

            return Command::FAILURE;
        }

        $existing = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if (null !== $existing) {
            $io->error(sprintf('Un compte existe déjà pour "%s".', $email));

            return Command::FAILURE;
        }

        $passwordQuestion = new Question('Mot de passe (saisie masquée) : ');
        $passwordQuestion->setHidden(true);
        $passwordQuestion->setHiddenFallback(false);
        $password = $helper->ask($input, $output, $passwordQuestion);

        $confirmQuestion = new Question('Confirmez le mot de passe : ');
        $confirmQuestion->setHidden(true);
        $confirmQuestion->setHiddenFallback(false);
        $confirmation = $helper->ask($input, $output, $confirmQuestion);

        if ($password !== $confirmation) {
            $io->error('Les deux mots de passe ne correspondent pas.');

            return Command::FAILURE;
        }

        $passwordErrors = $this->validator->validate($password, [
            new Assert\NotBlank(),
            new Assert\Length(min: 12, minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères.'),
        ]);
        if (count($passwordErrors) > 0) {
            $io->error((string) $passwordErrors);

            return Command::FAILURE;
        }

        $admin = new User();
        $admin->setEmail($email);
        $admin->setName($io->ask('Nom affiché', 'Administrateur'));
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, $password));
        $admin->setCreationDate(new \DateTimeImmutable());
        $admin->setIsActive(true);

        $this->entityManager->persist($admin);
        $this->entityManager->flush();

        $io->success(sprintf('Compte administrateur créé pour %s.', $email));

        return Command::SUCCESS;
    }
}
