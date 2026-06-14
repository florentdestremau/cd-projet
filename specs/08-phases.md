# 08 — Phases de livraison

Découpage indicatif. Chaque phase produit un livrable testable de bout en bout.

## Phase 0 — Bootstrap (avant tout code métier)

**But** : poser le squelette technique et l'environnement de dev/CI.

- `composer create-project` Symfony 8.0 + structure recommandée
- Dockerfile FrankenPHP + Caddyfile (copies depuis `ag-voter-sf`)
- `compose.yaml` + `compose.override.yaml` pour dev local (Mercure + Mailpit)
- GitHub Actions : `ci.yml` (lint + tests) et `docker.yml` (build & push GHCR)
- AssetMapper configuré, importmap sans Bootstrap
- Structure CSS `assets/styles/` posée avec tokens et reset
- Layout Twig de base (sidebar + topbar + main) avec palette appliquée
- Page d'accueil de test affichant la typo finale
- Doctrine + SQLite + première migration vide

**Livrable** : `docker compose up` lance l'app à `localhost:8000` avec la
page d'accueil dans le style définitif, et un push sur master pousse une image
sur GHCR.

## Phase 1 — Fondations métier (MVP utilisable)

**But** : pouvoir créer un projet, le faire avancer, communiquer dessus.

- Entités `User`, `Client`, `Project`, `ProjectStageStatus`, `Task`,
  `Comment`, `ActivityLog`
- Auth `form_login` + 6 rôles
- Voters Symfony pour permissions de base
- CRUD clients
- CRUD projets (création + édition + assignations)
- Fiche projet complète : header + fil de commentaires + étapes + tâches
- Mentions `@user` avec autocomplete Stimulus
- Activity log alimenté à chaque action significative
- Dashboard « Hey ! » basique (mentions non lues, mes tâches, mon activité)
- Vue liste projets avec filtres essentiels
- Fixtures complètes (cf. doc 07) au moins pour users / clients / projets de
  base

**Livrable** : un utilisateur peut se connecter, créer un projet pour un
client, le commenter en mentionnant des collègues, faire avancer les étapes,
et voir ce qui a bougé depuis sa dernière visite.

## Phase 2 — Communication & temps réel

**But** : faire de l'outil un canal de comm interne vivant.

- Intégration Mercure côté serveur (publication d'événements)
- Turbo Streams sur Mercure pour live-update du dashboard et du fil projet
- Symfony Notifier + WebPush bridge
- Page `/profil/notifications` (préférences par type d'événement)
- Cron / Messenger pour digest mail (quotidien + hebdo)
- Mail immédiat sur mention si user offline > 15 min
- Vue Kanban projets (drag & drop entre étapes)

**Livrable** : ouvrir 2 onglets ; commenter dans le premier ; voir apparaître
en temps réel dans le second. Recevoir un push navigateur sur mention.

## Phase 3 — Finances

**But** : couvrir le suivi devis / factures / paiements / marges.

- Entités `Quote`, `QuoteItem`, `Invoice`, `Payment`, `Expense`
- `Material`, `Stone`, `Supplier`, `ProjectMaterial`, `ProjectStone`
- UI création devis depuis un projet (lignes manuelles ou pré-remplies)
- Génération PDF Dompdf (template Twig sobre, papier ivoire + accent doré)
- Envoi devis par mail
- Suivi statuts devis / factures
- Enregistrement paiements partiels
- Tableau de bord finances (CA, marges, factures dues)
- Export CSV (factures, paiements, dépenses)
- Fixtures finances enrichies

**Livrable** : produire un devis PDF imprimable, le marquer accepté, générer
la facture, enregistrer le paiement, voir la marge réelle du projet.

## Phase 4 — Catalogues & polish atelier

**But** : rendre l'outil agréable au quotidien à l'atelier.

- Module catalogue matières / pierres avec UI complète
- Sélection matières / pierres au sein d'un projet avec coût figé
- Module documents : galerie projet, lightbox, drag & drop d'upload, gestion
  catégories
- Vue calendrier des projets
- Recherche fulltext basique (LIKE multi-champs)
- Vue fournisseurs
- Page d'audit pour admins (qui a fait quoi)

**Livrable** : on peut entièrement piloter le métier sans quitter l'outil.

## Phase 5 — Portail client + finitions

**But** : ouverture maîtrisée vers les clients.

- Espace `/client/{token}` (auth dédiée, session distincte)
- Vue avancement projet limitée (étapes, croquis publiables, devis, factures)
- Validation client d'une étape directement depuis le portail
- Paiement en ligne (Stripe ou virement instructions)
- Notifications mail au client à chaque jalon
- Personnalisation entreprise (logo, mentions légales, taux TVA, signature
  mail)
- Plus de finitions UX repérées en usage

**Livrable** : un client reçoit un lien vers son projet, voit l'avancement,
valide les croquis et reçoit ses factures.

## Hors phases planifiées (parking)

- Mode hors-ligne PWA
- Intégrations Pennylane / Sellsy
- Multi-société
- Export FEC normé
- Module RH
