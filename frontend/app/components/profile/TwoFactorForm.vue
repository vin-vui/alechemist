<script setup lang="ts">
const client = useSanctumClient()
const { refreshIdentity } = useSanctumAuth()
const user = useCurrentUser()
const { modalVisible, modalError, modalPending, runConfirmed, confirm } = useConfirmPassword()

const qrCode = ref<string | null>(null)
const recoveryCodes = ref<string[]>([])
const confirmCode = ref('')
const confirming = ref(false)
const errors = ref<Record<string, string[]>>({})
// Un seul pending partage entre les 5 actions du composant : elles ne sont jamais
// declenchables simultanement (boutons visibles selon l'etat), donc pas besoin d'un
// ref par action.
const pending = ref(false)

async function enable() {
  pending.value = true
  try {
    await runConfirmed(async () => {
      await client('/user/two-factor-authentication', { method: 'POST' })
      const qr = await client<{ svg: string }>('/user/two-factor-qr-code')
      qrCode.value = qr.svg
      confirming.value = true
    })
  } finally {
    pending.value = false
  }
}

async function confirmSetup() {
  pending.value = true
  errors.value = {}
  try {
    await client('/user/confirmed-two-factor-authentication', { method: 'POST', body: { code: confirmCode.value } })
    recoveryCodes.value = await client<string[]>('/user/two-factor-recovery-codes')
    confirming.value = false
    qrCode.value = null
    await refreshIdentity()
  } catch (e) {
    errors.value = extractErrors(e)
  } finally {
    pending.value = false
  }
}

async function showRecoveryCodes() {
  pending.value = true
  try {
    await runConfirmed(async () => {
      recoveryCodes.value = await client<string[]>('/user/two-factor-recovery-codes')
    })
  } finally {
    pending.value = false
  }
}

async function regenerateRecoveryCodes() {
  pending.value = true
  try {
    await runConfirmed(async () => {
      await client('/user/two-factor-recovery-codes', { method: 'POST' })
      recoveryCodes.value = await client<string[]>('/user/two-factor-recovery-codes')
    })
  } finally {
    pending.value = false
  }
}

async function disable() {
  pending.value = true
  try {
    await runConfirmed(async () => {
      await client('/user/two-factor-authentication', { method: 'DELETE' })
      recoveryCodes.value = []
      await refreshIdentity()
    })
  } finally {
    pending.value = false
  }
}
</script>

<template>
  <div class="panel profile-section">
    <h2 class="profile-section__title">{{ $t('Two Factor Authentication') }}</h2>

    <template v-if="!user?.two_factor_enabled && !confirming">
      <button type="button" class="btn btn-primary" :disabled="pending" @click="enable">{{ $t('Enable') }}</button>
    </template>

    <template v-if="confirming && qrCode">
      <p class="profile-section__hint">{{ $t('Scan the following QR code using your phone\'s authenticator application.') }}</p>
      <!-- v-html sur un SVG genere par notre propre backend Fortify (/user/two-factor-qr-code),
           pas par un contenu utilisateur : pas de risque XSS ici. Fond blanc fixe
           (et non un token du systeme) : necessaire au contraste scannable du QR
           code par un lecteur de camera, independamment du theme sombre. -->
      <div class="two-factor__qr" v-html="qrCode" />
      <div class="field">
        <label class="field__label" for="tfa-code">{{ $t('Code') }}</label>
        <input id="tfa-code" v-model="confirmCode" inputmode="numeric" class="field__input two-factor__code-input" />
        <p v-if="errors.code" class="field__error">{{ errors.code[0] }}</p>
      </div>
      <button type="button" class="btn btn-primary" :disabled="pending" @click="confirmSetup">{{ $t('Confirm') }}</button>
    </template>

    <template v-if="user?.two_factor_enabled">
      <ul v-if="recoveryCodes.length" class="two-factor__codes data">
        <li v-for="code in recoveryCodes" :key="code">{{ code }}</li>
      </ul>
      <div class="two-factor__actions">
        <button type="button" class="btn btn-secondary" :disabled="pending" @click="showRecoveryCodes">{{ $t('Show Recovery Codes') }}</button>
        <button type="button" class="btn btn-secondary" :disabled="pending" @click="regenerateRecoveryCodes">{{ $t('Regenerate Recovery Codes') }}</button>
        <button type="button" class="btn btn-danger" :disabled="pending" @click="disable">{{ $t('Disable') }}</button>
      </div>
    </template>

    <ConfirmPasswordModal :visible="modalVisible" :error="modalError" :pending="modalPending" @confirm="confirm" @close="modalVisible = false" />
  </div>
</template>

<style scoped>
.profile-section {
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 0.875rem;
}

.profile-section__title {
  font-family: var(--font-display);
  text-transform: uppercase;
  letter-spacing: -0.02em;
  font-weight: 700;
  font-size: 1.125rem;
}

.profile-section__hint {
  font-size: var(--text-body);
  color: var(--color-text-dim);
}

.two-factor__qr {
  display: inline-block;
  background-color: #fff;
  padding: 0.5rem;
  border-radius: var(--radius);
}

.field {
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
}

.field__label {
  font-size: var(--text-label);
  color: var(--color-text-dim);
}

.field__input {
  height: 48px;
  background-color: var(--color-panel);
  border: 1px solid var(--color-rule);
  border-radius: var(--radius);
  padding-inline: 0.875rem;
  color: var(--color-text);
  font-family: var(--font-sans);
}

.two-factor__code-input {
  max-width: 12rem;
}

.field__error {
  font-size: var(--text-label);
  color: var(--color-ember);
}

.two-factor__codes {
  background-color: var(--color-ink);
  border: 1px solid var(--color-rule);
  border-radius: var(--radius);
  padding: 0.75rem;
  font-size: var(--text-body);
  list-style: none;
}

.two-factor__actions {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}
</style>
