import { describe, expect, it } from 'vitest'
import { fermentSchedule } from '../app/utils/ferment'
import type { BrewingStep } from '../app/types/api'

function step(over: Partial<BrewingStep>): BrewingStep {
  return { id: 1, quantity: 0, unit: 'g', field: '', time: null, type: 'Primary', date: null, status: false, ...over }
}

describe('fermentSchedule', () => {
  const start = '2026-08-01T10:00:00.000000Z'

  // step.time est stocke en MINUTES cote API (cf. BeerXmlImporter::PRIMARY_AGE * 1440),
  // au meme titre que les autres steps (mash, boil...) : 7 jours -> 10080, 14 jours -> 20160.
  it('cumule les durees (en minutes) pour calculer les dates de fin', () => {
    const steps = [
      step({ id: 1, type: 'Primary', time: 10080 }),
      step({ id: 2, type: 'Secondary', time: 20160 }),
    ]
    const views = fermentSchedule(start, steps, new Date('2026-08-05T10:00:00Z'))
    expect(views[0].endsAt.toISOString()).toBe('2026-08-08T10:00:00.000Z')
    expect(views[1].endsAt.toISOString()).toBe('2026-08-22T10:00:00.000Z')
    expect(views[0].daysLeft).toBe(3)
    expect(views[1].daysLeft).toBe(17)
  })

  it('expose durationDays converti depuis les minutes stockees', () => {
    const steps = [
      step({ id: 1, type: 'Primary', time: 10080 }),
      step({ id: 2, type: 'Secondary', time: 20160 }),
    ]
    const views = fermentSchedule(start, steps, new Date('2026-08-05T10:00:00Z'))
    expect(views[0].durationDays).toBe(7)
    expect(views[1].durationDays).toBe(14)
  })

  it('ignore les steps hors fermentation', () => {
    const steps = [step({ type: 'Boil', time: 60 }), step({ type: 'Bottle', time: 2880 })]
    expect(fermentSchedule(start, steps, new Date())).toHaveLength(1)
  })

  it('marque late une etape depassee et non cochee, et borne la progression a 100', () => {
    const steps = [step({ type: 'Primary', time: 2880, status: false })]
    const views = fermentSchedule(start, steps, new Date('2026-08-10T10:00:00Z'))
    expect(views[0].late).toBe(true)
    expect(views[0].daysLeft).toBe(0)
    expect(views[0].progress).toBe(100)
  })

  it('ne marque pas late une etape cochee', () => {
    const steps = [step({ type: 'Primary', time: 2880, status: true })]
    const views = fermentSchedule(start, steps, new Date('2026-08-10T10:00:00Z'))
    expect(views[0].late).toBe(false)
  })
})
