import type { BrewingStep } from '~/types/api'

export const FERMENT_TYPES = ['Primary', 'Secondary', 'Tertiary', 'Bottle'] as const

export interface FermentStepView {
  step: BrewingStep
  endsAt: Date
  daysLeft: number
  progress: number
  late: boolean
}

const DAY_MS = 86_400_000

export function fermentSchedule(fermentStart: string, steps: BrewingStep[], now: Date): FermentStepView[] {
  const relevant = steps.filter((s) => (FERMENT_TYPES as readonly string[]).includes(s.type))
  const total = relevant.reduce((sum, s) => sum + (s.time ?? 0), 0)
  let cumulativeDays = 0

  return relevant.map((s) => {
    cumulativeDays += s.time ?? 0
    const endsAt = new Date(new Date(fermentStart).getTime() + cumulativeDays * DAY_MS)
    const msLeft = endsAt.getTime() - now.getTime()
    const daysLeftRaw = msLeft / DAY_MS
    const progress = total > 0 ? Math.min(100, Math.max(0, (1 - daysLeftRaw / total) * 100)) : 100

    return {
      step: s,
      endsAt,
      daysLeft: Math.max(0, Math.ceil(daysLeftRaw)),
      progress,
      late: msLeft < 0 && !s.status,
    }
  })
}
