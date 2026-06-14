# 04 — Modules fonctionnels

## A. Dashboard « Hey ! »

**Page d'accueil unique** après login. Inspirée Basecamp.

### Contenu, dans l'ordre vertical

1. **Mes mentions et réponses non lues** — cards avec extrait du commentaire,
   projet concerné, lien vers le fil.
2. **Activité récente sur les projets que je suis** — timeline groupée par
   projet (« 3 changements sur BAG-2026-042 »).
3. **Mes tâches** — checklist, tri par date d'échéance.
4. **Échéances projets** — 7 prochains jours, projets dont je suis assigné.
5. **Alertes finances** *(comptables et admins uniquement)* — factures impayées
   > 30 j, projets en dépassement budget.
6. **Activité globale du jour** *(collapsé par défaut)* — pour ceux qui veulent
   tout voir.

### Comportement

- Rechargement live via **Mercure** : un commentaire posté ailleurs apparaît
  sans refresh.
- Marquage « tout vu » par section pour ranger.
- Lien permanent vers chaque projet.

## B. Vue d'ensemble projets

### Vue Liste (par défaut)
- Tableau dense avec colonnes : Référence, Titre, Client, Étape, Échéance,
  Assigné, Marge prévue, Statut.
- Pagination 50 / page.
- Filtres en barre latérale gauche : statut, étape, assignations, client,
  urgence, fourchette de prix, retard.
- Recherche fulltext (titre, référence, nom client).
- Tri sur chaque colonne.

### Vue Kanban
- Une colonne par `ProjectStage`.
- Cards drag & drop entre colonnes → déclenche `stage_changed`.
- Cards condensées : référence + client + assigné + délai.

### Vue Calendrier
- Mois affiché.
- Une pastille par projet selon `targetDeliveryDate`.
- Couleur par urgence.

## C. Fiche projet

**Vue centrale.** Une seule URL : `/projets/{reference}`.

### Layout

```
┌─────────────────────────────────────────────────────────┐
│  BAG-2026-042  ●  CAO 3D                                │
│  Bague Solitaire — Mme Dubois                           │
│  Designer: Marie L.  ·  Joaillier: Paul D.              │
│  Livraison: 15 juillet 2026  ·  Budget: 12 500 €        │
├──────────────────────┬──────────────────────────────────┤
│                      │                                  │
│  FIL DE DISCUSSION   │  ÉTAPES (timeline horizontale)   │
│  ──────────────────  │  ●─●─●─◯─○─○─○─○─○─○            │
│  Marie · il y a 2h   │                                  │
│  « Validation OK »   │  TÂCHES                          │
│  📎 croquis_v3.jpg   │  ☑ Commander or 18k jaune       │
│                      │  ☐ Recevoir pierre centrale     │
│  Paul · hier         │                                  │
│  @Marie le ton du    │  DOCUMENTS (galerie)             │
│  rose vous convient  │  [croquis] [photo cire] [...]   │
│  ?                   │                                  │
│                      │  FINANCES                        │
│  [Nouveau message]   │  Devis: 12 500 € accepté        │
│                      │  Paiement: 6 250 € reçu          │
│                      │  Dépenses: 4 200 €               │
│                      │  Marge prévue: 8 300 €           │
│                      │                                  │
│                      │  MATIÈRES & PIERRES              │
│                      │  Or 18k jaune · 6g · 420 €       │
│                      │  Diamant 1.05ct VVS1 F · 3 800 € │
│                      │                                  │
│                      │  ACTIVITÉ TECHNIQUE              │
│                      │  (log discret, repliable)        │
└──────────────────────┴──────────────────────────────────┘
```

### Fonctionnalités clés

- **Fil de discussion** : nouveaux messages en bas, scroll infini vers le haut.
  Markdown léger (gras, italique, listes, liens). `@username` autocomplete
  Stimulus. Pièces jointes par drag & drop.
- **Suivi automatique** : commenter ou être mentionné → on suit le projet.
- **Étapes** : clic sur une étape → modal pour la marquer terminée / non
  applicable + commentaire optionnel.
- **Tâches** : ajout inline, drag & drop pour réordonner.
- **Documents** : grille de vignettes, lightbox pour ouvrir.
- **Finances** : visible selon rôle. Designers ne voient pas la marge.

## D. Module finances

### Devis
- Création depuis un projet, lignes manuelles ou pré-remplies avec matières /
  pierres / main-d'œuvre.
- Génération PDF Dompdf, template Twig avec entête maison.
- Envoi par mail au client (template HTML).
- Suivi statut (envoyé, accepté, refusé, expiré).

### Factures
- Génération depuis un devis accepté ou ex nihilo.
- PDF Dompdf.
- Enregistrement de paiements partiels.
- Relances automatiques *(Phase 4)*.

### Tableau de bord finances
- CA du mois (factures payées).
- CA prévisionnel (factures émises non payées).
- Top 5 projets les plus rentables.
- Top 5 projets en risque (dépassement budget).
- Factures dues à >30 j / >60 j / >90 j.

### Export CSV
- Factures sur période → CSV pour le cabinet comptable.
- Paiements sur période.
- Dépenses sur période.
- Format ouvert, configurable colonnes via UI.

## E. Communication

### Notifications

| Canal | Quand | Configurable |
|---|---|---|
| In-app (Mercure) | Toujours pour mentions et projets suivis | non |
| Push navigateur | Mention + changement d'étape projet suivi | oui (par type) |
| Mail immédiat | Mention si user offline > 15 min | oui |
| Mail digest | Quotidien (8h) ou hebdomadaire (lundi 8h) | oui |

### Préférences user
Page `/profil/notifications` :
- Activer/désactiver push par type d'événement
- Choisir digest (quotidien / hebdo / aucun)
- Plage de silence (ex: pas de mail entre 20h et 8h)

## F. Catalogues

### Clients
- Liste filtrable.
- Fiche client : coordonnées, projets liés, CA cumulé, notes internes.

### Fournisseurs
- Liste, fiche, projets/dépenses liés.

### Matières & pierres
- Catalogue avec prix de référence.
- Historique d'utilisation par projet.

## G. Administration (Admin uniquement)

- Gestion utilisateurs (création, rôles, désactivation).
- Configuration entreprise (logo, mentions légales devis/factures, taux TVA).
- Vue audit (qui a fait quoi).
