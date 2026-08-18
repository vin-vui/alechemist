import type { BrewingStep } from '~/types/api'

const MIN_MS = 60_000

export function boilEndMs(boilStart: string | null, boilTimeMin: number): number | null {
  if (!boilStart) return null
  return Date.parse(boilStart) + boilTimeMin * MIN_MS
}

export function boilRemainingMs(boilStart: string | null, boilTimeMin: number, nowMs: number): number | null {
  const end = boilEndMs(boilStart, boilTimeMin)
  return end === null ? null : end - nowMs
}

/** Instant cible (epoch ms) de l'ajout : les Aroma s'ajoutent au flame-out, les autres `step.time` minutes avant. */
function stepTargetMs(end: number, step: BrewingStep): number {
  return step.type === 'Aroma' ? end : end - (step.time ?? 0) * MIN_MS
}

export function stepRemainingMinutes(
  boilStart: string | null,
  boilTimeMin: number,
  step: BrewingStep,
  nowMs: number,
): number | null {
  const end = boilEndMs(boilStart, boilTimeMin)
  if (end === null) return null
  return Math.ceil((stepTargetMs(end, step) - nowMs) / MIN_MS)
}

/** Temps restant en ms (non arrondi), pour les calculs qui ont besoin d'une precision sous la minute (fenetre "du" de BoilTrack). */
export function stepRemainingMs(
  boilStart: string | null,
  boilTimeMin: number,
  step: BrewingStep,
  nowMs: number,
): number | null {
  const end = boilEndMs(boilStart, boilTimeMin)
  if (end === null) return null
  return stepTargetMs(end, step) - nowMs
}

export function isStepLate(
  boilStart: string | null,
  boilTimeMin: number,
  step: BrewingStep,
  nowMs: number,
): boolean {
  const remaining = stepRemainingMinutes(boilStart, boilTimeMin, step, nowMs)
  return remaining !== null && remaining <= 0 && !step.status
}

const DUE_WINDOW_MS = 60_000

/** Repere "du maintenant" de BoilTrack : chrono demarre, pas coche, echeance dans la fenetre de 60 s (ni deja depassee, ni lointaine). */
export function isStepDueSoon(
  boilStart: string | null,
  boilTimeMin: number,
  step: BrewingStep,
  nowMs: number,
): boolean {
  if (step.status) return false
  const remaining = stepRemainingMs(boilStart, boilTimeMin, step, nowMs)
  return remaining !== null && remaining > 0 && remaining <= DUE_WINDOW_MS
}

/**
 * Un aromatique entre dans la cuve a l'arret du feu, puis infuse hors flamme : son
 * chrono ne peut partir qu'une fois l'ebullition terminee. Le serveur applique la
 * meme regle (409), l'ecran se contente de ne pas proposer le bouton avant.
 */
export function canStartAromaTimer(
  boilStart: string | null,
  boilTimeMin: number,
  step: BrewingStep,
  nowMs: number,
): boolean {
  if (step.type !== 'Aroma' || step.status || step.time === null || step.started_at !== null) return false
  const remaining = boilRemainingMs(boilStart, boilTimeMin, nowMs)
  return remaining !== null && remaining <= 0
}

/** Position en % d'un repere le long de la BoilTrack, de T-boil_time (0) au flame-out (100). Les Aroma sont toujours au flame-out. */
export function boilStepPositionPercent(boilTimeMin: number, step: BrewingStep): number {
  if (boilTimeMin <= 0 || step.type === 'Aroma') return 100
  const pct = ((boilTimeMin - (step.time ?? 0)) / boilTimeMin) * 100
  return Math.min(100, Math.max(0, pct))
}

/** Position en % de la tete de lecture de BoilTrack : 0 si pas demarre, 100 si le chrono est ecoule. */
export function boilProgressPercent(boilStart: string | null, boilTimeMin: number, nowMs: number): number {
  if (boilTimeMin <= 0) return 0
  const remaining = boilRemainingMs(boilStart, boilTimeMin, nowMs)
  if (remaining === null) return 0
  const totalMs = boilTimeMin * MIN_MS
  const pct = ((totalMs - remaining) / totalMs) * 100
  return Math.min(100, Math.max(0, pct))
}
