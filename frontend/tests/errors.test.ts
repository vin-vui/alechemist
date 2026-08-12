import { describe, expect, it } from 'vitest'
import { extractErrors } from '../app/utils/errors'

function fetchError(status: number, data?: unknown) {
  return Object.assign(new Error(`HTTP ${status}`), { response: { status, _data: data } })
}

describe('extractErrors', () => {
  it('extrait les erreurs de validation 422', () => {
    const e = fetchError(422, { errors: { email: ['Adresse invalide'] } })
    expect(extractErrors(e)).toEqual({ email: ['Adresse invalide'] })
  })
  it('retourne un objet vide si le 422 est sans details', () => {
    expect(extractErrors(fetchError(422, {}))).toEqual({})
  })
  it('relance les autres statuts', () => {
    expect(() => extractErrors(fetchError(500))).toThrow()
  })
})
