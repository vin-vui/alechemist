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

export function stepRemainingMinutes(
  boilStart: string | null,
  boilTimeMin: number,
  step: BrewingStep,
  nowMs: number,
): number | null {
  const end = boilEndMs(boilStart, boilTimeMin)
  if (end === null) return null
  const target = step.type === 'Aroma' ? end : end - (step.time ?? 0) * MIN_MS
  return Math.ceil((target - nowMs) / MIN_MS)
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
