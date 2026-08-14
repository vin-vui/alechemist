import type { ApiItem } from '~/types/api'

/*
 * Le statut HTTP est traduit ici, au plus pres de la reponse. Plus haut, dans
 * `useAsyncData`, l'erreur est reemballee et le code d'origine n'est plus
 * lisible : une recette introuvable (404) affichait « Serveur injoignable, 500 ».
 */
export async function apiGet<T>(url: string): Promise<T> {
  const client = useSanctumClient()
  try {
    const { data } = await client<ApiItem<T>>(url)
    return data
  } catch (e) {
    throw createError({ statusCode: statusOf(e) })
  }
}

export async function apiSend<T>(url: string, method: 'POST' | 'PATCH' | 'DELETE', body?: Record<string, unknown> | FormData): Promise<T> {
  const client = useSanctumClient()
  const response = await client<ApiItem<T> | null>(url, { method, body })
  return (response as ApiItem<T> | null)?.data as T
}
