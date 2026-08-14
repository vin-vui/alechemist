<script setup lang="ts">
// Rail desktop + barre basse mobile (docs/design-system.md, Mise en page). Memes 3
// destinations sur les deux formats. La deconnexion n'y vit pas : elle est
// accessible depuis la page Profil (cf. pages/profile.vue).
//
// Il y a eu une quatrieme destination, « Brassins ». Elle disait la meme chose que
// le tableau de bord, qui liste deja les brassins ; sa page a ete versee dedans.
type IconName = 'dashboard' | 'recipes' | 'profile'

const ICONS: Record<IconName, string> = {
  dashboard: '<path d="M3 11.5 12 4l9 7.5" /><path d="M5 10v9a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1v-9" />',
  recipes:
    '<path d="M4 5.5C4 4.67 4.67 4 5.5 4H11v16H5.5C4.67 20 4 19.33 4 18.5v-13Z" /><path d="M20 5.5c0-.83-.67-1.5-1.5-1.5H13v16h5.5c.83 0 1.5-.67 1.5-1.5v-13Z" />',
  profile: '<circle cx="12" cy="8" r="3.25" /><path d="M5 20c0-3.5 3-6 7-6s7 2.5 7 6" />',
}

const route = useRoute()
// `match` : tout ce qui vit sous une recette eclaire Recettes, brassins compris.
// Un ecran de brassin n'allumait aucun onglet, on ne savait plus dans quelle
// section on se trouvait.
const links: { to: string; label: string; match: RegExp; icon: IconName }[] = [
  { to: '/', label: 'Dashboard', match: /^\/$/, icon: 'dashboard' },
  { to: '/recipes', label: 'Recipes', match: /^\/recipes/, icon: 'recipes' },
  { to: '/profile', label: 'Profile', match: /^\/profile/, icon: 'profile' },
]
</script>

<template>
  <!-- Rail desktop : libelles visibles en permanence. Trois icones sans legende
       etaient une devinette, et sur 1440px la place ne manque pas. -->
  <aside class="app-rail">
    <NuxtLink to="/" class="app-rail__brand">
      <img src="/assets/logo/alechemist@512.png" alt="" class="app-rail__logo" />
      <span class="app-rail__wordmark">AleChemist</span>
    </NuxtLink>
    <nav class="app-rail__nav" :aria-label="$t('Main navigation')">
      <NuxtLink
        v-for="link in links"
        :key="link.to"
        :to="link.to"
        class="app-rail__link"
        :class="{ 'app-rail__link--active': link.match.test(route.path) }"
      >
        <svg class="app-rail__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" v-html="ICONS[link.icon]" />
        <span class="app-rail__label">{{ $t(link.label) }}</span>
      </NuxtLink>
    </nav>
  </aside>

  <!-- Barre basse mobile -->
  <nav class="app-tabbar" :aria-label="$t('Main navigation')">
    <NuxtLink
      v-for="link in links"
      :key="link.to"
      :to="link.to"
      class="app-tabbar__link"
      :class="{ 'app-tabbar__link--active': link.match.test(route.path) }"
    >
      <svg class="app-tabbar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" v-html="ICONS[link.icon]" />
      <span class="app-tabbar__label">{{ $t(link.label) }}</span>
    </NuxtLink>
  </nav>
</template>

<style scoped>
.app-rail {
  display: none;
}

@media (min-width: 1024px) {
  .app-rail {
    display: flex;
    flex-direction: column;
    position: fixed;
    top: 0;
    bottom: 0;
    left: 0;
    /* 12.5rem : « Tableau de bord », le plus long des quatre libelles, doit tenir
       dans sa pastille active sans la toucher. */
    width: 12.5rem;
    background-color: var(--color-panel);
    border-right: 1px solid var(--color-rule);
    overflow: hidden;
    z-index: 40;
  }
}

.app-rail__brand {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem;
  flex: 0 0 auto;
  white-space: nowrap;
}

.app-rail__logo {
  width: 2rem;
  height: 2rem;
  min-width: 2rem;
  border-radius: var(--radius);
}

/* Le bloc-marque Shangrila, seul endroit de l'app ou cette police vit (contrat typo).
   Elle est illisible sous 24px, d'ou 1.5rem. */
.app-rail__wordmark {
  font-family: var(--font-brand);
  font-size: 1.5rem;
  color: var(--color-text);
}

.app-rail__nav {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  padding: 0.5rem;
}

.app-rail__link {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.625rem;
  min-height: 44px;
  border-radius: var(--radius);
  color: var(--color-text-dim);
  white-space: nowrap;
}

.app-rail__link:hover {
  background-color: var(--color-panel-hi);
  color: var(--color-text);
}

.app-rail__link--active {
  background-color: var(--color-panel-hi);
  color: var(--color-signal);
}

.app-rail__icon {
  width: 1.5rem;
  height: 1.5rem;
  min-width: 1.5rem;
}

.app-rail__label {
  font-family: var(--font-display);
  text-transform: uppercase;
  letter-spacing: -0.02em;
  font-size: var(--text-label);
}

/* Barre basse mobile : 3 destinations, pouce a portee. Sa hauteur est celle que
   declare --nav-height (main.css) : la mise en page et la barre d'action
   principale s'en servent pour se poser exactement dessus. */
.app-tabbar {
  display: flex;
  justify-content: space-around;
  position: fixed;
  left: 0;
  right: 0;
  bottom: 0;
  min-height: var(--nav-height);
  background-color: var(--color-panel);
  border-top: 1px solid var(--color-rule);
  padding-bottom: env(safe-area-inset-bottom);
  z-index: 40;
}

@media (min-width: 1024px) {
  .app-tabbar {
    display: none;
  }
}

.app-tabbar__link {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.125rem;
  padding: 0.5rem 0;
  min-height: 44px;
  color: var(--color-text-dim);
}

.app-tabbar__link--active {
  color: var(--color-signal);
}

.app-tabbar__icon {
  width: 1.5rem;
  height: 1.5rem;
}

/* Le libelle passe sur deux lignes plutot que d'etre rogne : « Tableau de bord »
   tient sur une ligne a trois destinations, mais pas sur les ecrans les plus
   etroits. */
.app-tabbar__label {
  font-family: var(--font-display);
  text-transform: uppercase;
  letter-spacing: -0.02em;
  font-size: 0.625rem;
  text-align: center;
  line-height: 1.15;
  padding-inline: 0.125rem;
}
</style>
