interface MaybeFetchError {
  response?: { status?: number; _data?: { errors?: Record<string, string[]>; message?: string } }
}

export function extractErrors(e: unknown): Record<string, string[]> {
  const err = e as MaybeFetchError
  if (err.response?.status === 422) {
    return err.response._data?.errors ?? {}
  }
  throw e
}

// Fortify renvoie 423 Locked quand une action sensible (2FA...) exige une
// confirmation de mot de passe récente (config/fortify.php: confirmPassword => true).
export function needsPasswordConfirmation(e: unknown): boolean {
  return (e as MaybeFetchError).response?.status === 423
}
