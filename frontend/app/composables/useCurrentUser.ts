import type { User } from '~/types/api'

// nuxt-auth-sanctum stocke la reponse brute de GET /api/user, donc l'enveloppe {data: {...}}.
export function useCurrentUser() {
  const { user } = useSanctumAuth<{ data: User }>()
  return computed(() => user.value?.data ?? null)
}
