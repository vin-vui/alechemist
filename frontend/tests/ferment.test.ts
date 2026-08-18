import { describe, expect, it } from 'vitest'
import { fermentSchedule } from '../app/utils/ferment'
import type { BrewingStep } from '../app/types/api'

function step(over: Partial<BrewingStep>): BrewingStep {
  return { id: 1, quantity: 0, unit: 'g', field: '', time: null, type: 'Primary', date: null, status: false, started_at: null, ...over }
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

  // F4 : la progression doit se baser sur le cumul jusqu'a la fin de l'etape
  // COURANTE, pas sur le total de toutes les etapes de fermentation. Avec
  // l'ancienne formule (denominateur = total), Primary 7j + Bottle 7j affichait
  // 50% sur la barre de Primary des le premier jour (rien n'est encore entame).
  it('calcule la progression sur le cumul jusqu a l etape courante, pas sur le total de toutes les etapes', () => {
    const steps = [
      step({ id: 1, type: 'Primary', time: 10080 }), // 7 jours
      step({ id: 2, type: 'Bottle', time: 10080 }), // 7 jours
    ]

    // t = debut de fermentation : rien n'a encore ete entame, les deux barres
    // doivent etre a 0%.
    const atStart = fermentSchedule(start, steps, new Date(start))
    expect(atStart[0].progress).toBe(0)
    expect(atStart[1].progress).toBe(0)

    // A mi-parcours de la 1ere etape (3.5j sur 7j) : 50% pour l'etape en cours ;
    // pour la 2e etape, le cumul (10.5j restants sur 14j cumules) donne 25%.
    const halfway = fermentSchedule(start, steps, new Date('2026-08-04T22:00:00.000Z'))
    expect(halfway[0].progress).toBe(50)
    expect(halfway[1].progress).toBe(25)
  })
})
