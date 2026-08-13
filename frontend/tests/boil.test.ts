import { describe, expect, it } from 'vitest'
import { boilEndMs, boilRemainingMs, isStepLate, stepRemainingMinutes } from '../app/utils/boil'
import type { BrewingStep } from '../app/types/api'

const START = '2026-08-12T10:00:00.000000Z'
const startMs = Date.parse('2026-08-12T10:00:00Z')
const MIN = 60_000

function step(over: Partial<BrewingStep>): BrewingStep {
  return { id: 1, quantity: 0, unit: 'g', field: '', time: null, type: 'Boil', date: null, status: false, ...over }
}

describe('boilEndMs / boilRemainingMs', () => {
  it('calcule la fin depuis boil_start + boil_time minutes', () => {
    expect(boilEndMs(START, 60)).toBe(startMs + 60 * MIN)
    expect(boilEndMs(null, 60)).toBeNull()
  })
  it('calcule le temps restant', () => {
    expect(boilRemainingMs(START, 60, startMs + 45 * MIN)).toBe(15 * MIN)
    expect(boilRemainingMs(START, 60, startMs + 61 * MIN)).toBe(-1 * MIN)
    expect(boilRemainingMs(null, 60, startMs)).toBeNull()
  })
})

describe('stepRemainingMinutes', () => {
  it('un houblon a time=15 entre 15 minutes avant la fin', () => {
    // Chrono de 60 min demarre, on est a 30 min : l'ajout est dans 15 min.
    expect(stepRemainingMinutes(START, 60, step({ time: 15 }), startMs + 30 * MIN)).toBe(15)
  })
  it('un Aroma entre au flame-out (fin du chrono)', () => {
    expect(stepRemainingMinutes(START, 60, step({ type: 'Aroma', time: 5 }), startMs + 30 * MIN)).toBe(30)
  })
  it('retourne null tant que le chrono n a pas demarre', () => {
    expect(stepRemainingMinutes(null, 60, step({ time: 15 }), startMs)).toBeNull()
  })
})

describe('isStepLate', () => {
  it('late si demarre, echeance depassee et non coche', () => {
    const late = step({ time: 45, status: false })
    expect(isStepLate(START, 60, late, startMs + 20 * MIN)).toBe(true)
  })
  it('jamais late avant le demarrage (bug web non replique)', () => {
    expect(isStepLate(null, 60, step({ time: 45 }), startMs)).toBe(false)
  })
  it('jamais late si coche', () => {
    expect(isStepLate(START, 60, step({ time: 45, status: true }), startMs + 20 * MIN)).toBe(false)
  })
})
