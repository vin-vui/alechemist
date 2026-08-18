import { describe, expect, it } from 'vitest'
import {
  COMPLETED_TABLES,
  RECIPE_TABLES,
  STEP_CONFIG,
  STEP_ORDER,
  formatStepTime,
  isStepSlug,
  stepsForPage,
  timeUnitFor,
} from '../app/utils/steps'
import type { BrewingStep } from '../app/types/api'

function step(over: Partial<BrewingStep>): BrewingStep {
  return { id: Math.random(), quantity: 0, unit: 'g', field: '', time: null, type: 'Mash', date: null, status: false, started_at: null, ...over }
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

// F3/F13 : point d'entree unique pour les descripteurs de tableaux (fiche recette
// et recap de brassin termine), avec unite par ligne pour gerer le cas Dry Hop
// (jours) mixe dans un tableau Yeasts sinon en minutes.
describe('timeUnitFor / formatStepTime', () => {
  it('resout minutes par defaut quand la table n a pas d unite', () => {
    const table = RECIPE_TABLES.find((t) => t.title === 'Boil')!
    expect(timeUnitFor(table, 'Boil')).toBe('minutes')
    expect(formatStepTime(60, timeUnitFor(table, 'Boil'))).toBe('60')
  })

  it('resout jours pour le tableau Ferment de la fiche recette', () => {
    const table = RECIPE_TABLES.find((t) => t.title === 'Ferment')!
    expect(timeUnitFor(table, 'Primary')).toBe('days')
    expect(formatStepTime(10080, timeUnitFor(table, 'Primary'))).toBe('7')
  })

  it('surcharge Dry Hop en jours dans le tableau Yeasts (minutes par ailleurs)', () => {
    const table = RECIPE_TABLES.find((t) => t.title === 'Yeasts')!
    expect(timeUnitFor(table, 'Yeast')).toBe('minutes')
    expect(timeUnitFor(table, 'Dry Hop')).toBe('days')
    expect(formatStepTime(20160, timeUnitFor(table, 'Dry Hop'))).toBe('14')
  })

  it('formate une valeur nulle en chaine vide', () => {
    expect(formatStepTime(null, 'minutes')).toBe('')
    expect(formatStepTime(undefined, 'days')).toBe('')
  })

  it('COMPLETED_TABLES ne convertit que Ferment (parite avec l ancien completed.blade.php)', () => {
    const yeasts = COMPLETED_TABLES.find((t) => t.title === 'Yeasts')!
    const ferment = COMPLETED_TABLES.find((t) => t.title === 'Ferment')!
    expect(timeUnitFor(yeasts, 'Dry Hop')).toBe('minutes')
    expect(timeUnitFor(ferment, 'Bottle')).toBe('days')
  })
})
