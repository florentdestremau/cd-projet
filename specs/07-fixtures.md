# 07 — Fixtures (dataset de démonstration)

## Objectif

Simuler un atelier de bijouterie luxe actif depuis 2 ans, avec un dataset
suffisamment riche pour :
- présenter l'outil en démo crédible,
- tester ergonomie et perfs sur volumes réalistes,
- développer chaque module avec des données qui parlent.

Tout est généré par `App\DataFixtures\*` via `doctrine/doctrine-fixtures-bundle`
et `fakerphp/faker` (locale `fr_FR`).

## Reset complet

Commande dédiée : `bin/console app:fixtures:reset` (alias projet de
`doctrine:fixtures:load --purge-with-truncate`) pour repartir d'un dataset
propre.

## Utilisateurs (13)

| Rôle | Nombre | Détails |
|---|---|---|
| Admin | 1 | `admin@maison-jeu.test` — mot de passe `demo` |
| Commerciaux | 2 | gèrent clients et devis |
| Designers | 3 | brief, croquis, CAO |
| Joaillier·ères | 3 | fabrication |
| Sertisseurs | 2 | sertissage |
| Comptables | 2 | factures, paiements, exports |

Tous ont `demo` comme mot de passe en dev. Avatars générés via DiceBear ou
images de marque générique stockées dans `/storage/uploads/avatars/`.

## Clients (80)

- 60 particuliers (noms français réalistes : Mme Dubois, M. Lefèvre, etc.)
- 15 maisons partenaires (joailleries, hôtels de luxe fictifs)
- 5 clients VIP avec historique riche

Coordonnées Faker `fr_FR`. Notes confidentielles plausibles pour 30 % d'entre
eux.

## Fournisseurs (12)

- 4 fournisseurs de pierres précieuses (Anvers, Genève, Paris)
- 3 fournisseurs de métaux (or, platine)
- 2 sous-traitants de fonte
- 1 sous-traitant de sertissage spécialisé
- 2 fournisseurs transverses (boîtes écrin, expédition assurée)

## Catalogue matières (20)

Or 18k jaune / blanc / rose (3 références), Or 14k jaune / blanc, Or 9k
(Royaume-Uni), Platine 950, Palladium, Argent 925. Prix au gramme cohérents
au cours du jour.

## Catalogue pierres (40)

- Diamants taille brillant : 0.25 / 0.5 / 0.75 / 1.0 / 1.5 / 2.0 ct, plusieurs
  qualités (VVS1 / VS1 / SI1 / D / E / F / G)
- Saphirs (bleu Ceylan, bleu royal, padparadscha, jaune, rose)
- Rubis (Birmanie, Mozambique)
- Émeraudes (Colombie, Zambie)
- Pierres semi-précieuses pour montage fantaisie (topaze, aigue-marine,
  tourmaline)

## Projets (60)

### Répartition

| Statut | Nombre | Profil |
|---|---|---|
| Livrés | 40 | distribués sur les 18 derniers mois |
| Actifs | 20 | tous étapes confondues, répartis pour avoir au moins 1 projet par étape |
| En attente | 0 | aucun par défaut (créer un cas via UI pour démo) |
| Annulés | 0 | idem |

### Profil de chaque projet actif

Pour les 20 projets actifs, distribution par étape :

| Étape | Nb projets |
|---|---|
| Brief | 2 |
| Croquis | 2 |
| Validation client | 1 |
| CAO 3D | 3 |
| Prototype cire | 2 |
| Fonte | 2 |
| Sertissage | 3 |
| Polissage | 2 |
| Contrôle qualité | 2 |
| Livraison | 1 |

### Contenu généré par projet

- **Référence** auto : `BAG-2025-XXX` ou `BAG-2026-XXX`
- **Titre** : tirage parmi templates (« Bague solitaire diamant », « Alliance
  pavée », « Bague de fiançailles trilogie », « Chevalière monogramme », « Bague
  toi & moi », ...)
- **Client** : tiré au sort dans la base clients
- **Designer / Joaillier / Sertisseur** assignés
- **Date de livraison cible** : entre J+7 et J+90 pour actifs
- **Budget** : entre 3 000 € et 35 000 € pour bagues classiques, jusqu'à
  120 000 € pour les VIP
- **Prix de vente** : marge cible 30-50 %
- **3 à 8 commentaires** par projet, datés de manière croissante, avec
  mentions inter-utilisateurs réalistes
- **2 à 5 tâches** par projet (mix terminées / en cours)
- **2 à 4 documents** : croquis (JPG fake), photos étapes, BL fournisseur
- **1 à 3 entrées matières / pierres** liées
- **Étapes** : toutes les étapes en amont de l'étape courante marquées
  `completedAt` cohérent

### Activité

Pour chaque projet actif, génération d'`ActivityLog` correspondant à chaque
commentaire, changement d'étape, document ajouté, tâche terminée. Distribution
temporelle réaliste (créneaux 8h-19h en semaine principalement).

### Finances

- **Tous les projets livrés** ont : 1 devis accepté, 1 facture, paiements
  complets.
- **Projets actifs ≥ étape Validation client** : devis accepté + 50 %
  d'acompte payé.
- **Projets actifs ≥ étape Livraison** : facture finale émise.
- **Dépenses imputées** sur 70 % des projets actifs : matières et pierres
  selon les liens, parfois sous-traitance.

## Notifications

Dataset de notifications : pour l'admin et 1-2 users de démo, générer 15-20
notifications, dont 5-8 non lues, mix mentions / changements d'étape / factures
payées.

## Volume résultant

- ~13 users
- ~80 clients
- ~60 projets
- ~250 commentaires
- ~150 tâches
- ~150 documents (la plupart factices, quelques images réelles libres pour
  rendre la galerie crédible)
- ~600 entries `ActivityLog`
- ~80 devis, ~70 factures, ~120 paiements, ~150 dépenses

Largement tenable sous SQLite.

## Reproductibilité

Seed Faker fixe (`Faker::create('fr_FR'); $faker->seed(2026)`) pour que le
dataset soit identique d'une exécution à l'autre. Permet aussi les tests
fonctionnels stables.

## Mode démo permanent

En option, prévoir une commande qui re-génère le dataset avec des dates
glissantes (les projets « actifs » sont toujours dans une fenêtre J-30 / J+60)
pour des démos vivantes.
