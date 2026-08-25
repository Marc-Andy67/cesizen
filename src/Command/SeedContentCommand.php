<?php

namespace App\Command;

use App\Entity\Category;
use App\Entity\Documentation;
use App\Entity\Question;
use App\Entity\Quiz;
use App\Entity\Response;
use App\Entity\StressThreshold;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Seed le contenu fonctionnel de CESIZen (catégories, documentation, quiz
 * Holmes & Rahe, seuils de stress).
 *
 * Contrairement à AppFixtures (dev/test uniquement, contient de faux comptes
 * utilisateurs avec mots de passe en dur), cette commande ne touche à aucune
 * donnée sensible : elle est volontairement disponible dans TOUS les
 * environnements (dev, test, qualif, prod) et est idempotente — on peut la
 * relancer sans jamais dupliquer le contenu.
 */
#[AsCommand(
    name: 'app:seed-content',
    description: 'Insère le contenu fonctionnel de CESIZen (catégories, documentation, quiz, seuils de stress) s\'il n\'existe pas déjà.',
)]
class SeedContentCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $existingQuiz = $this->entityManager->getRepository(Quiz::class)
            ->findOneBy(['title' => 'Échelle d\'Évaluation de Réajustement Social de Holmes et Rahe']);

        if (null !== $existingQuiz) {
            $io->success('Le contenu est déjà présent en base, rien à faire.');

            return Command::SUCCESS;
        }

        $manager = $this->entityManager;

        // 1. Catégories
        $categoriesData = [
            'Santé Mentale' => 'Ressources globales sur le bien-être émotionnel.',
            'Gestion du Stress' => 'Techniques et astuces pour réduire l\'anxiété au quotidien.',
            'Équilibre Vie Pro/Perso' => 'Conseils pour mieux concilier travail et vie privée.',
        ];

        $categories = [];
        foreach ($categoriesData as $name => $desc) {
            $category = new Category();
            $category->setName($name);
            $category->setDescription($desc);
            $manager->persist($category);
            $categories[] = $category;
        }

        // 2. Documentation
        $docsData = [
            [
                'title' => 'Comprendre le Burnout',
                'content' => '<p>Le syndrome d\'épuisement professionnel se traduit par un épuisement physique, émotionnel et mental...</p><ul><li>Symptôme 1</li><li>Symptôme 2</li></ul>',
                'cats' => [$categories[0], $categories[2]],
            ],
            [
                'title' => 'La technique Pomodoro',
                'content' => '<p>Améliorez votre productivité et limitez votre stress au travail grâce à des cycles de travail courts (25 min de travail, 5 min de pause).</p>',
                'cats' => [$categories[1], $categories[2]],
            ],
            [
                'title' => 'La cohérence cardiaque',
                'content' => '<p>Pratiquer la cohérence cardiaque 3 fois par jour pendant 5 minutes permet de réguler son niveau de stress de manière efficace et immédiate.</p>',
                'cats' => [$categories[0], $categories[1]],
            ],
            [
                'title' => 'Importance du sommeil',
                'content' => '<p>Un sommeil de qualité est le premier pilier de la santé mentale. Voici 10 conseils pour mieux dormir...</p>',
                'cats' => [$categories[0]],
            ],
            [
                'title' => 'Les signes précurseurs de l\'anxiété',
                'content' => '<p>L\'anxiété peut prendre plusieurs formes, mais certains signes corporels peuvent nous alerter (rythme cardiaque, tensions, troubles digestifs...).</p>',
                'cats' => [$categories[0], $categories[1]],
            ],
        ];

        foreach ($docsData as $d) {
            $doc = new Documentation();
            $doc->setTitle($d['title']);
            $doc->setContent($d['content']);
            $doc->setIsActive(true);
            foreach ($d['cats'] as $c) {
                $doc->addCategory($c);
            }
            $manager->persist($doc);
        }

        // 3. Questionnaire de Holmes et Rahe
        $quiz = new Quiz();
        $quiz->setTitle('Échelle d\'Évaluation de Réajustement Social de Holmes et Rahe');
        $quiz->setDescription("L'échelle de Holmes et Rahe permet d'évaluer de manière statistique la corrélation existant entre le stress induit par divers événements de l'existence et la probabilité d'apparition de maladies (physiques, psychiques ou émotionnelles). Le postulat de départ est que le changement, qu'il soit heureux ou non, est par essence anxiogène.\n\nVeuillez cocher les événements suivants s'ils vous sont arrivés **au cours des 12 derniers mois**. Si un événement exceptionnel s'est produit plus d'une fois au cours des 12 derniers mois, vous ne devez cocher la case qu'une seule fois.");
        $quiz->setIsActive(true);
        $quiz->addCategory($categories[0]);
        $quiz->addCategory($categories[1]);
        $manager->persist($quiz);

        // 4. Seuils de stress
        $thresholds = [
            [
                'level' => 'LowStress',
                'name' => 'Risque mineur',
                'min' => 0,
                'max' => 149,
                'desc' => 'Votre niveau de stress estimé est faible. Vous semblez avoir traversé une période relativement stable et sans heurts majeurs récemment.',
                'advice' => 'Maintenez vos bonnes habitudes de vie. Continuez à pratiquer une activité physique régulière et à veiller sur la qualité de votre sommeil. Poursuivez l\'utilisation de méthodes de relaxation légères si vous en ressentez le besoin.',
            ],
            [
                'level' => 'ModerateStress',
                'name' => 'Risque modéré',
                'min' => 150,
                'max' => 299,
                'desc' => 'Votre score indique un niveau de stress modéré. Vous accumulez actuellement plusieurs événements générateurs de changements (positifs ou négatifs) qui augmentent votre charge mentale globale et majorent le risque de développer un trouble d\'adaptation.',
                'advice' => 'Il est important d\'être à l\'écoute de votre corps. Introduisez des sas de décompression dans votre journée. Nous vous encourageons vivement à consulter notre section documentation pour découvrir des techniques de gestion du stress ou à en parler à un proche de confiance ou à un professionnel.',
            ],
            [
                'level' => 'HighStress',
                'name' => 'Risque élevé',
                'min' => 300,
                'max' => null,
                'desc' => 'Vos résultats témoignent d\'un niveau de stress très élevé ou chronique. Les événements récents de votre vie sollicitent très fortement vos capacités d\'adaptation et le risque sur votre santé (notamment cardiovasculaire et mentale) est significatif.',
                'advice' => 'Nous vous conseillons très fortement de consulter votre médecin traitant, un psychologue ou la médecine du travail. Ils sauront vous accompagner et vous orienter. En attendant, privilégiez le repos au maximum et n\'hésitez pas à déléguer ce qui peut l\'être pour réduire votre charge immédiate.',
            ],
        ];

        foreach ($thresholds as $tData) {
            $threshold = new StressThreshold();
            $threshold->setLevel($tData['level']);
            $threshold->setName($tData['name']);
            $threshold->setMinScore($tData['min']);
            $threshold->setMaxScore($tData['max']);
            $threshold->setDescription($tData['desc']);
            $threshold->setAdvice($tData['advice']);
            $manager->persist($threshold);
        }

        // 5. Items du questionnaire (échelle Holmes & Rahe)
        $holmesRaheItems = [
            ['title' => 'Décès du conjoint', 'score' => 100],
            ['title' => 'Divorce', 'score' => 73],
            ['title' => 'Séparation', 'score' => 65],
            ['title' => 'Emprisonnement', 'score' => 63],
            ['title' => 'Décès d\'un proche', 'score' => 63],
            ['title' => 'Blessure ou maladie personnelle', 'score' => 53],
            ['title' => 'Mariage', 'score' => 50],
            ['title' => 'Licenciement', 'score' => 47],
            ['title' => 'Réconciliation avec le conjoint', 'score' => 45],
            ['title' => 'Mise à la retraite', 'score' => 45],
            ['title' => 'Changement d\'état de santé d\'un membre de sa famille', 'score' => 44],
            ['title' => 'Grossesse', 'score' => 40],
            ['title' => 'Troubles sexuels', 'score' => 39],
            ['title' => 'Nouveau membre dans la famille', 'score' => 39],
            ['title' => 'Réorganisation professionnelle (ou de son conjoint)', 'score' => 39],
            ['title' => 'Changement notable dans ses rentrées d\'argent (gain ou perte)', 'score' => 38],
            ['title' => 'Décès d\'un grand ami', 'score' => 37],
            ['title' => 'Changement d\'activité professionnelle', 'score' => 36],
            ['title' => 'Augmentation notable dans les disputes avec son conjoint', 'score' => 35],
            ['title' => 'Emprunt ou prêt important (ex. maison)', 'score' => 31],
            ['title' => 'Responsabilité de plus en plus lourde quant vis-à-vis d\'un prêt ou emprunt', 'score' => 30],
            ['title' => 'Fin des possibilités d\'évolution dans l\'emploi que l\'on a', 'score' => 29],
            ['title' => 'Fils ou fille ou parent qui quitte le domicile', 'score' => 29],
            ['title' => 'Problèmes avec les beaux-parents ou problèmes de de belle-mère ou beau-père', 'score' => 29],
            ['title' => 'Réussite personnelle exceptionnelle', 'score' => 28],
            ['title' => 'Conjoint qui arrête ou qui reprend le travail', 'score' => 26],
            ['title' => 'Débuts ou fin aux études', 'score' => 26],
            ['title' => 'Changements notables dans nos conditions de vie', 'score' => 25],
            ['title' => 'Remise en question de quelques habitudes', 'score' => 24],
            ['title' => 'Problème ou conflit avec l\'employeur', 'score' => 23],
            ['title' => 'Déménagement', 'score' => 20],
            ['title' => 'Changement d\'établissement scolaire', 'score' => 20],
            ['title' => 'Changement de loisirs', 'score' => 19],
            ['title' => 'Changements dans l\'engagement religieux', 'score' => 19],
            ['title' => 'Changements d\'activités sociales', 'score' => 18],
            ['title' => 'Changement d\'habitudes concernant le sommeil', 'score' => 16],
            ['title' => 'Changement concernant les membres de la famille', 'score' => 15],
            ['title' => 'Changement d\'habitudes alimentaires', 'score' => 15],
            ['title' => 'Vacances, jours de congés', 'score' => 13],
            ['title' => 'Noël', 'score' => 12],
            ['title' => 'Petites infractions à la loi', 'score' => 11],
        ];

        foreach ($holmesRaheItems as $item) {
            $question = new Question();
            $question->setTitle($item['title']);
            $question->setIsActive(true);
            $question->addQuiz($quiz);
            $manager->persist($question);

            $responseOui = new Response();
            $responseOui->setDescription('Oui');
            $responseOui->setPoints($item['score']);
            $responseOui->setPosition(1);
            $responseOui->setIsActive(true);
            $responseOui->setQuestion($question);
            $manager->persist($responseOui);

            $responseNon = new Response();
            $responseNon->setDescription('Non');
            $responseNon->setPoints(0);
            $responseNon->setPosition(2);
            $responseNon->setIsActive(true);
            $responseNon->setQuestion($question);
            $manager->persist($responseNon);
        }

        $manager->flush();

        $io->success(sprintf(
            'Contenu inséré : %d catégories, %d documentations, 1 quiz (%d questions), %d seuils de stress.',
            count($categoriesData),
            count($docsData),
            count($holmesRaheItems),
            count($thresholds)
        ));

        return Command::SUCCESS;
    }
}
