import { describe, expect, it } from 'vitest'
import { brewingPath } from '../app/utils/paths'

describe('brewingPath', () => {
  it('pointe vers la page de l etape courante', () => {
    expect(brewingPath({ recipe_id: 3, id: 7, current_step: 'boil' }))
      .toBe('/recipes/3/brewings/7/boil')
  })
  it('replie sur preparation quand current_step est null', () => {
    expect(brewingPath({ recipe_id: 3, id: 7, current_step: null }))
      .toBe('/recipes/3/brewings/7/preparation')
  })
  it('pointe vers la page completed pour un brassin termine', () => {
    expect(brewingPath({ recipe_id: 3, id: 7, current_step: 'completed' }))
      .toBe('/recipes/3/brewings/7/completed')
  })
})
