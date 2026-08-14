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
