<script setup lang="ts">
import type { Recipe } from '~/types/api'

const { t } = useI18n()
const { data: recipes, error } = await useAsyncData('recipes', () => apiGet<Recipe[]>('/api/recipes'))
if (error.value) {
  throw createError({ statusCode: statusOf(error.value) })
}

useHead({ title: () => t('Recipes') })

const isEmpty = computed(() => (recipes.value ?? []).length === 0)
</script>

<template>
  <div class="recipes">
    <PageHeader :title="$t('Recipes')" />

    <!-- Etat vide : une seule invitation. Le lien « Importer » doublait le bouton
         flottant, deux appels ambre pour la meme action sur le meme ecran. -->
    <p v-if="isEmpty" class="recipes__empty">{{ $t('No recipes yet') }}</p>

    <div v-else class="recipes__grid">
      <NuxtLink
        v-for="recipe in recipes ?? []"
        :key="recipe.id"
        :to="`/recipes/${recipe.id}`"
        class="panel recipes__card"
        :style="{ '--recipe-color': ebcToHex(recipe.color) }"
      >
        <p class="recipes__card-name">{{ recipe.name }}</p>
        <p class="recipes__card-meta">
          <span>{{ recipe.type }}</span>
          <span class="recipes__sep">-</span>
          <span class="data">{{ $t('n_brewings', recipe.brewings_count ?? 0) }}</span>
        </p>
      </NuxtLink>
    </div>
    <PageAction>
      <NuxtLink to="/recipes/import" class="btn btn-primary">
        <AppIcon name="import" />
        <span>{{ $t('Import File') }}</span>
      </NuxtLink>
    </PageAction>
  </div>
</template>

<style scoped>
.recipes {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.recipes__empty {
  color: var(--color-text-dim);
}

/* Deux colonnes au maximum : voir la meme note sur la liste des brassins. */
.recipes__grid {
  display: grid;
  gap: 0.75rem;
  grid-template-columns: 1fr;
}

@media (min-width: 640px) {
  .recipes__grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

.recipes__card {
  display: block;
  padding: 1rem;
  border-left: 4px solid var(--recipe-color, var(--color-rule));
  transition: background-color 0.15s ease;
}

.recipes__card:hover {
  background-color: var(--color-panel-hi);
}

.recipes__card-name {
  font-family: var(--font-display);
  text-transform: uppercase;
  letter-spacing: -0.02em;
  font-weight: 700;
  color: var(--color-text);
}

.recipes__card-meta {
  margin-top: 0.375rem;
  font-size: var(--text-label);
  color: var(--color-text-dim);
}

.recipes__sep {
  margin-inline: 0.375rem;
  color: var(--color-text-faint);
}



</style>
