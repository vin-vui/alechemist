<script setup lang="ts">
import type { Dashboard } from '~/types/api'

const { data: dashboard, error } = await useAsyncData('dashboard', () => apiGet<Dashboard>('/api/dashboard'))
if (error.value) {
  throw createError({ statusCode: statusOf(error.value) })
}
const now = new Date()

// Heros (P1, docs/design-system.md) : au chargement, l'information capitale pour
// un brasseur est LE brassin en cours -- promu en bloc principal, avant les
// fermentations et bien avant le compteur de recettes (le moins urgent). S'il y a
// plusieurs brassins en cours (rare mais possible), seul le premier devient le
// heros ; les autres restent listes juste dessous, comportement inchange, juste
// redistribue visuellement (aucune donnee perdue).
const { t } = useI18n()

useHead({ title: () => t('Dashboard') })

const currentBrewing = computed(() => dashboard.value?.in_progress_brewings[0] ?? null)
const otherBrewings = computed(() => dashboard.value?.in_progress_brewings.slice(1) ?? [])

// Compte neuf : il n'y a aucune recette, donc « choisissez une recette » menait a
// une liste vide. La seule premiere action possible est d'importer un BeerXML.
const hasRecipes = computed(() => (dashboard.value?.recipe_count ?? 0) > 0)
</script>

<template>
  <div v-if="dashboard" class="dashboard">
    <!-- Meme en-tete que les autres ecrans. Le titre etait porte par le nom du
         brassin en heros, si bien qu'un tableau de bord vide n'avait aucun titre. -->
    <PageHeader :title="$t('Dashboard')" />

    <!-- Heros : LE brassin en cours. Etape courante mise en avant en --color-signal,
         toute la carte est l'acces direct a l'etape (meme convention que les autres
         cartes de cette page : carte cliquable, pas de bouton redondant). -->
    <NuxtLink
      v-if="currentBrewing"
      :to="brewingPath(currentBrewing)"
      class="panel dashboard__hero"
      :style="{ '--brewing-color': ebcToHex(currentBrewing.recipe?.color ?? null) }"
    >
      <p class="dashboard__hero-name">{{ currentBrewing.name }}</p>
      <p class="dashboard__hero-recipe">{{ currentBrewing.recipe?.name }}</p>
      <p class="dashboard__hero-meta">
        <span class="data">{{ formatDate(currentBrewing.date_start) }}</span>
        <span class="dashboard__sep">-</span>
        <span class="data">{{ formatQuantity(currentBrewing.volume, 'l') }}</span>
      </p>
      <p class="dashboard__hero-step">
        <span class="dashboard__hero-step-name">{{ $t(currentBrewing.current_step ?? 'preparation') }}</span>
        <span class="dashboard__hero-step-arrow" aria-hidden="true">&rarr;</span>
      </p>
    </NuxtLink>
    <!-- Etat vide : une seule invitation, et elle mene la ou il y a quelque chose a
         faire. Il y en avait trois, vers la meme page, sous trois libelles. -->
    <div v-else class="panel dashboard__hero dashboard__hero--empty">
      <p>{{ hasRecipes ? $t('No brewings in progress') : $t('Import your first recipe') }}</p>
      <NuxtLink :to="hasRecipes ? '/recipes' : '/recipes/import'" class="dashboard__empty-link">
        {{ hasRecipes ? $t('Recipes') : $t('Import File') }} &rarr;
      </NuxtLink>
    </div>

    <!-- Autres brassins en cours (au-dela du heros), s'il y en a. -->
    <section v-if="otherBrewings.length > 0" class="dashboard__section">
      <!-- Compte porte par le titre, comme celui des fermentations juste dessous.
           Meme carte que les deux listes de brassins : la suppression n'y figure
           pas, le tableau de bord est un ecran de coup d'oeil. -->
      <h2 class="section-title">{{ $t('n_other_brewings', otherBrewings.length) }}</h2>
      <div class="dashboard__grid">
        <BrewingCard
          v-for="brewing in otherBrewings"
          :key="brewing.id"
          :brewing="brewing"
          :deletable="false"
        />
      </div>
    </section>

    <section class="dashboard__section">
      <!-- Le titre ne porte le compteur que s'il est non nul (P1) : "0 FERMENTATIONS
           EN COURS" au-dessus de "Aucune fermentation en cours" disait deux fois la
           meme chose. A zero, seule l'invitation actionnable reste. -->
      <h2 v-if="dashboard.ferment_brewings.length > 0" class="section-title">
        {{ $t('n_fermentations', dashboard.ferment_brewings.length) }}
      </h2>
      <!-- Plus de lien ici : l'invitation de la page est celle du bloc principal. -->
      <p v-if="dashboard.ferment_brewings.length === 0" class="dashboard__empty">
        {{ $t('No fermentations in progress') }}
      </p>
      <div
        v-for="brewing in dashboard.ferment_brewings"
        :key="brewing.id"
        class="panel dashboard__ferment"
        :style="{ '--brewing-color': ebcToHex(brewing.recipe?.color ?? null) }"
      >
        <NuxtLink :to="brewingPath(brewing)" class="dashboard__ferment-name">
          {{ brewing.name }} <span class="dashboard__ferment-recipe">({{ brewing.recipe?.name }})</span>
        </NuxtLink>
        <div
          v-for="view in fermentSchedule(brewing.ferment_start!, brewing.steps ?? [], now)"
          :key="view.step.id"
          class="dashboard__ferment-row"
        >
          <div class="dashboard__ferment-row-head">
            <span class="dashboard__ferment-row-label">{{ view.step.field }}</span>
            <span class="data dashboard__ferment-row-meta" :class="{ 'dashboard__ferment-row-meta--late': view.late }">
              {{ view.late ? $t('late') : $t('n_days', view.daysLeft) }} - {{ formatDate(view.endsAt.toISOString()) }}
            </span>
          </div>
          <div class="dashboard__progress-track">
            <div class="dashboard__progress-bar" :style="{ width: `${view.progress}%` }" />
          </div>
        </div>
      </div>
    </section>

    <!-- L'archive des brassins termines. Elle occupait une page et un onglet a
         elle seule, qui redisaient ce que cet ecran montre deja. Repliee par
         defaut : le tableau de bord reste un coup d'oeil, et ne s'allonge pas
         d'une rangee a chaque brassin fini. -->
    <details v-if="dashboard.finished_brewings.length > 0" class="dashboard__archive">
      <!-- Le chevron est explicite : `display: flex` sur un `summary` efface le
           marqueur natif, et le resume se lisait alors comme un titre de section
           de plus, sans rien qui dise qu'il s'ouvre. -->
      <summary class="dashboard__archive-summary">
        <AppIcon name="chevron" class="dashboard__archive-chevron" />
        {{ $t('n_brewings_finished', dashboard.finished_brewings.length) }}
      </summary>
      <div class="dashboard__grid dashboard__archive-list">
        <BrewingCard
          v-for="brewing in dashboard.finished_brewings"
          :key="brewing.id"
          :brewing="brewing"
          :deletable="false"
        />
      </div>
    </details>

    <!-- Compteur de recettes : l'information la moins urgente de la page, en
         dernier (P1). Filet de separation plutot qu'un panel, pour ne pas rivaliser
         visuellement avec le heros et les fermentations.
         Le chiffre en gros au-dessus de « 1 recette » disait deux fois la meme
         chose : il ne reste que la phrase, et une fleche qui dit que c'est un lien. -->
    <NuxtLink v-if="hasRecipes" to="/recipes" class="dashboard__metric">
      <span class="dashboard__metric-label">{{ $t('n_recipes', dashboard.recipe_count) }}</span>
      <span aria-hidden="true">&rarr;</span>
    </NuxtLink>
  </div>
</template>

<style scoped>
.dashboard {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.dashboard__hero {
  display: block;
  padding: 1.5rem;
  border-left: 4px solid var(--brewing-color, var(--color-rule));
  transition: background-color 0.15s ease;
}

.dashboard__hero:hover {
  background-color: var(--color-panel-hi);
}

.dashboard__hero-name {
  font-family: var(--font-display);
  text-transform: uppercase;
  letter-spacing: -0.02em;
  font-weight: 800;
  font-size: 1.75rem;
  color: var(--color-text);
}

.dashboard__hero-recipe {
  margin-top: 0.25rem;
  font-size: var(--text-body);
  color: var(--color-text-dim);
}

.dashboard__hero-meta {
  margin-top: 0.5rem;
  font-size: var(--text-label);
  color: var(--color-text-dim);
}

.dashboard__hero-step {
  margin-top: 1rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.dashboard__hero-step-name {
  font-family: var(--font-display);
  text-transform: uppercase;
  letter-spacing: -0.02em;
  font-weight: 700;
  font-size: 1.25rem;
  color: var(--color-signal);
}

.dashboard__hero-step-arrow {
  color: var(--color-signal);
  font-size: 1.25rem;
  line-height: 1;
}

.dashboard__hero--empty {
  color: var(--color-text-dim);
  display: flex;
  flex-wrap: wrap;
  align-items: baseline;
  gap: 0.5rem;
}

.dashboard__section {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.dashboard__empty {
  color: var(--color-text-dim);
  font-size: var(--text-body);
}

.dashboard__empty-link {
  color: var(--color-signal);
}

.dashboard__ferment {
  padding: 1rem;
  border-left: 3px solid var(--brewing-color, var(--color-rule));
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.dashboard__ferment-name {
  font-weight: 600;
  color: var(--color-text);
}

.dashboard__ferment-name:hover {
  color: var(--color-text-dim);
}

.dashboard__ferment-recipe {
  font-size: var(--text-label);
  color: var(--color-text-dim);
  font-weight: 400;
}

.dashboard__ferment-row {
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
}

.dashboard__ferment-row-head {
  display: flex;
  justify-content: space-between;
  gap: 0.5rem;
  font-size: var(--text-label);
}

.dashboard__ferment-row-label {
  color: var(--color-text-dim);
}

.dashboard__ferment-row-meta {
  color: var(--color-text-dim);
  text-align: right;
}

.dashboard__ferment-row-meta--late {
  color: var(--color-ember);
}

.dashboard__progress-track {
  height: 0.375rem;
  border-radius: 9999px;
  background-color: var(--color-rule);
  overflow: hidden;
}

.dashboard__progress-bar {
  height: 100%;
  border-radius: 9999px;
  background-color: var(--color-malt);
}

/*
 * Deux colonnes au maximum. En trois colonnes dans une colonne de contenu de
 * 40rem, une carte seule occupait un tiers de la largeur et paraissait rabougrie
 * a cote du heros pleine largeur juste au-dessus.
 */
.dashboard__grid {
  display: grid;
  gap: 0.75rem;
  grid-template-columns: 1fr;
}

@media (min-width: 640px) {
  .dashboard__grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

.dashboard__sep {
  color: var(--color-text-faint);
  margin-inline: 0.25rem;
}

/* Le resume porte le compte et se lit comme un titre de section : deplie, il en
   devient un. */
.dashboard__archive-summary {
  cursor: pointer;
  min-height: 44px;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-family: var(--font-display);
  text-transform: uppercase;
  letter-spacing: -0.02em;
  font-weight: 700;
  font-size: 1.125rem;
  color: var(--color-text-dim);
}

.dashboard__archive-summary:hover {
  color: var(--color-text);
}

.dashboard__archive[open] .dashboard__archive-summary {
  color: var(--color-text);
}

.dashboard__archive-chevron {
  width: 1.125rem;
  height: 1.125rem;
  flex: 0 0 auto;
  transition: transform 0.15s ease;
}

.dashboard__archive[open] .dashboard__archive-chevron {
  transform: rotate(90deg);
}

.dashboard__archive-list {
  margin-top: 0.75rem;
}

.dashboard__metric {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  align-self: flex-start;
  min-height: 44px;
  font-size: var(--text-label);
  color: var(--color-text-dim);
  transition: color 0.15s ease;
}

.dashboard__metric:hover {
  color: var(--color-text);
}
</style>
