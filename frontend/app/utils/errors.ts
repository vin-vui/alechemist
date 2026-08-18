interface MaybeFetchError {
  statusCode?: number
  status?: number
  response?: { status?: number; _data?: { errors?: Record<string, string[]>; message?: string } }
  data?: { statusCode?: number }
}

/**
 * Erreurs de validation par champ (422), ou un objet vide.
 *
 * Cette fonction relançait l'erreur pour tout ce qui n'était pas un 422. Comme
 * chaque formulaire l'appelle depuis son `catch`, l'erreur repartait aussitôt en
 * rejet non traité : le formulaire ne bougeait pas et rien ne s'affichait. Les
 * appelants distinguent maintenant « erreurs de champ » (map non vide) de « échec
 * sans détail » (map vide), et disent quelque chose dans les deux cas.
 */
export function extractErrors(e: unknown): Record<string, string[]> {
  const err = e as MaybeFetchError
  if (err?.response?.status === 422) {
    return err.response._data?.errors ?? {}
  }
  return {}
}

/**
 * Clé du message à afficher quand un échec n'a pas de détail de validation.
 *
 * Tous les formulaires affichaient « L'action a échoué. Vérifiez votre connexion,
 * puis réessayez. » quel que soit l'échec. Sur un 419, la connexion n'y est pour
 * rien et recharger la page est la seule chose à faire ; sur un 500, réessayer
 * tout de suite ne sert à rien. Un message qui désigne la mauvaise cause envoie
 * l'utilisateur chercher la panne là où elle n'est pas.
 */
export function failureMessageKey(e: unknown): string {
  const err = e as MaybeFetchError
  const status = err?.response?.status ?? err?.statusCode ?? err?.status ?? err?.data?.statusCode

  // Aucune réponse du tout : le seul cas où parler de connexion a un sens.
  if (typeof status !== 'number') return 'Server unreachable'
  if (status === 419) return 'Session expired'
  if (status === 429) return 'Too many attempts'
  if (status >= 500) return 'Server error'
  return 'Action failed'
}

/** Code HTTP d'une erreur, quelle que soit la couche qui l'a emballée. */
export function statusOf(e: unknown): number {
  const err = e as MaybeFetchError
  return Number(err?.statusCode ?? err?.status ?? err?.response?.status ?? err?.data?.statusCode) || 500
}

// Fortify renvoie 423 Locked quand une action sensible (2FA...) exige une
// confirmation de mot de passe récente (config/fortify.php: confirmPassword => true).
export function needsPasswordConfirmation(e: unknown): boolean {
  return (e as MaybeFetchError).response?.status === 423
}
