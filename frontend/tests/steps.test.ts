import { describe, expect, it } from 'vitest'
import { STEP_CONFIG, STEP_ORDER, isStepSlug, stepsForPage } from '../app/utils/steps'
import type { BrewingStep } from '../app/types/api'

function step(over: Partial<BrewingStep>): BrewingStep {
  return { id: Math.random(), quantity: 0, unit: 'g', field: '', time: null, type: 'Mash', date: null, status: false, ...over }
}

describe('STEP_CONFIG', () => {
  it('encode l ordre des transitions', () => {
    expect(STEP_CONFIG.preparation.next).toBe('mash')
    expect(STEP_CONFIG.mash.next).toBe('boil')
    expect(STEP_CONFIG.boil.next).toBe('yeast')
    expect(STEP_CONFIG.yeast.next).toBe('ferment')
    expect(STEP_CONFIG.ferment.next).toBe('completed')
  })
  it('repartit les types par ecran comme le web', () => {
    expect(STEP_CONFIG.boil.types).toEqual(['Boil', 'Aroma'])
    expect(STEP_CONFIG.ferment.types).toEqual(['Primary', 'Secondary', 'Tertiary', 'Bottle'])
  })
})

describe('isStepSlug', () => {
  it('accepte les 5 etapes et rejette le reste', () => {
    expect(isStepSlug('preparation')).toBe(true)
    expect(isStepSlug('ferment')).toBe(true)
    expect(isStepSlug('completed')).toBe(false)
    expect(isStepSlug('note')).toBe(false)
    expect(isStepSlug(42)).toBe(false)
  })
})

describe('stepsForPage', () => {
  it('filtre par types de l etape', () => {
    const steps = [step({ type: 'Mash' }), step({ type: 'Boil' }), step({ type: 'Aroma' })]
    expect(stepsForPage('boil', steps)).toHaveLength(2)
  })
  it('fait remonter les lignes en litres pour preparation', () => {
    const steps = [
      step({ id: 1, type: 'Preparation', unit: 'g' }),
      step({ id: 2, type: 'Preparation', unit: 'l' }),
    ]
    expect(stepsForPage('preparation', steps).map((s) => s.id)).toEqual([2, 1])
  })
  it('conserve l ordre pour STEP_ORDER', () => {
    expect(STEP_ORDER).toEqual(['preparation', 'mash', 'boil', 'yeast', 'ferment', 'completed'])
  })
})
