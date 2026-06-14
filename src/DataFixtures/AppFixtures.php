<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\ActivityLog;
use App\Entity\Client;
use App\Entity\Comment;
use App\Entity\Project;
use App\Entity\ProjectStageStatus;
use App\Entity\Task;
use App\Entity\User;
use App\Enum\Priority;
use App\Enum\ProjectStage;
use App\Enum\ProjectStatus;
use App\Enum\UserRole;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AppFixtures extends Fixture
{
    private Generator $faker;

    public function __construct(
        private readonly UserPasswordHasherInterface $hasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $this->faker = Factory::create('fr_FR');
        $this->faker->seed(2026);

        $users = $this->createUsers($manager);
        $clients = $this->createClients($manager);

        $manager->flush();

        $this->createProjects($manager, $users, $clients);

        $manager->flush();

        $this->createFinanceData($manager);
        $manager->flush();
    }

    private function createFinanceData(ObjectManager $manager): void
    {
        $projectRepo = $manager->getRepository(\App\Entity\Project::class);
        $projects = $projectRepo->findAll();
        $quoteCounter = [];
        $invoiceCounter = [];

        foreach ($projects as $project) {
            assert($project instanceof \App\Entity\Project);
            $year = $project->getCreatedAt()->format('Y');
            $quoteCounter[$year] = ($quoteCounter[$year] ?? 0) + 1;

            // Devis pour tous les projets sauf premiers stages
            if ($project->getCurrentStage()->position() >= \App\Enum\ProjectStage::CLIENT_VALIDATION->position()
                || $project->getStatus() === \App\Enum\ProjectStatus::DELIVERED) {
                $quote = new \App\Entity\Quote();
                $quote->setProject($project);
                $quote->setReference(sprintf('DEV-%s-%03d', $year, $quoteCounter[$year]));
                $quote->setVatRate(2000);
                $quote->setValidUntil($project->getCreatedAt()->modify('+45 days'));
                $quote->setStatus(\App\Enum\QuoteStatus::ACCEPTED);
                $quote->setSentAt($project->getCreatedAt()->modify('+2 days'));
                $quote->setAcceptedAt($project->getCreatedAt()->modify('+5 days'));

                $items = [
                    ['Création unique — modélisation et fabrication', 1, $project->getSellingPrice() * 0.7],
                    ['Pierre centrale', 1, $project->getSellingPrice() * 0.2],
                    ['Sertissage et finitions', 1, $project->getSellingPrice() * 0.1],
                ];
                foreach ($items as [$desc, $qty, $price]) {
                    $item = new \App\Entity\QuoteItem();
                    $item->setDescription($desc);
                    $item->setQuantity((int) $qty);
                    $item->setUnitPriceHt((int) round($price / 1.20));
                    $quote->addItem($item);
                }

                $ref = new \ReflectionProperty($quote, 'createdAt');
                $ref->setValue($quote, $project->getCreatedAt()->modify('+1 day'));
                $manager->persist($quote);

                // Facture pour projets livrés ou en aval
                if ($project->getStatus() === \App\Enum\ProjectStatus::DELIVERED
                    || $project->getCurrentStage()->position() >= \App\Enum\ProjectStage::DELIVERY->position()) {
                    $invoiceCounter[$year] = ($invoiceCounter[$year] ?? 0) + 1;
                    $invoice = new \App\Entity\Invoice();
                    $invoice->setProject($project);
                    $invoice->setQuote($quote);
                    $invoice->setReference(sprintf('FAC-%s-%03d', $year, $invoiceCounter[$year]));
                    $invoice->setVatRate(2000);
                    $invoice->setStatus(\App\Enum\InvoiceStatus::SENT);
                    $invoice->setSentAt($project->getDeliveredAt() ?? $project->getCreatedAt()->modify('+40 days'));
                    $invoice->setDueDate(($project->getDeliveredAt() ?? $project->getCreatedAt())->modify('+30 days'));
                    foreach ($quote->getItems() as $qi) {
                        $invItem = new \App\Entity\InvoiceItem();
                        $invItem->setDescription($qi->getDescription());
                        $invItem->setQuantity($qi->getQuantity());
                        $invItem->setUnitPriceHt($qi->getUnitPriceHt());
                        $invoice->addItem($invItem);
                    }

                    $refInv = new \ReflectionProperty($invoice, 'createdAt');
                    $refInv->setValue($invoice, $invoice->getSentAt());

                    if ($project->getStatus() === \App\Enum\ProjectStatus::DELIVERED && $this->faker->boolean(85)) {
                        $payment = new \App\Entity\Payment();
                        $payment->setInvoice($invoice);
                        $payment->setAmount($invoice->getTotalTtc());
                        $payment->setMethod(\App\Enum\PaymentMethod::TRANSFER);
                        $payment->setReceivedAt($invoice->getSentAt()->modify('+'.$this->faker->numberBetween(3, 25).' days'));
                        $invoice->addPayment($payment);
                        $invoice->setStatus(\App\Enum\InvoiceStatus::PAID);
                        $invoice->setPaidAt($payment->getReceivedAt());
                        $manager->persist($payment);
                    } elseif ($project->getStatus() === \App\Enum\ProjectStatus::ACTIVE && $this->faker->boolean(60)) {
                        // Acompte 50%
                        $payment = new \App\Entity\Payment();
                        $payment->setInvoice($invoice);
                        $payment->setAmount((int) ($invoice->getTotalTtc() * 0.5));
                        $payment->setMethod(\App\Enum\PaymentMethod::TRANSFER);
                        $payment->setReceivedAt($invoice->getSentAt()->modify('+10 days'));
                        $payment->setReference('Acompte');
                        $invoice->addPayment($payment);
                        $manager->persist($payment);
                    }

                    $manager->persist($invoice);
                }
            }

            // Dépenses imputées
            $nbExpenses = $this->faker->numberBetween(2, 5);
            $categories = \App\Enum\ExpenseCategory::cases();
            for ($i = 0; $i < $nbExpenses; $i++) {
                $expense = new \App\Entity\Expense();
                $expense->setProject($project);
                $expense->setCategory($this->faker->randomElement($categories));
                $expense->setDescription($this->faker->randomElement([
                    'Or 18k 8g',
                    'Diamant 0.75ct VVS1',
                    'Fonte par sous-traitant',
                    'Sertissage spécialisé',
                    'Polissage final',
                    'Expédition assurée',
                    'Boîte écrin gravée',
                ]));
                $expense->setSupplierName($this->faker->company());
                $amount = $this->faker->numberBetween(50_00, 8000_00);
                $expense->setAmountHt($amount);
                $expense->setVatAmount((int) round($amount * 0.20));
                $expense->setOccurredAt($project->getCreatedAt()->modify('+'.$this->faker->numberBetween(0, 30).' days'));
                $manager->persist($expense);
            }
        }
    }

    /** @return array<string, User> */
    private function createUsers(ObjectManager $manager): array
    {
        $defs = [
            ['admin@maison.test', 'Camille', 'Vidal', UserRole::ADMIN],
            ['commercial1@maison.test', 'Sophie', 'Martin', UserRole::COMMERCIAL],
            ['commercial2@maison.test', 'Antoine', 'Bernard', UserRole::COMMERCIAL],
            ['designer1@maison.test', 'Marie', 'Lefèvre', UserRole::DESIGNER],
            ['designer2@maison.test', 'Lucas', 'Petit', UserRole::DESIGNER],
            ['designer3@maison.test', 'Élodie', 'Garnier', UserRole::DESIGNER],
            ['joaillier1@maison.test', 'Paul', 'Durand', UserRole::JEWELER],
            ['joaillier2@maison.test', 'Nora', 'Benhamou', UserRole::JEWELER],
            ['joaillier3@maison.test', 'Hugo', 'Mercier', UserRole::JEWELER],
            ['sertisseur1@maison.test', 'Lina', 'Caron', UserRole::SETTER],
            ['sertisseur2@maison.test', 'Théo', 'Roussel', UserRole::SETTER],
            ['compta1@maison.test', 'Aïcha', 'Bourgeois', UserRole::ACCOUNTANT],
            ['compta2@maison.test', 'Damien', 'Fontaine', UserRole::ACCOUNTANT],
        ];

        $users = [];
        foreach ($defs as [$email, $first, $last, $role]) {
            $user = new User();
            $user->setEmail($email);
            $user->setFirstName($first);
            $user->setLastName($last);
            $user->setRoles([$role->value]);
            $user->setPassword($this->hasher->hashPassword($user, 'demo'));
            $manager->persist($user);
            $users[$role->name.'_'.count($users)] = $user;
        }

        return $users;
    }

    /** @return list<Client> */
    private function createClients(ObjectManager $manager): array
    {
        $clients = [];

        $maisons = [
            'Hôtel Le Solène', 'Maison Cartier (partenaire)', 'Joaillerie d\'Avignon',
            'Atelier Rivière', 'Maison Bréguet (commande spéciale)', 'Galerie d\'Estienne',
            'Hôtel Rocher Blanc', 'Maison Lalique (revente)', 'Joaillerie Mansart',
            'Atelier Saint-Honoré', 'Maison Marengo', 'Hôtel des Quatre-Vents',
            'Galerie Vendôme', 'Joaillerie de la Madeleine', 'Maison Aurélien',
        ];
        foreach ($maisons as $name) {
            $client = new Client();
            $client->setDisplayName($name);
            $client->setCompanyName($name);
            $client->setContactEmail($this->faker->companyEmail());
            $client->setContactPhone($this->faker->phoneNumber());
            $client->setAddress($this->faker->address());
            if ($this->faker->boolean(40)) {
                $client->setNotes($this->faker->paragraph(2));
            }
            $manager->persist($client);
            $clients[] = $client;
        }

        for ($i = 0; $i < 65; $i++) {
            $civility = $this->faker->boolean() ? 'Mme' : 'M.';
            $last = $this->faker->lastName();
            $client = new Client();
            $client->setDisplayName(sprintf('%s %s', $civility, $last));
            $client->setContactEmail($this->faker->email());
            $client->setContactPhone($this->faker->phoneNumber());
            $client->setAddress($this->faker->address());
            if ($this->faker->boolean(30)) {
                $client->setNotes($this->faker->paragraph(2));
            }
            $manager->persist($client);
            $clients[] = $client;
        }

        return $clients;
    }

    /**
     * @param array<string, User> $users
     * @param list<Client>        $clients
     */
    private function createProjects(ObjectManager $manager, array $users, array $clients): void
    {
        $designers = array_values(array_filter($users, fn (User $u) => $u->hasRole(UserRole::DESIGNER->value)));
        $jewelers = array_values(array_filter($users, fn (User $u) => $u->hasRole(UserRole::JEWELER->value)));
        $setters = array_values(array_filter($users, fn (User $u) => $u->hasRole(UserRole::SETTER->value)));
        $allInternal = array_values(array_filter($users, fn (User $u) => !$u->hasRole(UserRole::CLIENT->value)));

        $titles = [
            'Bague solitaire diamant', 'Alliance pavée diamants', 'Bague de fiançailles trilogie',
            'Chevalière monogramme', 'Bague toi & moi', 'Solitaire émeraude', 'Alliance demi-tour',
            'Bague jonc ciselée', 'Solitaire saphir Ceylan', 'Bague cocktail rubis',
            'Pavage diamants noirs', 'Bague signature collection', 'Alliance damassée',
            'Solitaire poire 2 ct', 'Bague art déco saphir',
        ];

        $referenceCounter = 1;
        $now = new \DateTimeImmutable();

        // 40 projets livrés sur 18 derniers mois
        for ($i = 0; $i < 40; $i++) {
            $deliveredAt = $now->modify(sprintf('-%d days', $this->faker->numberBetween(15, 540)));
            $createdAt = $deliveredAt->modify(sprintf('-%d days', $this->faker->numberBetween(45, 120)));
            $project = $this->buildProject(
                $referenceCounter++,
                $createdAt,
                $this->faker->randomElement($titles).' '.($this->faker->boolean(30) ? '— pièce unique' : ''),
                $this->faker->randomElement($clients),
                $designers, $jewelers, $setters,
                ProjectStatus::DELIVERED,
                ProjectStage::DELIVERY,
            );
            $project->setDeliveredAt($deliveredAt);
            $project->setTargetDeliveryDate($deliveredAt);

            $this->seedStageStatuses($project, $createdAt, $deliveredAt, ProjectStage::DELIVERY);
            $this->seedCommentsAndActivity($manager, $project, $allInternal, $createdAt, $deliveredAt);

            $manager->persist($project);
        }

        // 20 projets actifs répartis sur toutes les étapes
        $activeDistribution = [
            [ProjectStage::BRIEF, 2],
            [ProjectStage::SKETCH, 2],
            [ProjectStage::CLIENT_VALIDATION, 1],
            [ProjectStage::CAD_3D, 3],
            [ProjectStage::WAX_PROTOTYPE, 2],
            [ProjectStage::CASTING, 2],
            [ProjectStage::STONE_SETTING, 3],
            [ProjectStage::POLISHING, 2],
            [ProjectStage::QUALITY_CONTROL, 2],
            [ProjectStage::DELIVERY, 1],
        ];

        foreach ($activeDistribution as [$stage, $count]) {
            for ($i = 0; $i < $count; $i++) {
                $createdAt = $now->modify(sprintf('-%d days', $this->faker->numberBetween(7, 90)));
                $project = $this->buildProject(
                    $referenceCounter++,
                    $createdAt,
                    $this->faker->randomElement($titles),
                    $this->faker->randomElement($clients),
                    $designers, $jewelers, $setters,
                    ProjectStatus::ACTIVE,
                    $stage,
                );
                $project->setTargetDeliveryDate($now->modify(sprintf('+%d days', $this->faker->numberBetween(7, 90))));

                $this->seedStageStatuses($project, $createdAt, $now, $stage);
                $this->seedCommentsAndActivity($manager, $project, $allInternal, $createdAt, $now);

                $manager->persist($project);
            }
        }
    }

    /**
     * @param list<User> $designers
     * @param list<User> $jewelers
     * @param list<User> $setters
     */
    private function buildProject(
        int $counter,
        \DateTimeImmutable $createdAt,
        string $title,
        Client $client,
        array $designers, array $jewelers, array $setters,
        ProjectStatus $status,
        ProjectStage $currentStage,
    ): Project {
        $project = new Project();
        $year = (int) $createdAt->format('Y');
        $project->setReference(sprintf('BAG-%d-%03d', $year, $counter));
        $project->setTitle($title);
        $project->setClient($client);
        $project->setStatus($status);
        $project->setCurrentStage($currentStage);
        $project->setPriority($this->faker->randomElement([Priority::NORMAL, Priority::NORMAL, Priority::NORMAL, Priority::HIGH, Priority::URGENT]));
        $project->setBudgetTarget($this->faker->numberBetween(3_000_00, 35_000_00));
        $project->setSellingPrice((int) ($project->getBudgetTarget() * $this->faker->randomFloat(2, 1.30, 1.55)));
        $project->setDescription($this->faker->paragraph(3));
        $project->setAssignedDesigner($this->faker->randomElement($designers));
        $project->setAssignedJeweler($this->faker->randomElement($jewelers));
        if ($currentStage->position() >= ProjectStage::STONE_SETTING->position()) {
            $project->setAssignedSetter($this->faker->randomElement($setters));
        }

        // Forcer createdAt via reflection — il est privé et timestampé au new
        $ref = new \ReflectionProperty($project, 'createdAt');
        $ref->setValue($project, $createdAt);
        $refU = new \ReflectionProperty($project, 'updatedAt');
        $refU->setValue($project, $createdAt);

        return $project;
    }

    private function seedStageStatuses(Project $project, \DateTimeImmutable $from, \DateTimeImmutable $to, ProjectStage $currentStage): void
    {
        $stages = ProjectStage::ordered();
        $totalDays = max(1, $from->diff($to)->days);
        $perStage = max(1, (int) ($totalDays / $currentStage->position()));

        foreach ($stages as $stage) {
            $status = new ProjectStageStatus($stage);
            $status->setProject($project);

            if ($stage->position() < $currentStage->position()) {
                $startedAt = $from->modify(sprintf('+%d days', ($stage->position() - 1) * $perStage));
                $completedAt = $startedAt->modify(sprintf('+%d days', $perStage));
                $status->setStartedAt($startedAt);
                $status->setCompletedAt($completedAt);
            } elseif ($stage === $currentStage) {
                $status->setStartedAt($from->modify(sprintf('+%d days', ($stage->position() - 1) * $perStage)));
            }

            $project->addStageStatus($status);
        }
    }

    /** @param list<User> $users */
    private function seedCommentsAndActivity(ObjectManager $manager, Project $project, array $users, \DateTimeImmutable $from, \DateTimeImmutable $to): void
    {
        $count = $this->faker->numberBetween(3, 8);
        $span = max(1, $from->diff($to)->days);

        for ($i = 0; $i < $count; $i++) {
            $author = $this->faker->randomElement($users);
            $createdAt = $from->modify(sprintf('+%d days', $this->faker->numberBetween(0, $span)));

            $comment = new Comment();
            $comment->setProject($project);
            $comment->setAuthor($author);
            $body = $this->faker->randomElement([
                'Bon retour client, on continue.',
                'Le ton du rose vous convient ? Je peux lancer la fonte.',
                'Croquis v2 envoyé, j\'attends validation.',
                'Réception pierres prévue jeudi.',
                'Polissage terminé, contrôle qualité demain.',
                'Devis envoyé, relance prévue dans 5 jours.',
                'Modèle CAO bouclé, je prépare la cire.',
            ]);
            $comment->setBody($body);
            $ref = new \ReflectionProperty($comment, 'createdAt');
            $ref->setValue($comment, $createdAt);

            if ($this->faker->boolean(40)) {
                $mention = $this->faker->randomElement($users);
                if ($mention !== $author) {
                    $comment->addMention($mention);
                }
            }

            $manager->persist($comment);

            $activity = new ActivityLog();
            $activity->setProject($project);
            $activity->setActor($author);
            $activity->setEventType('comment.created');
            $activity->setPayload(['comment_excerpt' => mb_substr($body, 0, 100)]);
            $refA = new \ReflectionProperty($activity, 'createdAt');
            $refA->setValue($activity, $createdAt);
            $manager->persist($activity);
        }

        $taskCount = $this->faker->numberBetween(2, 5);
        for ($i = 0; $i < $taskCount; $i++) {
            $task = new Task();
            $task->setProject($project);
            $task->setTitle($this->faker->randomElement([
                'Commander or 18k jaune', 'Recevoir pierre centrale', 'Préparer écrin', 'Faire signer devis',
                'Photo après polissage', 'Programmer rendez-vous client', 'Préparer certificat',
            ]));
            $task->setAssignee($this->faker->randomElement($users));
            if ($this->faker->boolean(50)) {
                $task->setCompletedAt($from->modify(sprintf('+%d days', $this->faker->numberBetween(0, $span))));
                $task->setCompletedBy($task->getAssignee());
            } elseif ($this->faker->boolean()) {
                $task->setDueDate($to->modify(sprintf('+%d days', $this->faker->numberBetween(0, 30))));
            }
            $manager->persist($task);
        }
    }
}
