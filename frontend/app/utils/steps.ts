import type { BrewingStep, CurrentStep, StepType } from '~/types/api'

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
