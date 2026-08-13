<script setup lang="ts">
const client = useSanctumClient()
const { refreshIdentity } = useSanctumAuth()
const user = useCurrentUser()
const { modalVisible, modalError, runConfirmed, confirm } = useConfirmPassword()

const qrCode = ref<string | null>(null)
const recoveryCodes = ref<string[]>([])
const confirmCode = ref('')
const confirming = ref(false)
const errors = ref<Record<string, string[]>>({})

async function enable() {
  await runConfirmed(async () => {
    await client('/user/two-factor-authentication', { method: 'POST' })
    const qr = await client<{ svg: string }>('/user/two-factor-qr-code')
    qrCode.value = qr.svg
    confirming.value = true
  })
}

async function confirmSetup() {
  errors.value = {}
  try {
    await client('/user/confirmed-two-factor-authentication', { method: 'POST', body: { code: confirmCode.value } })
    recoveryCodes.value = await client<string[]>('/user/two-factor-recovery-codes')
    confirming.value = false
    qrCode.value = null
    await refreshIdentity()
  } catch (e) {
    errors.value = extractErrors(e)
  }
}

async function showRecoveryCodes() {
  await runConfirmed(async () => {
    recoveryCodes.value = await client<string[]>('/user/two-factor-recovery-codes')
  })
}

async function regenerateRecoveryCodes() {
  await runConfirmed(async () => {
    await client('/user/two-factor-recovery-codes', { method: 'POST' })
    recoveryCodes.value = await client<string[]>('/user/two-factor-recovery-codes')
  })
}

async function disable() {
  await runConfirmed(async () => {
    await client('/user/two-factor-authentication', { method: 'DELETE' })
    recoveryCodes.value = []
    await refreshIdentity()
  })
}
</script>

<template>
  <div class="space-y-3 rounded bg-white p-4 shadow">
    <h2 class="font-shangrila text-lg">{{ $t('Two Factor Authentication') }}</h2>

    <template v-if="!user?.two_factor_enabled && !confirming">
      <button type="button" class="btn-yellow rounded" @click="enable">{{ $t('Enable') }}</button>
    </template>

    <template v-if="confirming && qrCode">
      <p class="text-sm">{{ $t('Scan the following QR code using your phone\'s authenticator application.') }}</p>
      <!-- v-html sur un SVG genere par notre propre backend Fortify (/user/two-factor-qr-code),
           pas par un contenu utilisateur : pas de risque XSS ici. -->
      <div class="inline-block rounded bg-white p-2" v-html="qrCode" />
      <div>
        <label class="block text-sm" for="tfa-code">{{ $t('Code') }}</label>
        <input id="tfa-code" v-model="confirmCode" inputmode="numeric" class="mt-1 w-48 rounded border-gray-300" />
        <p v-if="errors.code" class="mt-1 text-sm text-red-600">{{ errors.code[0] }}</p>
      </div>
      <button type="button" class="btn-yellow rounded" @click="confirmSetup">{{ $t('Confirm') }}</button>
    </template>

    <template v-if="user?.two_factor_enabled">
      <ul v-if="recoveryCodes.length" class="rounded bg-gray-100 p-3 font-mono text-sm">
        <li v-for="code in recoveryCodes" :key="code">{{ code }}</li>
      </ul>
      <div class="flex flex-wrap gap-2">
        <button type="button" class="rounded border px-3 py-1.5" @click="showRecoveryCodes">{{ $t('Show Recovery Codes') }}</button>
        <button type="button" class="rounded border px-3 py-1.5" @click="regenerateRecoveryCodes">{{ $t('Regenerate Recovery Codes') }}</button>
        <button type="button" class="rounded border border-red-600 px-3 py-1.5 text-red-600" @click="disable">{{ $t('Disable') }}</button>
      </div>
    </template>

    <ConfirmPasswordModal :visible="modalVisible" :error="modalError" @confirm="confirm" @close="modalVisible = false" />
  </div>
</template>
