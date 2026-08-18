import { describe, expect, it } from 'vitest'
import { timerRemainingMs, timerState, runningTimerStep } from '../app/utils/stepTimer'
import type { BrewingStep } from '../app/types/api'

const START = '2026-08-17T10:00:00.000000Z'
const startMs = Date.parse('2026-08-17T10:00:00Z')
const MIN = 60_000

function step(over: Partial<BrewingStep>): BrewingStep {
  return {
    id: 1,
    quantity: 67,
    unit: '°C',
    field: 'Palier',
    time: 60,
    type: 'Mash',
    date: null,
    status: false,
    started_at: null,
    ...over,
  }
}

describe('timerRemainingMs', () => {
  it('decompte la duree du palier depuis son demarrage', () => {
    expect(timerRemainingMs(step({ started_at: START, time: 60 }), startMs + 45 * MIN)).toBe(15 * MIN)
  })

  it('passe en negatif une fois le palier ecoule', () => {
    expect(timerRemainingMs(step({ started_at: START, time: 60 }), startMs + 61 * MIN)).toBe(-1 * MIN)
  })

  it('retourne null tant que le palier n a pas demarre', () => {
    expect(timerRemainingMs(step({ started_at: null }), startMs)).toBeNull()
  })

  // Un palier sans duree (BeerXML n'en donne pas toujours) ne peut rien decompter :
  // afficher 00:00 des le demarrage ferait croire a un palier deja fini.
  it('retourne null pour un palier sans duree', () => {
    expect(timerRemainingMs(step({ started_at: START, time: null }), startMs)).toBeNull()
  })
})

describe('runningTimerStep', () => {
  it('trouve le palier en cours', () => {
    const running = step({ id: 2, started_at: START })
    expect(runningTimerStep([step({ id: 1 }), running, step({ id: 3 })])).toBe(running)
  })

  it('retourne null quand aucun palier ne tourne', () => {
    expect(runningTimerStep([step({ id: 1 }), step({ id: 2 })])).toBeNull()
  })

  // Un palier coche est fini : son horodatage reste en base (l'historique n'est pas
  // efface) mais il ne doit plus occuper le chrono, sinon le brasseur voit un
  // decompte negatif sur une ligne qu'il vient de terminer.
  it('ignore un palier deja coche', () => {
    expect(runningTimerStep([step({ started_at: START, status: true })])).toBeNull()
  })
})

describe('timerState', () => {
  it('un palier coche est fait', () => {
    expect(timerState(step({ status: true }), startMs)).toBe('done')
  })

  it('un palier non demarre est en attente', () => {
    expect(timerState(step({}), startMs)).toBe('pending')
  })

  it('un palier en cours de decompte tourne', () => {
    expect(timerState(step({ started_at: START, time: 60 }), startMs + 30 * MIN)).toBe('running')
  })

  it('un palier dont le temps est ecoule est en retard', () => {
    expect(timerState(step({ started_at: START, time: 60 }), startMs + 61 * MIN)).toBe('late')
  })
})
