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

- Colonne de contenu **`max-width: 40rem`, centrée**, sur **tous** les écrans. Sur grand écran on ne s'étale pas : un instrument a une façade, pas un tableur. Seule exception, les écrans invités (connexion, inscription...) à `24rem` : une carte de connexion à 40rem n'aurait pas de sens.
- Barre latérale desktop : rail de **12.5rem**, icône et libellé visibles en permanence. Sur mobile, barre basse fixe avec le pouce à portée. Sa hauteur est déclarée une seule fois, `--nav-height` : la mise en page et la barre d'action s'y accrochent.
- **Trois destinations**, les mêmes sur les deux formats : Tableau de bord, Recettes, Profil. Il y en a eu une quatrième, Brassins ; elle redisait ce que le tableau de bord montre déjà, sa page a été versée dedans.
- Tout ce qui vit sous une recette allume l'onglet Recettes, brassins compris : un écran doit toujours dire dans quelle section on se trouve.

### La charpente d'une page

Tout écran est monté sur `PageHeader`, dans cet ordre, sans exception :

```
TITRE                    [+]   le titre, et au plus une action secondaire
méta                           à sa droite
```

### Les deux emplacements d'action

**L'action principale est ancrée en bas** (`PageAction`), sous le pouce, **fixe** : elle ne bouge pas d'un pixel pendant le défilement, y compris en fin de page. C'est la place qu'occupait déjà « Passer à l'étape suivante » : tous les écrans s'y alignent. Un bouton désactivé y affiche sa raison. Une cale de même hauteur, mesurée, reste dans le flux pour que la fin du contenu ne se cache pas derrière la barre.

**L'action secondaire vit en haut à droite** de l'en-tête, sur la ligne du titre, avec son libellé précédé de son icône. Quand les deux ne tiennent pas ensemble, elle descend sur sa propre ligne, toujours à droite, plutôt que de rogner l'un des deux.

| Écran | En bas (principale) | En haut à droite (secondaire) |
|---|---|---|
| Tableau de bord | aucune | aucune |
| Recettes | Importer une recette | aucune |
| Fiche recette | Brasser cette recette | Liste des brassins |
| Brassins d'une recette | Nouveau brassin | aucune |
| Étape de brassin | Passer à l'étape suivante | Note (icône seule) |
| Récap terminé | Brasser à nouveau | aucune |
| Formulaires | leur bouton d'envoi, précédé d'Annuler | aucune |
| Profil | aucune | Se déconnecter |

Seule exception au libellé, `.btn--icon` : l'icône seule, à toutes les tailles, pour une action secondaire récurrente dont le libellé n'apprend rien. Aujourd'hui la note d'un brassin, et elle seule.

Le poids d'une action se dit par la couleur, jamais par la taille : ambre plein pour l'action principale, contour pour une secondaire.

Les icônes vivent toutes dans `AppIcon.vue` : même grille de 24, même trait de 1,5, mêmes extrémités arrondies.

Sous 1024 px, la barre de défilement du navigateur est masquée (le défilement, lui, est intact) : un téléphone la dessine en surimpression, alors que Chrome en mode mobile en pose une permanente de 15 px le long du contenu.

### Pas de lien de retour

**Aucun écran ne porte de flèche de retour.** Calculée sur le trajet parcouru, elle changeait de libellé d'un passage à l'autre ; calquée sur la hiérarchie des URL, elle refaisait le travail de la barre de navigation. Dans les deux cas, un repère qui ne tient pas en place. On quitte un écran par la barre de navigation, un formulaire par son bouton **Annuler**, et un dialogue par sa fermeture.

Ce qui remplace le retour, c'est le **nom de l'entité parente, en lien, dans la ligne de méta** : la recette sur un écran de brassin et sur un récap, la recette sur la liste de ses brassins. Il ne dit pas d'où l'on vient, il dit à quoi appartient ce qu'on regarde.

Un écran transitoire (formulaire, dialogue) n'a pas de retour du tout : il a une sortie, à côté de son enregistrement.

Sur mobile, l'action d'en-tête se réduit à son icône (`.btn--icon`) : en bouton pleine largeur elle mangeait une rangée entière de l'écran où l'on brasse.

Une seule action par écran, et toujours au même endroit. Pas de bouton flottant : sa position variait d'un écran à l'autre et il entrait en concurrence avec le bouton principal de bas de page sur les écrans de brassin.

### La note est un dialogue

La note d'un brassin s'écrit dans un dialogue posé sur l'écran courant (`NoteDialog`), ouvert depuis l'icône de l'en-tête ou depuis le récap d'un brassin terminé. Elle occupait un écran entier : on quittait l'étape en cours pour écrire trois mots, et il fallait ensuite un chemin pour revenir.

### Où vit la suppression

Un brassin ne se supprime **que depuis la liste des brassins d'une recette**, par le bouton en fin de carte (`BrewingCard`). Ni depuis le dialogue de note, ni depuis un récap, ni depuis le tableau de bord : une action destructive n'a rien à faire au milieu d'un écran de saisie, de consultation ou de coup d'oeil.
- Espacement : rythme de 4 px, respiration généreuse verticalement (`space-y-6` entre blocs), serrée dans une ligne de donnée.
- Rayons : `--radius: 0.5rem` sur les blocs, `9999px` sur les pastilles d'état. Pas d'ombres portées : sur fond sombre, la profondeur se fait par la surface (`--color-panel`) et le filet (`--color-rule`), jamais par un `box-shadow` diffus.

## Les composants

### 1. `StepSpine` — la colonne vertébrale du brassin

Le brassage **est** une séquence : la numérotation est donc porteuse d'information, pas décorative. Mais la première question du brasseur n'est pas « combien d'étapes », c'est « où j'en suis » : elle est répondue en toutes lettres, pas en pictogrammes.

```
ÉTAPE 2 SUR 5
EMPÂTAGE                        Archivo 1.5rem, --color-signal
▬▬▬▬  ▬▬▬▬  ────  ────  ────    5 segments de 6px, cible tactile de 44px
prép  emp   ébu   lev   ferm    libellés à partir de 640px seulement
```

- Segment franchi : `--color-malt`, **cliquable** (consultation), jamais en avant
- Segment courant : `--color-signal`
- Segment à venir : `--color-rule`
- Sous 640 px les libellés ne sont lus que par les lecteurs d'écran : l'étape courante est déjà nommée en grand au-dessus, et cinq mots sous cinq segments de 67 px étaient illisibles

La version précédente en points reliés (repères de 12 px, libellés de 11 px, traits d'un pixel) disait « voici cinq étapes » sans répondre à la question posée.

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

### 5. `RecipeCard` et `BrewingCard` — identité par la couleur du moût

Bandeau vertical de 4 px à la couleur EBC réelle, nom en Archivo, méta en mono. La couleur de la bière **est** la vignette.

`BrewingCard` est la carte unique des trois endroits qui affichent des brassins (onglet Brassins, brassins d'une recette, tableau de bord) : chacun avait son propre balisage, d'où des cartes tantôt avec filet EBC tantôt sans. Elle porte le bouton de suppression, sauf sur le tableau de bord (`:deletable="false"`), qui est un écran de coup d'oeil.

### 6. Boutons

- Principal : fond `--color-signal`, texte `--color-ink`, Archivo majuscules, hauteur **minimale** 48 px, pleine largeur sur mobile
- Secondaire : contour `--color-rule`, texte `--color-text`
- Destructif : texte `--color-ember`, contour `--color-ember` au survol
- Désactivé : fond `--color-panel`, **filet `--color-rule`** (sans lui, un bouton posé sur une carte disparaît et se lit comme un titre), texte `--color-text-faint`, **et une raison affichée dessous quand l'écran ne la montre pas déjà** (« Le chrono n'est pas terminé », « Choisissez d'abord un fichier »). Un bouton grisé sans explication est un cul-de-sac, mais « Cochez toutes les lignes » sous une liste de cases vides ne fait que redire ce qu'on a sous les yeux : dans ce cas le bouton reste muet
- Un libellé long **revient à la ligne dans le bouton**. Jamais de `white-space: nowrap` : le libellé débordait de son conteneur et poussait la page entière en défilement horizontal sur mobile
- Le libellé est une action à l'impératif, jamais un nom : « Passer à l'étape suivante », pas « Prochaine étape »

### 7. `ConfirmDialog` — les actions destructives

Toute suppression passe par ce dialogue, jamais par le `confirm()` du navigateur. Il nomme ce qui va disparaître (« Supprimer ce brassin ? »), dit que c'est définitif, et met le focus sur « Annuler » : l'action sûre est celle qu'on atteint en premier. Les deux boutons ne portent jamais deux fois le même verbe avec deux sens opposés.

### 8. Case à cocher

Dessinée (`appearance: none`), 22 px, filet `--color-rule`. Cochée : fond `--color-signal`, coche en `--color-ink`, comme un bouton principal. Pas d'`accent-color` : la coche blanche du navigateur sur l'ambre est à 1,9:1.

## Écriture

- Impératif, à la deuxième personne, phrase courte. « Démarrez le chrono », pas « Démarrage du chronomètre ».
- Le libellé d'une action ne change pas entre le bouton et le résultat.
- Un état vide est une invitation : « Aucun brassin en cours. Choisissez une recette pour commencer. » plus un lien.
- Une erreur dit ce qui s'est passé et quoi faire : « La densité doit être un nombre. » pas « Oups ! Un problème est survenu. »
- Un formulaire qui échoue dit toujours quelque chose. `extractErrors` ne relance plus : une erreur sans détail de validation devient un message générique affiché, pas un écran qui ne bouge pas.
- Les unités sont en `--color-text-dim`, jamais en majuscules : `20 l`, `67 °C`, `1,052`. Une valeur sans unité n'existe pas (« Efficacité 72 % », pas « Efficacité 72 »).
- Les nombres suivent la locale : virgule décimale en français. Tout passe par `utils/format.ts` (`formatNumber`, `formatDensity`, `formatPercent`, `formatQuantity`), jamais par l'interpolation directe d'un nombre.
- Zéro prend le singulier en français (« 0 recette »). La règle de pluriel vit dans `i18n/i18n.config.ts`.

## Plancher de qualité

- **Zéro défilement horizontal**, à 390 px comme à 1440 px. `document.scrollWidth === clientWidth` sur chaque écran.
- `color-scheme: dark` déclaré : sans lui le navigateur rend ses propres contrôles en clair (ascenseurs, sélecteur de date, remplissage automatique) au milieu d'une page sombre.
- `<html lang>` renseigné, et **chaque page pose son propre titre** (`useHead`), sinon l'onglet et l'historique sont une suite de « AleChemist » indistinguables.
- Une page d'erreur du produit (`app/error.vue`), en français, sur le fond sombre. Sans elle, Nuxt sert sa page blanche anglaise.
- Contraste AA sur tous les textes (l'ambre `#F3AE1A` sur `#0E0E14` est à 9,8:1 ; le texte dim `#9A9AA8` à 6,4:1).
- Focus clavier visible partout : anneau `--color-signal` de 2 px, décalé de 2 px.
- `prefers-reduced-motion` respecté : pulsation et transitions supprimées, la tête de lecture saute au lieu de glisser.
- Cibles tactiles 44 px minimum.
- Rien ne dépend de la seule couleur : le retard se dit aussi par le mot « en retard », pas seulement par l'ambre brûlé.
