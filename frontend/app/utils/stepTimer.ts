import type { BrewingStep } from '~/types/api'

const MIN_MS = 60_000

/**
 * Chrono porte par un palier : les paliers d'empatage, et les aromatiques, qui
 * infusent hors flamme une fois le feu coupe. Contrairement a l'ebullition, dont
 * le depart est porte par le brassin (`boil_start`), chacun a sa duree et son
 * propre depart (`started_at`).
 */
export function timerRemainingMs(step: BrewingStep, nowMs: number): number | null {
  if (!step.started_at || step.time === null) return null
  return Date.parse(step.started_at) + step.time * MIN_MS - nowMs
}

/** Le palier qui tient le chrono : demarre et pas encore coche. Un seul a la fois. */
export function runningTimerStep(steps: BrewingStep[]): BrewingStep | null {
  return steps.find((step) => step.started_at !== null && !step.status) ?? null
}

export type TimerState = 'pending' | 'running' | 'late' | 'done'

export function timerState(step: BrewingStep, nowMs: number): TimerState {
  if (step.status) return 'done'
  const remaining = timerRemainingMs(step, nowMs)
  if (remaining === null) return 'pending'
  return remaining <= 0 ? 'late' : 'running'
}
