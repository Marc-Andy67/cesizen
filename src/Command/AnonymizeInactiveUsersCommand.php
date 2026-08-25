<?php

namespace App\Command;

use App\Repository\UserRepository;
use App\Service\RgpdService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Anonymise automatiquement les comptes utilisateurs inactifs depuis plus de N années.
 *
 * Un compte est considéré comme inactif au-delà du seuil s'il n'y a pas eu de connexion
 * depuis N ans (u.lastConnection), ou, s'il n'y a jamais eu de connexion, si le compte a
 * été créé il y a plus de N ans (u.creationDate).
 *
 * Déclenchement : tâche planifiée (cron) sur le serveur, voir /deploy/rgpd-cron.md.
 */
#[AsCommand(
    name: 'app:rgpd:anonymize-inactive-users',
    description: "Anonymise les comptes utilisateurs inactifs depuis plus de N années (RGPD - droit à l'oubli).",
)]
class AnonymizeInactiveUsersCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly RgpdService $rgpdService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'years',
                null,
                InputOption::VALUE_REQUIRED,
                'Ancienneté (en années) à partir de laquelle un compte inactif est anonymisé',
                '5'
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                "N'anonymise rien : affiche seulement la liste des comptes qui seraient concernés"
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $years = (int) $input->getOption('years');
        $dryRun = (bool) $input->getOption('dry-run');

        if ($years < 1) {
            $io->error('Le paramètre --years doit être un entier positif.');

            return Command::INVALID;
        }

        $threshold = new \DateTimeImmutable(sprintf('-%d years', $years));
        $users = $this->userRepository->findInactiveUsersOlderThan($threshold);

        if ([] === $users) {
            $io->success(sprintf('Aucun compte inactif depuis plus de %d ans à anonymiser.', $years));

            return Command::SUCCESS;
        }

        $io->note(sprintf(
            '%d compte(s) inactif(s) depuis plus de %d ans (seuil : %s) détecté(s).',
            count($users),
            $years,
            $threshold->format('Y-m-d')
        ));

        foreach ($users as $user) {
            $reference = $user->getLastConnection() ?? $user->getCreationDate();
            $referenceLabel = $reference?->format('Y-m-d') ?? 'inconnue';

            if ($dryRun) {
                $io->writeln(sprintf(' - [DRY-RUN] %s (dernière activité : %s)', $user->getEmail(), $referenceLabel));

                continue;
            }

            $io->writeln(sprintf(' - Anonymisation de %s (dernière activité : %s)', $user->getEmail(), $referenceLabel));
            $this->rgpdService->anonymizeUser($user);
        }

        if ($dryRun) {
            $io->success(sprintf(
                '%d compte(s) auraient été anonymisés (mode dry-run, aucune donnée modifiée).',
                count($users)
            ));
        } else {
            $io->success(sprintf('%d compte(s) anonymisé(s) avec succès.', count($users)));
        }

        return Command::SUCCESS;
    }
}
