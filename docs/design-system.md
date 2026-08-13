# AleChemist — système de design "L'instrument"

Direction validée le 2026-08-13. Ce fichier est le contrat : toute page doit en dériver ses couleurs, ses tailles et ses composants. Aucune valeur en dur hors de ces tokens.

## Le sujet, en une phrase

Un brasseur amateur, seul dans sa cuisine ou son garage, téléphone dans une main, chronomètre en cours, doit ajouter le bon houblon à la bonne minute sans se tromper d'étape. L'app est un **appareil de mesure**, pas un site de recettes.

Conséquences non négociables :
- Mobile d'abord. Tout doit être lisible à un bras de distance, cliquable avec un pouce mouillé (cibles 44 px minimum).
- Les nombres sont le contenu principal. Ils ont leur propre police, en chiffres tabulaires.
- Un seul élément vit à l'écran à la fois : ce qu'il faut faire maintenant.

## Couleur

Palette de marque conservée, étendue en surfaces pour un fond sombre.

| Token | Hex | Rôle |
|---|---|---|
| `--color-ink` | `#0E0E14` | Fond de page |
| `--color-panel` | `#17171F` | Surface surélevée (bloc, ligne) |
| `--color-panel-hi` | `#1F1F29` | Survol, état actif |
| `--color-rule` | `#2A2A36` | Filets, séparateurs, contours |
| `--color-signal` | `#F3AE1A` | **Signal unique** : étape en cours, chrono actif, action principale |
| `--color-ember` | `#CA6018` | Alerte : échéance dépassée, retard, destruction |
| `--color-malt` | `#E2C05D` | Fait, terminé, accent secondaire |
| `--color-text` | `#ECECF0` | Texte principal |
| `--color-text-dim` | `#9A9AA8` | Texte secondaire, unités, légendes |
| `--color-text-faint` | `#6A6A78` | Texte désactivé, méta |

Règle de discipline : **`--color-signal` ne sert qu'à une chose par écran**. Si deux éléments sont ambre, l'un des deux a tort. Le vert n'existe pas dans cette interface ; "fait" se dit en `--color-malt` et par une coche, pas par une couleur de validation générique.

La couleur EBC (`ebcToHex`) est l'**identité d'une recette** : c'est la couleur réelle du moût. Elle n'est jamais décorative, elle n'apparaît que là où elle identifie une recette ou un brassin.

## Typographie

Quatre rôles, quatre usages stricts.

| Rôle | Police | Usage |
|---|---|---|
| Marque | **Shangrila** (locale, déjà dans le projet) | Le bloc-marque dans la barre latérale. **Nulle part ailleurs** : illisible sous 24 px. |
| Display | **Archivo** 700/800, `letter-spacing: -0.02em`, majuscules | Noms d'étape, titres de section, libellés de boutons |
| Texte | **Figtree** 400/500/600 | Phrases, descriptions, formulaires |
| Données | **IBM Plex Mono** 400/500, `font-variant-numeric: tabular-nums` | **Tous** les nombres : chrono, densités, températures, quantités, durées, dates |

Archivo et IBM Plex Mono se chargent depuis fonts.bunny.net, comme Figtree aujourd'hui.

Échelle (mobile → desktop) :

| Token | Taille | Usage |
|---|---|---|
| `--text-chrono` | `clamp(3.5rem, 18vw, 5.5rem)` | Le chronomètre, et lui seul |
| `--text-metric` | `clamp(1.75rem, 7vw, 2.5rem)` | Une valeur mise en avant (compteur, densité en édition) |
| `--text-step` | `1.125rem` | Nom d'étape (Archivo, majuscules) |
| `--text-body` | `1rem` | Texte courant |
| `--text-label` | `0.8125rem` | Légendes, unités, méta |

## Mise en page

- Colonne de contenu **`max-width: 40rem`, centrée**. Sur grand écran on ne s'étale pas : un instrument a une façade, pas un tableur.
- Barre latérale desktop réduite à un **rail de 4.5rem** (icônes + bloc-marque), déployée au survol. Sur mobile, barre basse fixe avec les 3 destinations et le pouce à portée.
- Espacement : rythme de 4 px, respiration généreuse verticalement (`space-y-6` entre blocs), serrée dans une ligne de donnée.
- Rayons : `--radius: 0.5rem` sur les blocs, `9999px` sur les pastilles d'état. Pas d'ombres portées : sur fond sombre, la profondeur se fait par la surface (`--color-panel`) et le filet (`--color-rule`), jamais par un `box-shadow` diffus.

## Les composants

### 1. `StepSpine` — la colonne vertébrale du brassin

Le brassage **est** une séquence : la numérotation est donc porteuse d'information, pas décorative.

```
●───●───◉───○───○     3/5
prép  emp  ÉBU  lev  ferm
```

- Étape faite : point plein `--color-malt`, libellé `--color-text-dim`
- Étape en cours : anneau `--color-signal`, libellé Archivo majuscules `--color-signal`
- Étape à venir : point creux `--color-rule`, libellé `--color-text-faint`
- Le trait de liaison est en `--color-malt` jusqu'à l'étape courante, en `--color-rule` ensuite
- Cliquable en arrière (consultation), jamais en avant

Présent en haut de chaque écran de brassin. Remplace la rangée de pastilles actuelle.

### 2. `BoilTrack` — LA signature

Pendant l'ébullition, une piste temporelle horizontale de T-60 jusqu'au flame-out. Chaque ajout de houblon est un repère à sa minute. Une tête de lecture avance en temps réel.

```
T-60              T-15    FLAME-OUT
●━━━━━━━━━━━━━━━━━●━━━━━━▶│
Cascade           Citra
```

- Repère à venir : point `--color-text-dim`
- Repère dû maintenant (fenêtre de 60 s) : point `--color-signal`, halo pulsant lent (1,6 s), **désactivé si `prefers-reduced-motion`**
- Repère dépassé non coché : point `--color-ember`
- Repère coché : point `--color-malt`, opacité réduite
- La tête de lecture est un trait fin `--color-signal` qui progresse ; le rail restant est en `--color-rule`

C'est l'élément qu'on doit retenir de cette app. Rien d'autre à l'écran ne doit lui disputer l'attention.

### 3. `Chrono` — le compte à rebours

`--text-chrono`, IBM Plex Mono, tabulaire, `--color-signal` quand il tourne, `--color-ember` quand le temps est écoulé.
Format `MM:SS` sous une heure. Sous la minute, les secondes passent en `--color-ember`.
**Les chiffres ne s'animent pas** : la transformation d'un chiffre à chaque seconde donne une impression de tremblement. Seule la barre de progression se déplace.

### 4. `DataRow` — une ligne de donnée

Remplace les tableaux à bandeau noir. Une ligne = une action ou une mesure.

```
┌──────────────────────────────────┐
│ ☐  30 g   Cascade      dans 27'  │
└──────────────────────────────────┘
   ^   ^      ^              ^
  case │    nom(Figtree)   échéance(mono, dim)
     quantité (mono)
```

- Fond `--color-panel`, filet gauche de 3 px qui porte l'état : transparent (à faire), `--color-malt` (fait), `--color-ember` (en retard)
- Hauteur minimale 56 px, toute la ligne est cliquable
- Sur une recette (lecture seule), pas de case : la quantité passe à gauche

### 5. `RecipeCard` — identité par la couleur du moût

Bandeau vertical de 4 px à la couleur EBC réelle, nom en Archivo, méta en mono. La couleur de la bière **est** la vignette.

### 6. Boutons

- Principal : fond `--color-signal`, texte `--color-ink`, Archivo majuscules, hauteur 48 px, pleine largeur sur mobile
- Secondaire : contour `--color-rule`, texte `--color-text`
- Destructif : texte `--color-ember`, contour `--color-ember` au survol
- Désactivé : fond `--color-panel`, texte `--color-text-faint`, **et une raison affichée dessous** (« Cochez toutes les lignes », « Le chrono n'est pas terminé ») — un bouton grisé sans explication est un cul-de-sac

## Écriture

- Impératif, à la deuxième personne, phrase courte. « Démarrez le chrono », pas « Démarrage du chronomètre ».
- Le libellé d'une action ne change pas entre le bouton et le résultat.
- Un état vide est une invitation : « Aucun brassin en cours. Choisissez une recette pour commencer. » plus un lien.
- Une erreur dit ce qui s'est passé et quoi faire : « La densité doit être un nombre. » pas « Oups ! Un problème est survenu. »
- Les unités sont en `--color-text-dim`, jamais en majuscules : `20 l`, `67 °C`, `1.052`.

## Plancher de qualité

- Contraste AA sur tous les textes (l'ambre `#F3AE1A` sur `#0E0E14` est à 9,8:1 ; le texte dim `#9A9AA8` à 6,4:1).
- Focus clavier visible partout : anneau `--color-signal` de 2 px, décalé de 2 px.
- `prefers-reduced-motion` respecté : pulsation et transitions supprimées, la tête de lecture saute au lieu de glisser.
- Cibles tactiles 44 px minimum.
- Rien ne dépend de la seule couleur : le retard se dit aussi par le mot « en retard », pas seulement par l'ambre brûlé.
