# 01 — Overview

## Contexte métier

Application interne pour une maison de **bijouterie luxe** spécialisée dans la
création de bagues. L'outil sert à piloter le cycle de vie complet d'une
création, du brief client à la livraison de la pièce finale, en gardant la
trace de tout ce qui se passe autour (communication interne, suivi finances,
matières premières).

## Volumétrie cible

- ~300 projets actifs simultanés
- Total annuel ~500 projets (livrés + actifs + abandonnés)
- 10-15 utilisateurs internes (designers, joaillier·ères, sertisseurs,
  commerciaux, comptables, admins)
- ~50 commentaires et ~20 fichiers par projet moyen

## Philosophie produit

Inspirée de **Basecamp** :

- Un seul outil, pas une suite. La fiche projet contient tout.
- La communication interne (commentaires, mentions) est au cœur, pas un
  module secondaire.
- Le dashboard d'accueil répond à une seule question : *« Qu'est-ce qui a bougé
  depuis la dernière fois que je suis passé ? »*
- Pas de notifications agressives. L'utilisateur choisit ce qu'il suit.
- Email + push navigateur en complément, jamais en remplacement de l'app.

## Utilisateurs et rôles

| Rôle | Responsabilités | Vue dominante |
|---|---|---|
| Admin | Configuration, gestion utilisateurs | Tableau de bord global |
| Commercial | Relation client, devis, suivi commercial | Liste projets + finances |
| Designer | Brief, croquis, CAO, validation client | Fiche projet + galerie |
| Joaillier·ère | Fabrication, fonte, polissage | Kanban étapes atelier |
| Sertisseur | Sertissage des pierres | File de travail filtrée |
| Comptable | Factures, paiements, marges, exports | Module finances |
| Client (Phase 5) | Suivi de son projet, validation, paiement | Portail dédié simplifié |

## Hors-scope

- Module RH / paie
- Caisse magasin / point de vente
- Catalogue e-commerce public
- Multi-société / multi-devise (français + euro uniquement)
