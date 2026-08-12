import type { ApiItem } from '~/types/api'

export async function apiGet<T>(url: string): Promise<T> {
  const client = useSanctumClient()
  const { data } = await client<ApiItem<T>>(url)
  return data
}

export async function apiSend<T>(url: string, method: 'POST' | 'PATCH' | 'DELETE', body?: Record<string, unknown> | FormData): Promise<T> {
  const client = useSanctumClient()
  const response = await client<ApiItem<T> | null>(url, { method, body })
  return (response as ApiItem<T> | null)?.data as T
}
