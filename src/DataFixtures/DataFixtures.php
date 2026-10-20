<?php

namespace App\DataFixtures;

use App\Entity\Company;
use App\Entity\Job;
use App\Entity\User;
use App\Enum\GraduateEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class DataFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        // Mot de passe commun à tous les comptes de test : "password123"
        $make = function (string $email, string $first, string $last, string $city, string $postal, array $roles) use ($manager): User {
            $user = new User();
            $user->setEmail($email);
            $user->setFirstname($first);
            $user->setLastname($last);
            $user->setCity($city);
            $user->setPostalCode($postal);
            $user->setRoles($roles);
            $user->setPassword($this->hasher->hashPassword($user, '123'));
            return $user;
        };

        // --- Admin ---
        $admin = $make('admin@test.com', 'Admin', 'Root', 'Paris', '75001', ['ROLE_ADMIN']);
        $manager->persist($admin);

        // --- Candidats ---
        $candidates = [
            ['alice@test.com', 'Alice', 'Martin', 'Lyon', '69001', 'LICENCE'],
            ['bob@test.com', 'Bob', 'Durand', 'Nantes', '44000', 'BAC'],
            ['chloe@test.com', 'Chloé', 'Bernard', 'Lille', '59000', 'MASTER'],
            ['david@test.com', 'David', 'Moreau', 'Paris', '75011', 'MASTER'],
            ['emma@test.com', 'Emma', 'Leroy', 'Marseille', '13002', 'LICENCE'],
            ['farid@test.com', 'Farid', 'Benali', 'Lyon', '69003', 'BAC'],
            ['giulia@test.com', 'Giulia', 'Rossi', 'Nice', '06000', 'DOCTORAT'],
            ['hugo@test.com', 'Hugo', 'Petit', 'Rennes', '35000', 'BAC'],
            ['ines@test.com', 'Inès', 'Garcia', 'Toulouse', '31000', 'MASTER'],
            ['jules@test.com', 'Jules', 'Roux', 'Bordeaux', '33000', 'LICENCE'],
            ['karim@test.com', 'Karim', 'Haddad', 'Lille', '59000', 'BAC'],
            ['lea@test.com', 'Léa', 'Fournier', 'Strasbourg', '67000', 'MASTER'],
            ['mathis@test.com', 'Mathis', 'Girard', 'Nantes', '44000', 'LICENCE'],
        ];
        foreach ($candidates as [$email, $first, $last, $city, $postal, $graduate]) {
            $user = $make($email, $first, $last, $city, $postal, ['ROLE_USER']);
            $user->setGraduate($graduate);
            $manager->persist($user);
        }

        // --- Companies + leur user (OneToOne : 1 user par company) ---
        $companies = [
            ['Acme SAS', 'Éditeur de logiciels.', '73282932000074', 'rh@acme.com', 'Jean', 'Acme', 'Paris', '75002'],
            ['Globex SARL', 'Conseil et ingénierie.', '35600000000048', 'rh@globex.com', 'Sophie', 'Globex', 'Bordeaux', '33000'],
            ['Initech', 'Services numériques.', '12345678901237', 'rh@initech.com', 'Paul', 'Initech', 'Toulouse', '31000'],
            ['Nova Tech', 'Développement d\'applications web et mobiles.', '40123456700019', 'rh@novatech.com', 'Claire', 'Nova', 'Paris', '75008'],
            ['Bleu Horizon', 'Agence de communication digitale.', '40234567800027', 'rh@bleuhorizon.com', 'Marc', 'Horizon', 'Marseille', '13001'],
            ['Atelier Vert', 'Bureau d\'études en écologie urbaine.', '40345678900035', 'rh@ateliervert.com', 'Lucie', 'Vert', 'Nantes', '44000'],
            ['DataPulse', 'Analyse de données et intelligence artificielle.', '40456789000043', 'rh@datapulse.com', 'Thomas', 'Pulse', 'Lyon', '69002'],
            ['Cobalt Industries', 'Fabrication de composants électroniques.', '40567890100051', 'rh@cobalt.com', 'Nadia', 'Cobalt', 'Grenoble', '38000'],
            ['Lumen Santé', 'Solutions numériques pour la santé.', '40678901200069', 'rh@lumensante.com', 'Hugo', 'Lumen', 'Strasbourg', '67000'],
            ['Horizon Logistique', 'Transport et gestion de la chaîne logistique.', '40789012300077', 'rh@horizonlog.com', 'Emma', 'Horizon', 'Lille', '59000'],
            ['Pixel Studio', 'Studio de création graphique et jeux vidéo.', '40890123400085', 'rh@pixelstudio.com', 'Julien', 'Pixel', 'Rennes', '35000'],
            ['Cyberia', 'Cybersécurité et conseil en sécurité informatique.', '40901234500093', 'rh@cyberia.com', 'Inès', 'Cyberia', 'Toulouse', '31000'],
            ['Terra Foods', 'Distribution de produits alimentaires bio.', '41012345600018', 'rh@terrafoods.com', 'Louis', 'Terra', 'Montpellier', '34000'],
        ];
        foreach ($companies as [$name, $desc, $siret, $email, $first, $last, $city, $postal]) {
            $company = new Company();
            $company->setName($name);
            $company->setDescription($desc);
            $company->setSiret($siret);
            $manager->persist($company);

            $user = $make($email, $first, $last, $city, $postal, ['ROLE_COMPANY']);
            $user->setCompany($company);
            $manager->persist($user);

            $this->addReference('company_' . $name, $company); // utile pour de futures fixtures de Job
        }

        $data = [
            'Acme SAS' => [
                '12 rue de la Bourse',
                75002,
                'Paris',
                [
                    ['Développeur PHP Symfony', 'Développement et maintenance de nos applications web.', 42000, 'Temps plein 35h', 'CDI'],
                    ['Chef de projet digital', 'Pilotage des projets et relation avec les clients.', 48000, 'Temps plein 39h', 'CDI'],
                    ['Testeur QA', 'Rédaction et exécution de plans de test.', 32000, 'Temps plein 35h', 'CDD'],
                ]
            ],
            'Globex SARL' => [
                '5 cours de l\'Intendance',
                33000,
                'Bordeaux',
                [
                    ['Ingénieur conseil', 'Accompagnement des clients sur des missions d\'ingénierie.', 45000, 'Temps plein 39h', 'CDI'],
                    ['Consultant junior', 'Participation aux audits et recommandations.', 34000, 'Temps plein 35h', 'CDD'],
                    ['Assistant de direction', 'Gestion de l\'agenda et des dossiers.', 30000, 'Temps partiel 24h', 'CDI'],
                ]
            ],
            'Initech' => [
                '8 allée Jean Jaurès',
                31000,
                'Toulouse',
                [
                    ['Administrateur systèmes', 'Gestion des serveurs et de la supervision.', 40000, 'Temps plein 35h', 'CDI'],
                    ['Développeur Java', 'Conception de services back-end.', 43000, 'Temps plein 35h', 'CDI'],
                    ['Support technique niveau 2', 'Résolution des incidents complexes.', 29000, 'Temps plein 35h', 'CDD'],
                ]
            ],
            'Nova Tech' => [
                '21 rue de Courcelles',
                75008,
                'Paris',
                [
                    ['Développeur front-end React', 'Création d\'interfaces web modernes.', 41000, 'Temps plein 35h', 'CDI'],
                    ['Développeur mobile Flutter', 'Développement d\'applications iOS et Android.', 44000, 'Temps plein 35h', 'CDI'],
                    ['Product owner', 'Définition de la roadmap et du backlog.', 50000, 'Temps plein 39h', 'CDI'],
                ]
            ],
            'Bleu Horizon' => [
                '3 quai du Port',
                13001,
                'Marseille',
                [
                    ['Community manager', 'Animation des réseaux sociaux.', 28000, 'Temps plein 35h', 'CDD'],
                    ['Graphiste', 'Conception de visuels print et web.', 30000, 'Temps plein 35h', 'CDI'],
                    ['Chargé de communication', 'Gestion des campagnes clients.', 31000, 'Temps plein 35h', 'Alternance'],
                ]
            ],
            'Atelier Vert' => [
                '14 rue des Olivettes',
                44000,
                'Nantes',
                [
                    ['Ingénieur environnement', 'Études d\'impact et aménagement durable.', 38000, 'Temps plein 35h', 'CDI'],
                    ['Paysagiste concepteur', 'Conception d\'espaces verts urbains.', 33000, 'Temps plein 35h', 'CDI'],
                    ['Technicien de terrain', 'Relevés et suivi de chantiers écologiques.', 26000, 'Temps plein 35h', 'Stage'],
                ]
            ],
            'DataPulse' => [
                '40 rue de la République',
                69002,
                'Lyon',
                [
                    ['Data scientist', 'Modélisation de grands volumes de données.', 52000, 'Temps plein 35h', 'CDI'],
                    ['Data engineer', 'Construction des pipelines de données.', 50000, 'Temps plein 35h', 'CDI'],
                    ['Analyste BI', 'Création de tableaux de bord décisionnels.', 38000, 'Temps plein 35h', 'Alternance'],
                ]
            ],
            'Cobalt Industries' => [
                '2 avenue des Martyrs',
                38000,
                'Grenoble',
                [
                    ['Technicien en électronique', 'Assemblage et contrôle de cartes.', 27000, 'Temps plein 35h', 'CDI'],
                    ['Ingénieur méthodes', 'Optimisation des processus de production.', 42000, 'Temps plein 35h', 'CDI'],
                    ['Responsable qualité', 'Suivi de la conformité et des audits.', 46000, 'Temps plein 39h', 'CDI'],
                ]
            ],
            'Lumen Santé' => [
                '9 rue des Hallebardes',
                67000,
                'Strasbourg',
                [
                    ['Développeur full stack', 'Plateformes de suivi de patients.', 43000, 'Temps plein 35h', 'CDI'],
                    ['Chargé de conformité RGPD', 'Gouvernance des données de santé.', 40000, 'Temps plein 35h', 'CDI'],
                    ['Support client santé', 'Assistance des professionnels de santé.', 28000, 'Temps partiel 24h', 'CDD'],
                ]
            ],
            'Horizon Logistique' => [
                '17 boulevard de la Liberté',
                59000,
                'Lille',
                [
                    ['Gestionnaire de transport', 'Planification et suivi des livraisons.', 32000, 'Temps plein 35h', 'CDI'],
                    ['Responsable d\'entrepôt', 'Organisation des flux et encadrement.', 38000, 'Temps plein 39h', 'CDI'],
                    ['Chauffeur livreur', 'Tournées de livraison régionales.', 25000, 'Temps plein 35h', 'CDD'],
                ]
            ],
            'Pixel Studio' => [
                '6 rue de Siam',
                35000,
                'Rennes',
                [
                    ['Game designer', 'Conception des mécaniques de jeu.', 36000, 'Temps plein 35h', 'CDI'],
                    ['Développeur Unity', 'Programmation de jeux en C#.', 39000, 'Temps plein 35h', 'CDI'],
                    ['Artiste 3D', 'Modélisation d\'environnements et de personnages.', 34000, 'Temps plein 35h', 'Stage'],
                ]
            ],
            'Cyberia' => [
                '11 place du Capitole',
                31000,
                'Toulouse',
                [
                    ['Analyste SOC', 'Surveillance et détection des incidents.', 41000, 'Temps plein 35h', 'CDI'],
                    ['Pentester', 'Tests d\'intrusion et rapports.', 47000, 'Temps plein 35h', 'CDI'],
                    ['Consultant en cybersécurité', 'Audit et accompagnement des clients.', 49000, 'Temps plein 39h', 'CDI'],
                ]
            ],
            'Terra Foods' => [
                '25 rue de la Loge',
                34000,
                'Montpellier',
                [
                    ['Acheteur produits bio', 'Sélection et négociation avec les producteurs.', 35000, 'Temps plein 35h', 'CDI'],
                    ['Responsable magasin', 'Gestion d\'un point de vente et de son équipe.', 37000, 'Temps plein 39h', 'CDI'],
                    ['Préparateur de commandes', 'Préparation et expédition des commandes.', 24000, 'Temps plein 35h', 'CDD'],
                ]
            ],
        ];

        foreach ($data as $companyName => [$address, $postalCode, $city, $offers]) {
            /** @var Company $company */
            $company = $this->getReference('company_' . $companyName, Company::class);

            foreach ($offers as [$title, $description, $salary, $schedule, $type]) {
                $job = new Job();
                $job->setTitle($title);
                $job->setDescription($description);
                $job->setSalary((float) $salary);
                $job->setSchedule($schedule);
                $job->setType($type);
                $job->setAddress($address);
                $job->setPostalCode($postalCode);
                $job->setCity($city);
                $job->setCompany($company);   // voir remarque ci-dessous
                $manager->persist($job);
            }
        }

        $manager->flush();
    }
}