<script setup lang="ts">
const password = ref('')
const errors = ref<Record<string, string[]>>({})
// Un echec sans detail de validation (reseau, 419, 500) laissait le formulaire
// immobile et muet : on le dit.
const failed = ref(false)
const askPassword = ref(false)
const pending = ref(false)

async function destroyAccount() {
  pending.value = true
  errors.value = {}
  failed.value = false
  try {
    await apiSend('/api/user', 'DELETE', { password: password.value })
    window.location.href = '/login'
  } catch (e) {
    errors.value = extractErrors(e)
    failed.value = Object.keys(errors.value).length === 0
  } finally {
    pending.value = false
  }
}
</script>

<template>
  <div class="panel profile-section profile-section--danger">
    <h2 class="section-title profile-section__title--danger">{{ $t('Delete Account') }}</h2>
    <p v-if="failed" class="banner-error">{{ $t('Action failed') }}</p>
    <p class="profile-section__hint">{{ $t('Once your account is deleted, all of its resources and data will be permanently deleted.') }}</p>
    <button v-if="!askPassword" type="button" class="btn btn-danger" @click="askPassword = true">
      {{ $t('Delete Account') }}
    </button>
    <form v-else class="delete-account__form" @submit.prevent="destroyAccount">
      <div class="field delete-account__field">
        <label class="field__label" for="delete-password">{{ $t('Password') }}</label>
        <input id="delete-password" v-model="password" type="password" class="field__input" />
        <p v-if="errors.password" class="field__error">{{ errors.password[0] }}</p>
      </div>
      <button type="submit" class="btn btn-danger" :disabled="pending">{{ $t('Confirm') }}</button>
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

/* Un filet ember suffit a isoler la zone : le contour double, le titre et le bouton
   faisaient trois signaux pour une seule action. */
.profile-section--danger {
  border-color: var(--color-ember);
}

.profile-section__title--danger {
  color: var(--color-ember);
}

.profile-section__hint {
  font-size: var(--text-body);
  color: var(--color-text-dim);
}

.delete-account__form {
  display: flex;
  align-items: flex-end;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.delete-account__field {
  flex: 1 1 12rem;
}
</style>
