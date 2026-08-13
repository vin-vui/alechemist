<script setup lang="ts">
const client = useSanctumClient()
const { refreshIdentity } = useSanctumAuth()
const user = useCurrentUser()

const form = reactive({ name: user.value?.name ?? '', email: user.value?.email ?? '' })
const errors = ref<Record<string, string[]>>({})
const saved = ref(false)
const pending = ref(false)

async function submit() {
  pending.value = true
  errors.value = {}
  saved.value = false
  try {
    await client('/user/profile-information', { method: 'PUT', body: form })
    await refreshIdentity()
    saved.value = true
  } catch (e) {
    errors.value = extractErrors(e)
  } finally {
    pending.value = false
  }
}
</script>

<template>
  <form class="panel profile-section" @submit.prevent="submit">
    <h2 class="profile-section__title">{{ $t('Profile Information') }}</h2>
    <div class="field">
      <label class="field__label" for="profile-name">{{ $t('Name') }}</label>
      <input id="profile-name" v-model="form.name" class="field__input" />
      <p v-if="errors.name" class="field__error">{{ errors.name[0] }}</p>
    </div>
    <div class="field">
      <label class="field__label" for="profile-email">{{ $t('Email') }}</label>
      <input id="profile-email" v-model="form.email" type="email" class="field__input" />
      <p v-if="errors.email" class="field__error">{{ errors.email[0] }}</p>
    </div>
    <div class="profile-section__actions">
      <button type="submit" class="btn btn-primary" :disabled="pending">{{ $t('Save') }}</button>
      <span v-if="saved" class="profile-section__saved">{{ $t('Saved.') }}</span>
    </div>
  </form>
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

.field__error {
  font-size: var(--text-label);
  color: var(--color-ember);
}

.profile-section__actions {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.profile-section__saved {
  font-size: var(--text-label);
  color: var(--color-malt);
}
</style>
