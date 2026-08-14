<script setup lang="ts">
// Sans ce fichier, Nuxt sert sa page d'erreur par defaut : fond blanc, texte
// anglais, onglet titre « 404 - Not Found | Nuxt ». Au milieu d'une app sombre et
// francaise, c'etait l'ecran le plus etranger au produit.
import type { NuxtError } from '#app'

const props = defineProps<{ error: NuxtError }>()

const { t } = useI18n()

const isNotFound = computed(() => props.error?.statusCode === 404)

// Le message dit ce qui s'est passe et quoi faire (contrat design-system.md,
// Ecriture) : pas de « Oups ! Un probleme est survenu. »
const title = computed(() => (isNotFound.value ? t('Page not found') : t('Server unreachable')))
const detail = computed(() =>
  isNotFound.value ? t('This page does not exist or is no longer yours.') : t('Retry in a moment.'))

useHead({ title: () => title.value })

/*
 * Navigation dure et non `clearError({ redirect })` : cette page est le seul ecran
 * sans barre de navigation, donc la seule sortie. Si l'app est arrivee ici parce
 * que son etat est casse (chunk manquant apres un deploiement, service worker
 * perime), une redirection cote client la laisse sur place. Un rechargement
 * complet repart toujours d'une base saine.
 */
function goHome() {
  window.location.assign('/')
}
</script>

<template>
  <div class="error-page">
    <div class="panel error-page__card">
      <p class="error-page__code data">{{ error?.statusCode ?? 500 }}</p>
      <h1 class="error-page__title">{{ title }}</h1>
      <p class="error-page__detail">{{ detail }}</p>
      <button type="button" class="btn btn-primary error-page__action" @click="goHome">
        {{ $t('Back to dashboard') }}
      </button>
    </div>
  </div>
</template>

<style scoped>
.error-page {
  min-height: 100vh;
  background-color: var(--color-ink);
  color: var(--color-text);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}

.error-page__card {
  width: 100%;
  max-width: 28rem;
  padding: 2rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.error-page__code {
  font-size: var(--text-metric);
  color: var(--color-ember);
  line-height: 1;
}

.error-page__title {
  font-family: var(--font-display);
  text-transform: uppercase;
  letter-spacing: -0.02em;
  font-weight: 800;
  font-size: 1.5rem;
}

.error-page__detail {
  color: var(--color-text-dim);
}

.error-page__action {
  margin-top: 0.75rem;
  width: 100%;
}
</style>
