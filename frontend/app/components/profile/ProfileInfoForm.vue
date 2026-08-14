<script setup lang="ts">
const client = useSanctumClient()
const { refreshIdentity } = useSanctumAuth()
const user = useCurrentUser()

const form = reactive({ name: user.value?.name ?? '', email: user.value?.email ?? '' })
const errors = ref<Record<string, string[]>>({})
// Un echec sans detail de validation (reseau, 419, 500) laissait le formulaire
// immobile et muet : on le dit.
const failed = ref(false)
const saved = ref(false)
const pending = ref(false)

async function submit() {
  pending.value = true
  errors.value = {}
  failed.value = false
  saved.value = false
  try {
    await client('/user/profile-information', { method: 'PUT', body: form })
    await refreshIdentity()
    saved.value = true
  } catch (e) {
    errors.value = extractErrors(e)
    failed.value = Object.keys(errors.value).length === 0
  } finally {
    pending.value = false
  }
}
</script>

<template>
  <form class="panel profile-section" @submit.prevent="submit">
    <h2 class="section-title">{{ $t('Profile Information') }}</h2>
    <p v-if="failed" class="banner-error">{{ $t('Action failed') }}</p>
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
      <!-- Deux boutons « Sauvegarder » identiques se suivaient sur cet ecran, pour
           deux actions differentes : chacun dit ce qu'il enregistre. -->
      <button type="submit" class="btn btn-primary" :disabled="pending">{{ $t('Save profile') }}</button>
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
