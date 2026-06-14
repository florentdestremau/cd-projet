# 03 — Modèle de domaine

## Entités principales

### `User`
Utilisateur interne ou client (Phase 5). Distinction par champ `type` ou
implémentation `UserInterface` dédiée — à arbitrer en début de Phase 1.

| Champ | Type | Notes |
|---|---|---|
| id | int | PK |
| email | string | unique |
| password | string | argon2id |
| firstName / lastName | string | |
| roles | json | `ROLE_ADMIN`, `ROLE_COMMERCIAL`, `ROLE_DESIGNER`, `ROLE_JEWELER`, `ROLE_SETTER`, `ROLE_ACCOUNTANT`, `ROLE_CLIENT` |
| avatar | string? | chemin Vich |
| pushSubscriptions | OneToMany | abonnements Web Push |
| notificationPrefs | json | granularité par type d'événement |
| createdAt | datetime | |

### `Client`
Commanditaire d'un projet (particulier ou maison).

| Champ | Type | Notes |
|---|---|---|
| id | int | PK |
| displayName | string | "Mme Dubois", "Maison Cartier" |
| companyName | string? | si pro |
| contactEmail | string? | |
| contactPhone | string? | |
| address | text? | |
| notes | text? | confidentielles |
| linkedUser | OneToOne? User | si portail client activé (Phase 5) |
| createdAt | datetime | |

### `Project`
Cœur du système : une création de bague.

| Champ | Type | Notes |
|---|---|---|
| id | int | PK |
| reference | string | unique, format `BAG-2026-042`, auto-généré |
| title | string | "Bague solitaire diamant 1ct" |
| client | ManyToOne Client | |
| status | enum | `ACTIVE`, `DELIVERED`, `ON_HOLD`, `CANCELLED` |
| currentStage | enum ProjectStage | étape en cours |
| priority | enum | `NORMAL`, `HIGH`, `URGENT` |
| targetDeliveryDate | date? | |
| deliveredAt | datetime? | |
| budgetTarget | int | centimes |
| sellingPrice | int | centimes, prix de vente final |
| description | text? | brief client |
| assignedDesigner | ManyToOne? User | |
| assignedJeweler | ManyToOne? User | |
| assignedSetter | ManyToOne? User | |
| createdAt / updatedAt | datetime | |

### `ProjectStageStatus`
Trace de chaque étape pour un projet donné. Workflow **fixe** mais étapes
**marquables non applicables**.

| Champ | Type | Notes |
|---|---|---|
| id | int | PK |
| project | ManyToOne Project | |
| stage | enum ProjectStage | l'étape concernée |
| applicable | bool | si false → skippée |
| startedAt | datetime? | |
| completedAt | datetime? | |
| notes | text? | |

#### Enum `ProjectStage` (ordonné)
1. `BRIEF` — Brief client
2. `SKETCH` — Croquis / dessin
3. `CLIENT_VALIDATION` — Validation client du croquis
4. `CAD_3D` — Modélisation CAO 3D
5. `WAX_PROTOTYPE` — Prototype cire
6. `CASTING` — Fonte
7. `STONE_SETTING` — Sertissage
8. `POLISHING` — Polissage
9. `QUALITY_CONTROL` — Contrôle qualité
10. `DELIVERY` — Livraison

### `Task`
Tâches à cocher liées à un projet.

| Champ | Type | Notes |
|---|---|---|
| id | int | PK |
| project | ManyToOne Project | |
| title | string | |
| description | text? | |
| assignee | ManyToOne? User | |
| dueDate | date? | |
| completedAt | datetime? | |
| completedBy | ManyToOne? User | |

### `Comment`
Message dans le fil de discussion d'un projet. Cœur Basecamp.

| Champ | Type | Notes |
|---|---|---|
| id | int | PK |
| project | ManyToOne Project | |
| author | ManyToOne User | |
| body | text | Markdown léger + @mentions |
| mentions | ManyToMany User | utilisateurs mentionnés (parsé à l'enregistrement) |
| attachments | OneToMany Document | |
| createdAt | datetime | |
| editedAt | datetime? | |

### `Document`
Fichier attaché : croquis, photo d'étape, plan CAO, BL fournisseur, facture
scannée.

| Champ | Type | Notes |
|---|---|---|
| id | int | PK |
| project | ManyToOne? Project | nullable si attaché à autre chose |
| comment | ManyToOne? Comment | si pièce jointe inline |
| filename | string | nom original |
| storagePath | string | géré par Vich, dans `/storage/uploads/` |
| mimeType | string | |
| size | int | octets |
| category | enum | `SKETCH`, `PHOTO`, `CAD`, `INVOICE`, `OTHER` |
| uploadedBy | ManyToOne User | |
| uploadedAt | datetime | |

### `ActivityLog`
Journal d'événements pour le fil d'activité du dashboard.

| Champ | Type | Notes |
|---|---|---|
| id | int | PK |
| project | ManyToOne? Project | nullable pour événements globaux |
| actor | ManyToOne User | |
| eventType | string | `comment.created`, `project.stage_changed`, `task.completed`, `document.uploaded`, `invoice.paid`, etc. |
| payload | json | métadonnées spécifiques au type |
| createdAt | datetime | |

### `Notification`
Notification destinée à un user (in-app, mail, push).

| Champ | Type | Notes |
|---|---|---|
| id | int | PK |
| recipient | ManyToOne User | |
| activityLog | ManyToOne? ActivityLog | source |
| readAt | datetime? | null = non lue |
| channels | json | canaux où elle a été envoyée |
| createdAt | datetime | |

### `Mention`
Liaison commentaire ↔ user mentionné (peut être inférée mais entité explicite
facilite les notifs).

## Catalogues

### `Material`
| Champ | Type |
|---|---|
| id, name, type (`OR`, `ARGENT`, `PLATINE`, `PALLADIUM`), karat, pricePerGram, supplier (FK) |

### `Stone`
| Champ | Type |
|---|---|
| id, type (`DIAMANT`, `SAPHIR`, `RUBIS`, `EMERAUDE`, ...), caratWeight, quality, color, certificateRef?, costPrice, supplier (FK) |

### `Supplier`
| Champ | Type |
|---|---|
| id, name, contactEmail, contactPhone, specialty (`STONES`, `METALS`, `CASTING`, `SETTING`, `OTHER`), notes |

### `ProjectMaterial` (join + quantité)
Liens projet ↔ matière avec quantité utilisée et coût figé.

### `ProjectStone` (join + quantité)
Liens projet ↔ pierre avec quantité utilisée et coût figé.

## Finances

### `Quote` (devis)
| Champ | Type | Notes |
|---|---|---|
| id | int | PK |
| project | ManyToOne Project | |
| reference | string | `DEV-2026-042` |
| status | enum | `DRAFT`, `SENT`, `ACCEPTED`, `DECLINED`, `EXPIRED` |
| validUntil | date? | |
| items | OneToMany QuoteItem | |
| totalHt / totalTtc | int | centimes |
| vatRate | int | en points × 100 (2000 = 20.00 %) |
| sentAt / acceptedAt | datetime? | |
| pdfPath | string? | dernier PDF généré |

### `QuoteItem`
`description, quantity, unitPriceHt, totalHt`

### `Invoice` (facture)
Même structure que `Quote` mais avec `dueDate`, `paidAt?` et lien optionnel vers
le devis source.

### `Payment`
| Champ | Type | Notes |
|---|---|---|
| invoice | ManyToOne Invoice | |
| amount | int | centimes |
| method | enum | `TRANSFER`, `CARD`, `CASH`, `CHECK`, `OTHER` |
| receivedAt | datetime | |
| reference | string? | n° virement, etc. |

### `Expense` (dépense imputée à un projet)
| Champ | Type | Notes |
|---|---|---|
| project | ManyToOne Project | |
| supplier | ManyToOne? Supplier | |
| category | enum | `MATERIAL`, `STONE`, `SUBCONTRACT`, `SHIPPING`, `OTHER` |
| amountHt | int | centimes |
| vatAmount | int | centimes |
| occurredAt | date | |
| description | string | |
| documentPath | string? | scan justificatif |

## Calculs dérivés (services)

- **Marge projet** = (`sellingPrice` − Σ`Expense.amountHt`) — calculé live
- **Avancement %** = ratio étapes complétées / étapes applicables
- **Solde dû** = `Invoice.totalTtc` − Σ`Payment.amount`
- **CA mensuel** = Σ`Invoice.totalHt` où `Invoice.paidAt` ∈ mois

## Index / contraintes notables

- Index sur `Project.status` + `Project.currentStage` (filtres dashboard)
- Index sur `Comment.project` + `Comment.createdAt DESC`
- Index sur `ActivityLog.createdAt DESC`
- Index sur `Notification.recipient` + `Notification.readAt`
- Unique sur `Project.reference`, `Quote.reference`, `Invoice.reference`
