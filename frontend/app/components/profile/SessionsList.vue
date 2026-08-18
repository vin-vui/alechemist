<script setup lang="ts">
interface SessionRow {
  ip_address: string | null
  user_agent: string | null
  last_active_at: string
  is_current_device: boolean
}

const { t } = useI18n()

const { data: sessions, refresh } = await useAsyncData('sessions', () => apiGet<SessionRow[]>('/api/user/sessions'))

/*
 * La ligne affichait l'IP brute suivie du User-Agent complet, tronque a l'ellipse :
 * « 172.17.0.1 - Mozilla/5.0 (X11; Lin... ». On nomme l'appareil comme l'utilisateur
 * le reconnait : son navigateur et son systeme.
 */
const BROWSERS: [RegExp, string][] = [
  [/Edg\//, 'Edge'],
  [/OPR\/|Opera/, 'Opera'],
  [/Chrome\//, 'Chrome'],
  [/Safari\//, 'Safari'],
  [/Firefox\//, 'Firefox'],
]

const PLATFORMS: [RegExp, string][] = [
  [/Android/, 'Android'],
  [/iPhone|iPad|iOS/, 'iOS'],
  [/Mac OS X|Macintosh/, 'macOS'],
  [/Windows/, 'Windows'],
  [/Linux|X11/, 'Linux'],
]

function describe(userAgent: string | null): string {
  if (!userAgent) return t('Unknown device')
  const browser = BROWSERS.find(([pattern]) => pattern.test(userAgent))?.[1]
  const platform = PLATFORMS.find(([pattern]) => pattern.test(userAgent))?.[1]
  if (browser && platform) return `${browser} - ${platform}`
  return browser ?? platform ?? t('Unknown device')
}
const password = ref('')
const errors = ref<Record<string, string[]>>({})
// Un echec sans detail de validation (reseau, 419, 500) laissait le formulaire
// immobile et muet : on le dit.
const failure = ref<string | null>(null)
const askPassword = ref(false)
const pending = ref(false)

async function logoutOthers() {
  pending.value = true
  errors.value = {}
  failure.value = null
  try {
    await apiSend('/api/user/sessions/logout-others', 'POST', { password: password.value })
    askPassword.value = false
    password.value = ''
    await refresh()
  } catch (e) {
    errors.value = extractErrors(e)
    failure.value = Object.keys(errors.value).length === 0 ? failureMessageKey(e) : null
  } finally {
    pending.value = false
  }
}
</script>

<template>
  <div class="panel profile-section">
    <h2 class="section-title">{{ $t('Browser Sessions') }}</h2>
    <p v-if="failure" class="banner-error">{{ $t(failure) }}</p>
    <ul class="sessions-list">
      <li v-for="(session, index) in sessions ?? []" :key="index" class="sessions-list__row">
        <span class="sessions-list__info">{{ describe(session.user_agent) }}</span>
        <span v-if="session.is_current_device" class="chip sessions-list__badge">{{ $t('This device') }}</span>
        <span v-else class="sessions-list__date data">{{ formatDate(session.last_active_at) }}</span>
      </li>
    </ul>
    <button
      v-if="!askPassword"
      type="button"
      class="btn btn-secondary sessions-list__logout"
      @click="askPassword = true"
    >
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

/* Le bouton reste dans la carte : son libelle, le plus long de l'app, poussait la
   page a 478px de large pour 390px de viewport. */
.sessions-list__logout {
  align-self: flex-start;
}

.sessions-list__form {
  display: flex;
  align-items: flex-end;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.sessions-list__field {
  flex: 1 1 12rem;
}
</style>
