import { describe, expect, it } from 'vitest'
import { formatDate, formatQuantity } from '../app/utils/format'

describe('formatQuantity', () => {
  it('convertit les grammes en kilogrammes a partir de 1000', () => {
    expect(formatQuantity(2500, 'g')).toBe('2.5 kg')
    expect(formatQuantity(999, 'g')).toBe('999 g')
  })
  it('laisse les autres unites telles quelles', () => {
    expect(formatQuantity(20, 'l')).toBe('20 l')
    expect(formatQuantity(65, '°C')).toBe('65 °C')
  })
})

describe('formatDate', () => {
  it('rend vide pour null', () => {
    expect(formatDate(null)).toBe('')
  })
  it('formate une date ISO en francais', () => {
    expect(formatDate('2026-08-12')).toContain('2026')
  })
})
