import { describe, expect, it } from 'vitest'
import { ebcToHex } from '../app/utils/ebc'

describe('ebcToHex', () => {
  it('retourne un hex valide pour toute la plage 0..90', () => {
    for (let n = 0; n <= 90; n++) {
      expect(ebcToHex(n)).toMatch(/^#[0-9A-Fa-f]{6}$/)
    }
  })
  it('borne les valeurs hors plage', () => {
    expect(ebcToHex(-5)).toBe(ebcToHex(0))
    expect(ebcToHex(200)).toBe(ebcToHex(90))
  })
  it('accepte les chaines et le null', () => {
    expect(ebcToHex('12')).toBe(ebcToHex(12))
    expect(ebcToHex(null)).toMatch(/^#[0-9A-Fa-f]{6}$/)
  })
  it('correspond aux valeurs transcrites de app.css (spot-check)', () => {
    expect(ebcToHex(0)).toBe('#ffe699')
    expect(ebcToHex(32)).toBe('#b54c00')
    expect(ebcToHex(61)).toBe('#5a0a02')
    expect(ebcToHex(90)).toBe('#36080a')
  })
})
