import type { BrewingStep, CurrentStep, StepType } from '~/types/api'
import { minutesToDays } from './ferment'

export interface StepConfig {
  types: StepType[]
  next: CurrentStep
  title: string
}

export type StepSlug = Exclude<CurrentStep, 'completed'>

export const STEP_CONFIG: Record<StepSlug, StepConfig> = {
  preparation: { types: ['Preparation'], next: 'mash', title: 'Preparation' },
  mash: { types: ['Mash'], next: 'boil', title: 'Mash' },
  boil: { types: ['Boil', 'Aroma'], next: 'yeast', title: 'Boil' },
  yeast: { types: ['Yeast'], next: 'ferment', title: 'Yeast' },
  ferment: { types: ['Primary', 'Secondary', 'Tertiary', 'Bottle'], next: 'completed', title: 'Ferment' },
}

export const STEP_ORDER: CurrentStep[] = ['preparation', 'mash', 'boil', 'yeast', 'ferment', 'completed']

export function isStepSlug(value: unknown): value is StepSlug {
  return typeof value === 'string' && value in STEP_CONFIG
}

export function stepsForPage(stepKey: StepSlug, steps: BrewingStep[]): BrewingStep[] {
  const filtered = steps.filter((s) => STEP_CONFIG[stepKey].types.includes(s.type))
  if (stepKey === 'preparation') {
    // Parite web (orderBy unit DESC) : les volumes a chauffer d'abord.
    return [...filtered.filter((s) => s.unit === 'l'), ...filtered.filter((s) => s.unit !== 'l')]
  }
  return filtered
}

// --- Descripteurs des tableaux affiches sur la fiche recette et sur le recap de
// brassin termine (F13) : point d'entree UNIQUE, au lieu des 3 definitions
// concurrentes qui existaient avant (ce fichier, [recipe]/index.vue, completed.vue).
// C'etait la cause racine de F3 : step.time est stocke en MINUTES cote API pour
// TOUS les types de step, y compris ceux qui representent des jours (Ferment,
// Dry Hop). `unit` porte l'unite d'AFFICHAGE par defaut de la colonne temps d'un
// tableau ; `unitByType` permet de surcharger cette unite pour certains types au
// sein du meme tableau (ex: Dry Hop en jours dans un tableau Yeasts sinon en
// minutes) plutot que de multiplier les tableaux.
export type StepTimeUnit = 'minutes' | 'days'

export interface RecipeTableDescriptor {
  title: string
  types: StepType[]
  unit?: StepTimeUnit
  unitByType?: Partial<Record<StepType, StepTimeUnit>>
}

export function timeUnitFor(table: RecipeTableDescriptor, type: StepType): StepTimeUnit {
  return table.unitByType?.[type] ?? table.unit ?? 'minutes'
}

/** Formate step.time (minutes en base) selon l'unite resolue pour la ligne. */
export function formatStepTime(time: number | null | undefined, unit: StepTimeUnit): string {
  if (time == null) return ''
  return unit === 'days' ? String(minutesToDays(time)) : String(time)
}

// Fiche recette (steps de la recette, pas d'un brassin) : Ferment en jours,
// et Dry Hop (mixe dans Yeasts) egalement en jours (parite avec l'ancien
// resources/views/recipes/recipe.blade.php qui faisait `$step->time / 1440`
// pour ces deux cas).
export const RECIPE_TABLES: RecipeTableDescriptor[] = [
  { title: 'Preparation', types: ['Preparation'] },
  { title: 'Mash', types: ['Mash'] },
  { title: 'Boil', types: ['Boil', 'Aroma'] },
  { title: 'Yeasts', types: ['Yeast', 'Dry Hop'], unitByType: { 'Dry Hop': 'days' } },
  { title: 'Ferment', types: ['Primary', 'Secondary', 'Tertiary', 'Bottle'], unit: 'days' },
]

// Recap de brassin termine : seul Ferment est en jours, parite avec l'ancien
// resources/views/brewings/completed.blade.php qui n'y convertissait PAS Dry Hop
// (contrairement a la fiche recette) - comportement inchange, uniquement factorise.
export const COMPLETED_TABLES: RecipeTableDescriptor[] = [
  { title: 'Boil', types: ['Boil', 'Aroma'] },
  { title: 'Yeasts', types: ['Yeast', 'Dry Hop'] },
  { title: 'Ferment', types: ['Primary', 'Secondary', 'Tertiary', 'Bottle'], unit: 'days' },
]
