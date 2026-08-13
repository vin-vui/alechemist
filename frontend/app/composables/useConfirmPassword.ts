export function useConfirmPassword() {
  const client = useSanctumClient()
  const modalVisible = ref(false)
  const modalError = ref('')
  const modalPending = ref(false)
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
    modalPending.value = true
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
    } finally {
      modalPending.value = false
    }
  }

  return { modalVisible, modalError, modalPending, runConfirmed, confirm }
}
