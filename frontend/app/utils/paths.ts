import type { CurrentStep } from '~/types/api'

export function brewingPath(brewing: { recipe_id: number; id: number; current_step: CurrentStep | null }): string {
  const step = brewing.current_step ?? 'preparation'
  return `/recipes/${brewing.recipe_id}/brewings/${brewing.id}/${step}`
}
