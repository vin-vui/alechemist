import { describe, expect, it } from 'vitest'
import { brewingsWithNotes } from '../app/utils/notes'
import type { Brewing } from '../app/types/api'

function brewing(over: Partial<Brewing>): Brewing {
  return {
    id: 1,
    name: 'Brassin',
    volume: 20,
    alcohol: null,
    boil_time: 60,
    boil_start: null,
    ferment_start: null,
    ferment_end: null,
    initial_density: 1.05,
    final_density: 1.01,
    before_boil_density: null,
    carbonation: 5,
    date_start: '2026-08-17',
    current_step: 'completed',
    note: 'une note',
    recipe_id: 1,
    created_at: '2026-08-17T10:00:00.000000Z',
    updated_at: '2026-08-17T10:00:00.000000Z',
    ...over,
  }
}

const ids = (list: Brewing[]) => list.map((b) => b.id)

describe('brewingsWithNotes', () => {
  it('ne garde que les brassins qui portent une note', () => {
    const list = brewingsWithNotes([
      brewing({ id: 1, note: 'empatage a 67' }),
      brewing({ id: 2, note: null }),
      brewing({ id: 3, note: '' }),
    ])
    expect(ids(list)).toEqual([1])
  })

  it('ignore une note faite d espaces et de retours a la ligne', () => {
    expect(brewingsWithNotes([brewing({ note: '  \n  ' })])).toEqual([])
  })

  it('trie du plus recent au plus ancien', () => {
    const list = brewingsWithNotes([
      brewing({ id: 1, date_start: '2026-05-04' }),
      brewing({ id: 2, date_start: '2026-08-17' }),
      brewing({ id: 3, date_start: '2026-06-30' }),
    ])
    expect(ids(list)).toEqual([2, 3, 1])
  })

  it('departage deux brassins du meme jour par id decroissant', () => {
    const list = brewingsWithNotes([
      brewing({ id: 1, date_start: '2026-08-17' }),
      brewing({ id: 4, date_start: '2026-08-17' }),
      brewing({ id: 3, date_start: '2026-08-17' }),
    ])
    expect(ids(list)).toEqual([4, 3, 1])
  })

  it('ne modifie pas le tableau recu', () => {
    const source = [brewing({ id: 1, date_start: '2026-05-04' }), brewing({ id: 2, date_start: '2026-08-17' })]
    brewingsWithNotes(source)
    expect(ids(source)).toEqual([1, 2])
  })

  it('accepte une liste vide ou absente', () => {
    expect(brewingsWithNotes([])).toEqual([])
    expect(brewingsWithNotes(null)).toEqual([])
  })
})
