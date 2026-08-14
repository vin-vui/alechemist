import { afterEach, describe, expect, it } from 'vitest'
import {
  formatDate,
  formatDensity,
  formatDuration,
  formatNumber,
  formatPercent,
  formatQuantity,
  parseDecimalInput,
  setFormatLocale,
} from '../app/utils/format'

afterEach(() => setFormatLocale('fr'))

describe('formatNumber', () => {
  it('utilise la virgule decimale en francais', () => {
    expect(formatNumber(2.5)).toBe('2,5')
    expect(formatNumber(72)).toBe('72')
  })
  it('suit la locale active', () => {
    setFormatLocale('en')
    expect(formatNumber(2.5)).toBe('2.5')
  })
  it('rend une chaine vide pour une valeur absente', () => {
    expect(formatNumber(null)).toBe('')
    expect(formatNumber(undefined)).toBe('')
    expect(formatNumber('')).toBe('')
  })
  it('accepte les decimaux serialises en chaine par l API', () => {
    expect(formatNumber('5.60')).toBe('5,6')
    expect(formatNumber('45.3')).toBe('45,3')
  })
})

describe('formatQuantity', () => {
  it('convertit les grammes en kilogrammes a partir de 1000', () => {
    expect(formatQuantity(2500, 'g')).toBe('2,5 kg')
    expect(formatQuantity(999, 'g')).toBe('999 g')
  })
  it('laisse les autres unites telles quelles', () => {
    expect(formatQuantity(20, 'l')).toBe('20 l')
    expect(formatQuantity(65, '°C')).toBe('65 °C')
  })
})

describe('formatDensity', () => {
  it('affiche toujours trois decimales', () => {
    expect(formatDensity(1.052)).toBe('1,052')
    expect(formatDensity(1.01)).toBe('1,010')
  })
  it('rend une chaine vide pour une valeur absente', () => {
    expect(formatDensity(null)).toBe('')
  })
})

describe('formatPercent', () => {
  it('colle l unite au nombre localise', () => {
    expect(formatPercent(5.6)).toBe('5,6 %')
    expect(formatPercent(72)).toBe('72 %')
  })
})

describe('parseDecimalInput', () => {
  it('accepte la virgule, puisque l app affiche des virgules', () => {
    expect(parseDecimalInput('1,048')).toBe(1.048)
    expect(parseDecimalInput('1.048')).toBe(1.048)
  })
  it('accepte un nombre, que v-model produit sur un input number', () => {
    expect(parseDecimalInput(1.048)).toBe(1.048)
  })
  it('rend null pour un champ vide, donc une valeur a effacer', () => {
    expect(parseDecimalInput('')).toBeNull()
    expect(parseDecimalInput('   ')).toBeNull()
    expect(parseDecimalInput(null)).toBeNull()
  })
  it('rend undefined pour une saisie qui n est pas un nombre', () => {
    expect(parseDecimalInput('abc')).toBeUndefined()
    expect(parseDecimalInput('1,0,4')).toBeUndefined()
    expect(parseDecimalInput(Number.NaN)).toBeUndefined()
  })
})

describe('formatDate', () => {
  it('rend vide pour null', () => {
    expect(formatDate(null)).toBe('')
  })
  it('formate une date ISO en francais', () => {
    expect(formatDate('2026-08-12')).toContain('2026')
  })
  it('suit la locale active', () => {
    setFormatLocale('en')
    expect(formatDate('2026-08-12')).toContain('Aug')
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
