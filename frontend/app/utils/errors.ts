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
