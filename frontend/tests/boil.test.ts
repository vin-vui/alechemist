import { describe, expect, it } from 'vitest'
import {
  boilEndMs,
  boilProgressPercent,
  boilRemainingMs,
  boilStepPositionPercent,
  canStartAromaTimer,
  isStepDueSoon,
  isStepLate,
  stepRemainingMinutes,
  stepRemainingMs,
} from '../app/utils/boil'
import type { BrewingStep } from '../app/types/api'

const START = '2026-08-12T10:00:00.000000Z'
const startMs = Date.parse('2026-08-12T10:00:00Z')
const MIN = 60_000

function step(over: Partial<BrewingStep>): BrewingStep {
  return { id: 1, quantity: 0, unit: 'g', field: '', time: null, type: 'Boil', date: null, status: false, started_at: null, ...over }
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

describe('stepRemainingMs', () => {
  it('donne le reste en ms non arrondi', () => {
    expect(stepRemainingMs(START, 60, step({ time: 15 }), startMs + 30 * MIN + 30_000)).toBe(15 * MIN - 30_000)
  })
  it('retourne null tant que le chrono n a pas demarre', () => {
    expect(stepRemainingMs(null, 60, step({ time: 15 }), startMs)).toBeNull()
  })
})

describe('isStepDueSoon', () => {
  it('du si l echeance est dans la fenetre de 60 s et non coche', () => {
    // Echeance a 45 min (time=15 sur un chrono de 60 min), on est a 44 min 30 : reste 30 s.
    expect(isStepDueSoon(START, 60, step({ time: 15 }), startMs + 44 * MIN + 30_000)).toBe(true)
  })
  it('pas encore du si l echeance est a plus de 60 s', () => {
    expect(isStepDueSoon(START, 60, step({ time: 15 }), startMs + 43 * MIN)).toBe(false)
  })
  it('plus du une fois l echeance depassee (late, pas due)', () => {
    expect(isStepDueSoon(START, 60, step({ time: 15 }), startMs + 46 * MIN)).toBe(false)
  })
  it('jamais du si deja coche', () => {
    expect(isStepDueSoon(START, 60, step({ time: 15, status: true }), startMs + 44 * MIN + 30_000)).toBe(false)
  })
  it('jamais du tant que le chrono n a pas demarre', () => {
    expect(isStepDueSoon(null, 60, step({ time: 15 }), startMs)).toBe(false)
  })
})

describe('boilStepPositionPercent', () => {
  it('positionne un houblon a (boil_time - time) / boil_time * 100', () => {
    expect(boilStepPositionPercent(60, step({ time: 15 }))).toBe(75)
    expect(boilStepPositionPercent(60, step({ time: 60 }))).toBe(0)
  })
  it('les Aroma sont toujours au flame-out (100)', () => {
    expect(boilStepPositionPercent(60, step({ type: 'Aroma', time: 5 }))).toBe(100)
  })
  it('reste dans [0, 100] meme avec un time hors bornes', () => {
    expect(boilStepPositionPercent(60, step({ time: 90 }))).toBe(0)
    expect(boilStepPositionPercent(60, step({ time: -10 }))).toBe(100)
  })
})

describe('boilProgressPercent', () => {
  it('0 tant que le chrono n a pas demarre', () => {
    expect(boilProgressPercent(null, 60, startMs)).toBe(0)
  })
  it('avance proportionnellement au temps ecoule', () => {
    expect(boilProgressPercent(START, 60, startMs + 15 * MIN)).toBe(25)
    expect(boilProgressPercent(START, 60, startMs + 30 * MIN)).toBe(50)
  })
  it('plafonne a 100 une fois le chrono ecoule', () => {
    expect(boilProgressPercent(START, 60, startMs + 90 * MIN)).toBe(100)
  })
})

// Un aromatique entre dans la cuve a l'arret du feu, puis infuse hors flamme :
// son chrono n'a de sens qu'une fois l'ebullition terminee.
describe('canStartAromaTimer', () => {
  const aroma = (over: Partial<BrewingStep> = {}) => step({ type: 'Aroma', time: 10, ...over })

  it('autorise le demarrage une fois l arret du feu passe', () => {
    expect(canStartAromaTimer(START, 60, aroma(), startMs + 61 * MIN)).toBe(true)
  })

  it('refuse tant que l ebullition tourne', () => {
    expect(canStartAromaTimer(START, 60, aroma(), startMs + 30 * MIN)).toBe(false)
  })

  it('refuse tant que l ebullition n a pas demarre', () => {
    expect(canStartAromaTimer(null, 60, aroma(), startMs)).toBe(false)
  })

  it('refuse un palier deja demarre ou deja coche', () => {
    expect(canStartAromaTimer(START, 60, aroma({ started_at: START }), startMs + 61 * MIN)).toBe(false)
    expect(canStartAromaTimer(START, 60, aroma({ status: true }), startMs + 61 * MIN)).toBe(false)
  })

  // Un houblon d'amertume s'ajoute pendant l'ebullition, il n'infuse pas apres :
  // le seul chrono qui le concerne est celui du feu.
  it('ne concerne pas les autres types de palier', () => {
    expect(canStartAromaTimer(START, 60, step({ type: 'Boil', time: 10 }), startMs + 61 * MIN)).toBe(false)
  })

  // Sans duree, il n'y a rien a decompter.
  it('refuse un aromatique sans duree', () => {
    expect(canStartAromaTimer(START, 60, aroma({ time: null }), startMs + 61 * MIN)).toBe(false)
  })
})
