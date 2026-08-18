<script setup lang="ts">
// DataRow : la ligne de donnee du contrat (docs/design-system.md #4).
// Une ligne = une action ou une mesure. Case optionnelle (absente en lecture
// seule sur une recette : la quantite passe alors a gauche). Toute la ligne est
// cliquable quand une case est presente.
const props = withDefaults(
  defineProps<{
    label: string
    quantity?: string | null
    meta?: string | null
    state?: 'pending' | 'done' | 'late' | 'running'
    checkable?: boolean
    checked?: boolean
    disabled?: boolean
  }>(),
  {
    quantity: null,
    meta: null,
    state: 'pending',
    checkable: true,
    checked: false,
    disabled: false,
  },
)

const emit = defineEmits<{ toggle: [] }>()

/*
 * Le nom accessible de la case est pose explicitement, et pas laisse au contenu du
 * label : quand une ligne porte un bouton d'action (le « Demarrer » d'un palier
 * d'empatage), la case s'annoncait « 67 °C Saccharification pendant 60 minutes
 * Demarrer », en avalant le libelle du bouton. Le calcul ne peut pas dependre de
 * `useSlots()` pour ne s'appliquer qu'aux lignes avec action : les slots ne sont
 * pas reactifs, le computed serait fige sur son premier rendu et le nom
 * redeviendrait faux des que le bouton reapparait.
 */
const checkboxLabel = computed(() => [props.quantity, props.label, props.meta].filter(Boolean).join(' '))
</script>

<template>
  <label
    v-if="checkable"
    class="data-row"
    :class="[`data-row--${state}`, { 'data-row--disabled': disabled }]"
  >
    <input
      type="checkbox"
      class="checkbox data-row__checkbox"
      :aria-label="checkboxLabel"
      :checked="checked"
      :disabled="disabled"
      @change="emit('toggle')"
    />
    <span v-if="quantity" class="data-row__quantity data">{{ quantity }}</span>
    <span class="data-row__label">{{ label }}</span>
    <span v-if="meta" class="data-row__meta data">{{ meta }}</span>
    <!-- Action propre a la ligne (le « Demarrer » d'un palier d'empatage). Elle vit
         dans le label : au consommateur d'arreter le clic pour que le bouton ne
         coche pas la case au passage. -->
    <span v-if="$slots.action" class="data-row__action"><slot name="action" /></span>
  </label>
  <div v-else class="data-row" :class="`data-row--${state}`">
    <span v-if="quantity" class="data-row__quantity data">{{ quantity }}</span>
    <span class="data-row__label">{{ label }}</span>
    <span v-if="meta" class="data-row__meta data">{{ meta }}</span>
  </div>
</template>

<style scoped>
/*
 * La ligne peut passer sur deux niveaux : sur mobile, la meta (l'echeance) descend
 * sous le nom au lieu d'etre tronquee. C'etait le pire defaut de l'ecran de
 * brassage -- « Saccharification pendant 6... », « Mise en bouteille 7 jours -
 * 16 jo... » : l'information coupee etait justement celle dont le brasseur a
 * besoin, et le `title` de secours est inatteignable au doigt.
 */
.data-row {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  column-gap: 0.75rem;
  row-gap: 0.25rem;
  min-height: 56px;
  background-color: var(--color-panel);
  border-radius: var(--radius);
  border-left: 3px solid transparent;
  padding-block: 0.625rem;
  padding-inline: 1rem;
  cursor: pointer;
}

div.data-row {
  cursor: default;
}

.data-row--disabled {
  cursor: default;
}

.data-row--done {
  border-left-color: var(--color-malt);
}

.data-row--late {
  border-left-color: var(--color-ember);
}

/* Etape en cours : le signal unique du contrat (design-system.md #1). */
.data-row--running {
  border-left-color: var(--color-signal);
}

.data-row__action {
  flex: 0 0 auto;
  margin-left: auto;
}

.data-row__quantity {
  min-width: 3.5rem;
  flex: 0 0 auto;
  color: var(--color-text-dim);
}

/*
 * Le nom est l'information principale (contrat design-system.md #4) : il garde
 * toute la largeur restante et revient a la ligne plutot que de deborder
 * ("une ligne peut faire deux lignes de texte, c'est mieux qu'un nom illisible").
 */
.data-row__label {
  flex: 1 1 auto;
  min-width: 0;
  overflow-wrap: break-word;
  color: var(--color-text);
}

/* Pleine largeur : la meta occupe sa propre ligne sous le nom. */
.data-row__meta {
  flex: 1 0 100%;
  min-width: 0;
  overflow-wrap: break-word;
  color: var(--color-text-dim);
}

/* A partir de sm:, la place ne manque plus : la meta remonte a droite du nom. */
@media (min-width: 640px) {
  .data-row__meta {
    flex: 0 1 auto;
    margin-left: auto;
    text-align: right;
    white-space: nowrap;
  }
}

.data-row--late .data-row__meta {
  color: var(--color-ember);
}
</style>
