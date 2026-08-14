<script setup lang="ts">
import type { Brewing, Recipe } from '~/types/api'

const { t } = useI18n()
const route = useRoute()
const recipeId = Number(route.params.recipe)
const { data: brewings, error, refresh } = await useAsyncData(`brewings-${recipeId}`, () =>
  apiGet<Brewing[]>(`/api/recipes/${recipeId}/brewings`))
if (error.value) {
  throw createError({ statusCode: statusOf(error.value) })
}

// La couleur EBC identifie la recette sur toutes les autres listes : les cartes de
// brassin etaient les seules a en etre privees.
const { data: recipe } = await useAsyncData(`recipe-${recipeId}`, () => apiGet<Recipe>(`/api/recipes/${recipeId}`))

useHead({ title: () => t('Brewing list') })

const toDelete = ref<Brewing | null>(null)
const deleting = ref(false)
const deleteError = ref(false)

async function destroyBrewing() {
  if (!toDelete.value) return
  deleting.value = true
  try {
    await apiSend(`/api/brewings/${toDelete.value.id}`, 'DELETE')
    toDelete.value = null
    await refresh()
  } catch {
    deleteError.value = true
    toDelete.value = null
  } finally {
    deleting.value = false
  }
}
</script>

<template>
  <div class="brewings">
    <!-- Le titre porte le nom de la recette : sans lien de retour, c'est lui qui
         dit de quelle recette on liste les brassins. -->
    <PageHeader :title="$t('Brewing list')">
      <template #meta>
        <p v-if="recipe" class="brewings__recipe">{{ recipe.name }}</p>
      </template>
    </PageHeader>

    <p v-if="deleteError" class="banner-error">{{ $t('Brewing not deleted') }}</p>

    <p v-if="(brewings ?? []).length === 0" class="brewings__empty">
      {{ $t('No brewings for this recipe') }}
    </p>

    <div v-else class="brewings__list">
      <BrewingCard
        v-for="brewing in brewings ?? []"
        :key="brewing.id"
        :brewing="brewing"
        :recipe="recipe"
        @delete="toDelete = brewing"
      />
    </div>

    <PageAction>
      <NuxtLink :to="`/recipes/${recipeId}/brewings/new`" class="btn btn-primary">
        <AppIcon name="plus" />
        <span>{{ $t('New Brewing') }}</span>
      </NuxtLink>
    </PageAction>

    <ConfirmDialog
      :open="toDelete !== null"
      :title="$t('Delete this brewing?')"
      :detail="`${toDelete?.name ?? ''} - ${$t('Deleting is final.')}`"
      :confirm-label="$t('Delete')"
      :pending="deleting"
      @confirm="destroyBrewing"
      @close="toDelete = null"
    />
  </div>
</template>

<style scoped>
.brewings {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.brewings__empty {
  color: var(--color-text-dim);
}

.brewings__recipe {
  margin-top: 0.25rem;
  color: var(--color-text-dim);
}

.brewings__list {
  display: grid;
  gap: 0.75rem;
  grid-template-columns: 1fr;
}

@media (min-width: 640px) {
  .brewings__list {
    grid-template-columns: repeat(2, 1fr);
  }
}
</style>
