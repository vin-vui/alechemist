import { describe, expect, it } from 'vitest'
import { formatDate, formatDuration, formatQuantity } from '../app/utils/format'

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

describe('formatDuration', () => {
  it('formate en MM:SS sous l heure', () => {
    expect(formatDuration(0)).toBe('00:00')
    expect(formatDuration(65_000)).toBe('01:05')
    expect(formatDuration(59 * 60_000 + 59_000)).toBe('59:59')
  })
  it('formate en H:MM:SS a partir d une heure', () => {
    expect(formatDuration(60 * 60_000)).toBe('1:00:00')
    expect(formatDuration(90 * 60_000 + 5_000)).toBe('1:30:05')
  })
  it('ne descend jamais sous zero', () => {
    expect(formatDuration(-5_000)).toBe('00:00')
  })
})
