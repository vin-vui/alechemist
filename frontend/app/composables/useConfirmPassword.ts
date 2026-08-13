export function useConfirmPassword() {
  const client = useSanctumClient()
  const modalVisible = ref(false)
  const modalError = ref('')
  let pendingAction: (() => Promise<void>) | null = null

  async function runConfirmed(action: () => Promise<void>) {
    try {
      await action()
    } catch (e) {
      if (!needsPasswordConfirmation(e)) throw e
      pendingAction = action
      modalVisible.value = true
    }
  }

  async function confirm(password: string) {
    modalError.value = ''
    try {
      await client('/user/confirm-password', { method: 'POST', body: { password } })
      modalVisible.value = false
      if (pendingAction) {
        const action = pendingAction
        pendingAction = null
        await action()
      }
    } catch (e) {
      const errors = extractErrors(e)
      modalError.value = errors.password?.[0] ?? 'Erreur'
    }
  }

  return { modalVisible, modalError, runConfirmed, confirm }
}
