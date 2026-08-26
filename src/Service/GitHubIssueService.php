<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface as HttpClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * GitHubIssueService — Transforme un signalement utilisateur (formulaire de contact)
 * en ticket GitHub Issues, afin d'alimenter directement l'outil de suivi des
 * anomalies/évolutions utilisé par le prestataire (voir plan de maintenance).
 *
 * Le formulaire public ne donne jamais directement accès à GitHub : c'est ce
 * service, exécuté côté serveur avec un jeton dédié à portée restreinte
 * (droits "Issues: write" uniquement), qui crée le ticket pour le compte de
 * l'utilisateur.
 */
class GitHubIssueService
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
        private readonly ?string $githubToken,
        private readonly ?string $githubRepository,
    ) {
    }

    /**
     * Indique si le service est correctement configuré (jeton + dépôt renseignés).
     */
    public function isConfigured(): bool
    {
        return !empty($this->githubToken) && !empty($this->githubRepository);
    }

    /**
     * Crée un ticket GitHub Issues à partir d'un signalement utilisateur.
     *
     * @param string      $environnement Rubrique/module concerné, choisi dans le formulaire
     * @param string      $sujet         Résumé court du problème (devient le titre du ticket)
     * @param string      $description   Description détaillée fournie par l'utilisateur
     * @param string|null $email         Email de contact optionnel de la personne signalant le problème
     *
     * @return bool true si le ticket a bien été créé, false en cas d'échec (l'échec est journalisé
     *              mais ne doit jamais faire planter le parcours utilisateur)
     */
    public function createIssueFromReport(
        string $environnement,
        string $sujet,
        string $description,
        ?string $email = null,
    ): bool {
        if (!$this->isConfigured()) {
            $this->logger->error('GitHubIssueService non configuré : GITHUB_TOKEN ou GITHUB_REPOSITORY manquant.');

            return false;
        }

        $title = sprintf('[Signalement client] (%s) %s', $environnement, $sujet);

        $body = "**Origine :** formulaire public de signalement (page /contact)\n\n"
            .'**Rubrique concernée :** '.$environnement."\n\n"
            .'**Contact fourni :** '.($email ?: 'non renseigné')."\n\n"
            ."**Description transmise par l'utilisateur :**\n\n"
            .$description;

        try {
            $response = $this->httpClient->request(
                'POST',
                sprintf('https://api.github.com/repos/%s/issues', $this->githubRepository),
                [
                    'headers' => [
                        'Authorization' => 'Bearer '.$this->githubToken,
                        'Accept' => 'application/vnd.github+json',
                        'X-GitHub-Api-Version' => '2022-11-28',
                        'User-Agent' => 'CESIZen-Contact-Form',
                    ],
                    'json' => [
                        'title' => $title,
                        'body' => $body,
                        // Labels appliqués automatiquement : à qualifier par le prestataire,
                        // qui affinera ensuite avec le label de sévérité (bloquant/majeur/mineur).
                        'labels' => ['signalement-client', 'à-qualifier'],
                    ],
                ]
            );

            $statusCode = $response->getStatusCode();

            if ($statusCode >= 200 && $statusCode < 300) {
                return true;
            }

            $this->logger->error('Échec de création du ticket GitHub Issues.', [
                'status_code' => $statusCode,
                'response' => $response->getContent(false),
            ]);

            return false;
        } catch (HttpClientExceptionInterface $e) {
            $this->logger->error('Erreur lors de l\'appel à l\'API GitHub Issues.', [
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
