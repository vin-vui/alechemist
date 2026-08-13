import { describe, expect, it } from 'vitest'
import { needsPasswordConfirmation } from '../app/utils/errors'

function fetchError(status: number) {
  return Object.assign(new Error(`HTTP ${status}`), { response: { status } })
}

describe('needsPasswordConfirmation', () => {
  it('reconnait le 423 Locked de Fortify', () => {
    expect(needsPasswordConfirmation(fetchError(423))).toBe(true)
  })
  it('ignore les autres erreurs', () => {
    expect(needsPasswordConfirmation(fetchError(422))).toBe(false)
    expect(needsPasswordConfirmation(new Error('boom'))).toBe(false)
  })
})
