# 09 — Décisions tranchées

Format ADR léger. Chaque décision a un statut, une date, le contexte, la
décision prise, et les alternatives écartées.

---

## ADR-001 — Stack runtime : FrankenPHP

**Date** : 2026-06-14 · **Statut** : Acceptée

**Contexte.** Il faut une stack PHP capable d'héberger Symfony + Mercure, en
mode no-build, déployable simplement sur un serveur auto-hébergé.

**Décision.** Utiliser FrankenPHP (`dunglas/frankenphp:1-php8.4-alpine`) qui
embarque Caddy + PHP + plugin Mercure dans un seul binaire. Une seule image en
production.

**Alternatives écartées.**
- Nginx + PHP-FPM + container Mercure séparé : plus de pièces mouvantes.
- Apache + mod_php : daté.

---

## ADR-002 — BDD : SQLite

**Date** : 2026-06-14 · **Statut** : Acceptée

**Contexte.** Volumétrie cible faible (~300 projets actifs, ~10 users
simultanés). Demande explicite d'utiliser SQLite.

**Décision.** SQLite en mode WAL, fichier stocké sur volume persistant
`/storage/data_prod.db`. Backup = copie de fichier.

**Alternatives écartées.**
- PostgreSQL : surdimensionné, ajoute un container.
- MySQL : idem.

---

## ADR-003 — Frontend : Hotwire (Stimulus + Turbo), no-build

**Date** : 2026-06-14 · **Statut** : Acceptée

**Contexte.** Demande explicite de no-build via AssetMapper. Besoin
d'interactivité riche (drag & drop, autocomplete, fil live).

**Décision.** AssetMapper + Stimulus + Turbo. Mercure publie des Turbo Streams
pour le live. CSS custom artisanal, pas de framework.

**Alternatives écartées.**
- React / Vue : nécessite un bundler, contradiction avec la demande no-build.
- Tailwind v4 standalone : ajoute un binaire externe au build pour un gain
  faible vu le périmètre typographique du projet.
- Bootstrap : style trop générique et lourd pour une identité luxe sur-mesure.

---

## ADR-004 — Workflow étapes : fixe avec étapes optionnelles

**Date** : 2026-06-14 · **Statut** : Acceptée

**Contexte.** Le métier a un workflow standard (brief → croquis → ... →
livraison) mais certaines étapes sont parfois non applicables (ex: pas de
sertissage).

**Décision.** Enum PHP `ProjectStage` avec les 10 étapes en dur, entité
`ProjectStageStatus` par couple (projet, étape) avec flag `applicable`.

**Alternatives écartées.**
- Workflow entièrement configurable par projet : double la complexité du
  module pour un gain réel marginal.

---

## ADR-005 — Temps réel : Mercure intégré FrankenPHP

**Date** : 2026-06-14 · **Statut** : Acceptée

**Contexte.** Le dashboard et le fil projet doivent se mettre à jour sans
refresh. L'utilisateur veut éviter d'ajouter une brique d'infra externe.

**Décision.** Mercure activé via le plugin Caddy intégré à FrankenPHP en prod.
En dev, container Mercure séparé via `compose.yaml`.

**Alternatives écartées.**
- Polling Turbo Stream : moins fluide, gaspille des requêtes.
- WebSocket via Ratchet / autre : plus de code custom à maintenir.

---

## ADR-006 — Palette : doré/laiton sur ivoire

**Date** : 2026-06-14 · **Statut** : Acceptée

**Contexte.** L'application doit refléter le positionnement luxe chic de la
maison.

**Décision.** Ivoire `#FAF8F3` en fond, encre `#1A1A1A`, accent laiton patiné
`#B8935A`. Typographie Cormorant Garamond pour les titres, Inter pour le
corps.

**Alternatives écartées.**
- Bordeaux profond : trop sombre pour le dashboard quotidien.
- Noir + crème + or fin : très haute couture mais peut fatiguer en usage
  intensif.

---

## ADR-007 — Auth : `form_login` classique

**Date** : 2026-06-14 · **Statut** : Acceptée

**Contexte.** Petite PME, peu d'utilisateurs, pas de Google Workspace
nécessaire dans l'immédiat.

**Décision.** Email + mot de passe argon2id, `form_login` Symfony Security.

**Alternatives écartées.**
- OAuth Google : reportable. La porte reste ouverte si besoin futur.

---

## ADR-008 — PDF : Dompdf

**Date** : 2026-06-14 · **Statut** : Acceptée

**Contexte.** Génération de devis et factures avec une identité visuelle
soignée.

**Décision.** `dompdf/dompdf` en pur PHP. Template Twig dédié, palette
ivoire/doré transposée pour l'impression.

**Alternatives écartées.**
- PDFKit : lib JavaScript, hors-stack PHP.
- wkhtmltopdf via KnpSnappy : meilleur rendu mais ajoute un binaire de
  ~50 Mo à l'image. Réversible si le rendu Dompdf déçoit.
- Gotenberg : trop lourd pour le besoin.

---

## ADR-009 — Export compta : CSV libre

**Date** : 2026-06-14 · **Statut** : Acceptée

**Contexte.** Le cabinet comptable retraite les données. Pas besoin de FEC
normé à ce stade.

**Décision.** Export CSV configurable (sélection colonnes, période).
Implémenté via Symfony Serializer.

**Alternatives écartées.**
- FEC : lourd à maintenir, non demandé.

---

## ADR-010 — Hébergement : serveur Once + image GHCR

**Date** : 2026-06-14 · **Statut** : Acceptée

**Contexte.** L'utilisateur dispose d'un serveur Once auto-hébergé.

**Décision.** Image publiée sur `ghcr.io/florentdestremau/cd-projet` via CI
GitHub à chaque push master. Déploiement par `docker pull` + `docker run`
manuel sur le serveur, volume persistant `/storage`.

**Alternatives écartées.**
- PaaS (Platform.sh, Scalingo) : coût + dépendance.

---

## ADR-011 — Portail client : Phase 5 mais modèle préparé dès Phase 1

**Date** : 2026-06-14 · **Statut** : Acceptée

**Contexte.** Le portail client n'est pas prioritaire mais doit être possible
sans réécriture du modèle.

**Décision.** Entité `Client` peut référencer un `User` (relation
`linkedUser`) dès Phase 1. Les permissions sont séparées `internal` /
`external` dans les voters. Aucune route client tant que la Phase 5 n'est pas
lancée.

---

## ADR-012 — Volumétrie de fixtures : généreuse (60 projets, 13 users)

**Date** : 2026-06-14 · **Statut** : Acceptée

**Contexte.** Demande explicite d'un dataset réaliste pour démos et
développement.

**Décision.** ~13 users couvrant tous les rôles, 80 clients, 60 projets
(dont 20 actifs répartis sur toutes les étapes), commentaires + finances
cohérents. Détail dans `07-fixtures.md`.

**Alternatives écartées.**
- Minimal (3 users, 5 projets) : insuffisant pour valider les vues d'ensemble.
- Massif (300 projets) : surcoût de génération sans valeur ajoutée pour la
  Phase 1.
