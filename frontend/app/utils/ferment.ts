import type { BrewingStep } from '~/types/api'

export const FERMENT_TYPES = ['Primary', 'Secondary', 'Tertiary', 'Bottle'] as const

export interface FermentStepView {
  step: BrewingStep
  endsAt: Date
  /** Duree de l'etape convertie en jours pour l'affichage (step.time est stocke en minutes). */
  durationDays: number
  daysLeft: number
  progress: number
  late: boolean
}

const MINUTE_MS = 60_000
const MINUTES_PER_DAY = 1440

/** step.time (ferment comme les autres types de step) est stocke en MINUTES cote API
 * (cf. BeerXmlImporter::PRIMARY_AGE * 1440) : on convertit en jours pour l'affichage. */
export function minutesToDays(minutes: number | null | undefined): number {
  return Math.round((minutes ?? 0) / MINUTES_PER_DAY)
}

export function fermentSchedule(fermentStart: string, steps: BrewingStep[], now: Date): FermentStepView[] {
  const relevant = steps.filter((s) => (FERMENT_TYPES as readonly string[]).includes(s.type))
  const total = relevant.reduce((sum, s) => sum + (s.time ?? 0), 0)
  const fermentStartMs = new Date(fermentStart).getTime()
  let cumulativeMinutes = 0

  return relevant.map((s) => {
    cumulativeMinutes += s.time ?? 0
    const endsAt = new Date(fermentStartMs + cumulativeMinutes * MINUTE_MS)
    const msLeft = endsAt.getTime() - now.getTime()
    const minutesLeftRaw = msLeft / MINUTE_MS
    const progress = total > 0 ? Math.min(100, Math.max(0, (1 - minutesLeftRaw / total) * 100)) : 100

    return {
      step: s,
      endsAt,
      durationDays: minutesToDays(s.time),
      daysLeft: Math.max(0, Math.ceil(minutesLeftRaw / MINUTES_PER_DAY)),
      progress,
      late: msLeft < 0 && !s.status,
    }
  })
}
