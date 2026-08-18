import { describe, expect, it } from 'vitest'
import { extractErrors, failureMessageKey, statusOf } from '../app/utils/errors'

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

// Tous les formulaires affichaient « L'action a echoue. Verifiez votre connexion »
// quel que soit l'echec : sur un 419, la connexion n'y est pour rien et recharger
// la page est la seule chose a faire. Le message doit dire ce qui s'est passe et
// quoi faire (docs/design-system.md, Ecriture).
describe('failureMessageKey', () => {
  it('distingue une session expiree', () => {
    expect(failureMessageKey(fetchError(419))).toBe('Session expired')
  })

  it('distingue un serveur en erreur', () => {
    expect(failureMessageKey(fetchError(500))).toBe('Server error')
    expect(failureMessageKey(fetchError(503))).toBe('Server error')
  })

  it('distingue un trop-plein de tentatives', () => {
    expect(failureMessageKey(fetchError(429))).toBe('Too many attempts')
  })

  // Une panne reseau n'a pas de reponse du tout : c'est le seul cas ou parler de
  // connexion a un sens.
  it('distingue un serveur injoignable', () => {
    expect(failureMessageKey(new TypeError('Failed to fetch'))).toBe('Server unreachable')
    expect(failureMessageKey(null)).toBe('Server unreachable')
  })

  it('retombe sur un echec generique pour les autres statuts', () => {
    expect(failureMessageKey(fetchError(400))).toBe('Action failed')
    expect(failureMessageKey(fetchError(403))).toBe('Action failed')
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
