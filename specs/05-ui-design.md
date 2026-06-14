# 05 — UI / Design system

## Direction artistique

**Luxe chic, intemporel, atelier.** Pas de néon, pas d'effets clinquants. La
matière (or, cuir, papier ivoire, lignes typographiques) inspire l'interface.

## Palette

```
--color-ivory       #FAF8F3   /* fond principal */
--color-paper       #F5F2EB   /* surfaces alternées */
--color-ink         #1A1A1A   /* texte principal */
--color-ink-soft    #4A4A48   /* texte secondaire */
--color-ink-muted   #8A8784   /* texte tertiaire, hints */
--color-line        #E5DFD2   /* séparateurs fins */

--color-gold        #B8935A   /* accent principal — laiton patiné */
--color-gold-soft   #D4B584   /* accent doux, hover */
--color-gold-deep   #8E6E3E   /* accent appuyé, focus */

--color-success     #5B7A52   /* vert tilleul sombre */
--color-warning     #B07A2E   /* ambre */
--color-danger      #8E3B3B   /* bordeaux atténué */
```

Pas de blanc pur, pas de noir pur, pas de gris froid.

## Typographie

```css
--font-serif: "Cormorant Garamond", "EB Garamond", Georgia, serif;
--font-sans: "Inter", -apple-system, BlinkMacSystemFont, sans-serif;
--font-mono: "JetBrains Mono", ui-monospace, monospace;
```

- Chargées via Google Fonts en `<link>` dans le layout (no-build oblige).
- Titres `h1`-`h3` en sérif, weight 500-600, généreux en taille.
- Corps en sans, 16 px base, line-height 1.6.
- `font-feature-settings: "ss01", "ss02"` pour les chiffres tabulaires (utile
  pour les colonnes de prix).

### Échelle

```
--text-xs:   0.75rem
--text-sm:   0.875rem
--text-base: 1rem
--text-lg:   1.125rem
--text-xl:   1.375rem
--text-2xl:  1.75rem
--text-3xl:  2.25rem    /* h1 page */
--text-4xl:  3rem       /* hero éventuel */
```

## Espacement

Système 4 px : `--space-1: 4px`, `2: 8px`, `3: 12px`, `4: 16px`, `5: 24px`,
`6: 32px`, `7: 48px`, `8: 64px`.

Toujours préférer **plus** d'espace que moins. Le luxe respire.

## Composants

### Bouton primaire
Fond doré `--color-gold`, texte ivoire, padding généreux, pas d'ombre, légère
nuance dorée plus profonde au hover. Coins très légèrement arrondis (2 px).

### Bouton secondaire
Fond transparent, bordure 1 px ink-soft, texte ink. Hover : fond paper.

### Card
Fond paper, bordure 1 px line, pas d'ombre. Padding `--space-5`. Coins 2 px.

### Input
Bordure basse seulement (style « ligne d'écriture »), 1 px line. Focus :
ligne basse devient gold-deep. Pas de bordure complète, plus aérien.

### Tableau
Lignes alternées paper/ivory, lignes de séparation très fines, headers en
sérif italique petit corps.

### Tags / chips
Petite capsule, fond paper, bordure 1 px line, texte ink-soft, font-size xs.

## Iconographie

- Lucide via `symfony/ux-icons` ou SVG inline.
- Style stroke 1.5, jamais filled.
- Pas d'emoji dans l'UI.

## Layout général

### Largeur
`max-width: 1280px`, centré, padding latéral généreux. Mobile : tout en
pleine largeur, padding ramené à `--space-4`.

### Sidebar
- Sidebar gauche fixe sur desktop (220 px) avec navigation principale.
- Logo maison en haut (typo sérif).
- Items de menu en sans, weight 400, gold en actif.
- Badge non lus en bord droit.

### Topbar
- Recherche globale (cmd+K, Stimulus).
- Avatar user + menu.

### Page interne
- Header de page : titre h1 sérif + sous-titre sans + actions à droite.
- Espace généreux entre sections.
- Footer discret avec version + lien support.

## États interactifs

- **Hover** : transition 150 ms ease.
- **Focus** : `outline: 2px solid var(--color-gold-deep); outline-offset: 2px`.
- **Disabled** : opacité 0.5.
- **Loading** : skeleton ou indicateur typographique discret (« … »), pas de
  spinner.

## Responsive

- Breakpoints : `640px`, `960px`, `1280px`.
- Sidebar passe en drawer en dessous de 960px.
- Fiche projet à 2 colonnes au-dessus de 960px, pile verticale en dessous.

## Structure CSS

```
assets/
├── app.js
├── controllers/
│   ├── ...
└── styles/
    ├── app.css            # entrypoint, importe tout
    ├── reset.css          # reset moderne
    ├── tokens.css         # variables CSS (couleurs, espacements, polices)
    ├── typography.css     # styles de base typo
    ├── layout.css         # sidebar, topbar, main, grid utilities
    ├── components/
    │   ├── button.css
    │   ├── card.css
    │   ├── form.css
    │   ├── table.css
    │   ├── tag.css
    │   └── ...
    └── pages/
        ├── dashboard.css
        ├── project.css
        └── ...
```

Pas de framework CSS. Vraie maîtrise des styles, ~800-1200 lignes au total
attendu.

## Dark mode

**Non prévu en Phase 1**. La direction ivoire/doré est l'identité forte de
l'outil. Si demandé plus tard, prévoir une palette nuit (encre profonde, doré
patiné, lignes brunes) plutôt qu'une inversion simple.
