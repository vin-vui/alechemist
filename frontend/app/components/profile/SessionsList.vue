<script setup lang="ts">
interface SessionRow {
  ip_address: string | null
  user_agent: string | null
  last_active_at: string
  is_current_device: boolean
}

const { data: sessions, refresh } = await useAsyncData('sessions', () => apiGet<SessionRow[]>('/api/user/sessions'))
const password = ref('')
const errors = ref<Record<string, string[]>>({})
const askPassword = ref(false)
const pending = ref(false)

async function logoutOthers() {
  pending.value = true
  errors.value = {}
  try {
    await apiSend('/api/user/sessions/logout-others', 'POST', { password: password.value })
    askPassword.value = false
    password.value = ''
    await refresh()
  } catch (e) {
    errors.value = extractErrors(e)
  } finally {
    pending.value = false
  }
}
</script>

<template>
  <div class="panel profile-section">
    <h2 class="profile-section__title">{{ $t('Browser Sessions') }}</h2>
    <ul class="sessions-list">
      <li v-for="(session, index) in sessions ?? []" :key="index" class="sessions-list__row">
        <span class="sessions-list__info">{{ session.ip_address }} - {{ session.user_agent }}</span>
        <span v-if="session.is_current_device" class="chip sessions-list__badge">{{ $t('This device') }}</span>
        <span v-else class="sessions-list__date data">{{ formatDate(session.last_active_at) }}</span>
      </li>
    </ul>
    <button v-if="!askPassword" type="button" class="btn btn-secondary" @click="askPassword = true">
      {{ $t('Log Out Other Browser Sessions') }}
    </button>
    <form v-else class="sessions-list__form" @submit.prevent="logoutOthers">
      <div class="field sessions-list__field">
        <label class="field__label" for="sessions-password">{{ $t('Password') }}</label>
        <input id="sessions-password" v-model="password" type="password" class="field__input" />
        <p v-if="errors.password" class="field__error">{{ errors.password[0] }}</p>
      </div>
      <button type="submit" class="btn btn-primary" :disabled="pending">{{ $t('Confirm') }}</button>
    </form>
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

.sessions-list {
  display: flex;
  flex-direction: column;
  gap: 0.625rem;
  font-size: var(--text-label);
}

.sessions-list__row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
}

.sessions-list__info {
  min-width: 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  color: var(--color-text);
}

.sessions-list__badge {
  flex: 0 0 auto;
}

.sessions-list__date {
  flex: 0 0 auto;
  color: var(--color-text-dim);
}

.sessions-list__form {
  display: flex;
  align-items: flex-end;
  gap: 0.5rem;
}

.sessions-list__field {
  flex: 1;
}

.field__label {
  font-size: var(--text-label);
  color: var(--color-text-dim);
}

.field__input {
  height: 48px;
  width: 100%;
  background-color: var(--color-panel);
  border: 1px solid var(--color-rule);
  border-radius: var(--radius);
  padding-inline: 0.875rem;
  color: var(--color-text);
  font-family: var(--font-sans);
}

.field__error {
  font-size: var(--text-label);
  color: var(--color-ember);
}
</style>
