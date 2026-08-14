import { describe, expect, it } from 'vitest'
import { extractErrors, statusOf } from '../app/utils/errors'

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
  // Cette fonction relancait les autres statuts. Comme elle est appelee depuis le
  // `catch` de chaque formulaire, l'erreur repartait en rejet non traite et le
  // formulaire restait muet. Elle rend maintenant une map vide, que l'appelant
  // traduit en message d'echec generique.
  it('rend une map vide pour les autres statuts, sans relancer', () => {
    expect(extractErrors(fetchError(500))).toEqual({})
    expect(extractErrors(fetchError(419))).toEqual({})
    expect(extractErrors(new TypeError('undefined is not an object'))).toEqual({})
  })
})

describe('statusOf', () => {
  it('lit le statut quelle que soit la couche qui a emballe l erreur', () => {
    expect(statusOf(fetchError(404))).toBe(404)
    expect(statusOf({ statusCode: 403 })).toBe(403)
    expect(statusOf({ status: 401 })).toBe(401)
  })
  it('retombe sur 500 quand le statut est introuvable', () => {
    expect(statusOf(new Error('boom'))).toBe(500)
    expect(statusOf(null)).toBe(500)
  })
})
