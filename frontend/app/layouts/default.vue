<!--
  Rail desktop + barre basse mobile (AppSidebar), colonne de contenu 40rem centree
  (docs/design-system.md, Mise en page). Plus de menu off-canvas : la barre basse
  fixe remplace le hamburger sur mobile.
-->
<template>
  <div class="app-shell">
    <AppSidebar />
    <main class="app-shell__main">
      <div class="app-shell__content">
        <slot />
      </div>
    </main>
  </div>
</template>

<style scoped>
.app-shell {
  min-height: 100vh;
  background-color: var(--color-ink);
  color: var(--color-text);
}

.app-shell__main {
  padding-top: 1rem;
  padding-right: 1rem;
  padding-left: 1rem;
  padding-bottom: calc(4.5rem + env(safe-area-inset-bottom) + 1rem);
}

@media (min-width: 1024px) {
  .app-shell__main {
    padding-top: 1.5rem;
    padding-right: 1.5rem;
    padding-bottom: 1.5rem;
    padding-left: 6rem;
  }
}

.app-shell__content {
  max-width: 40rem;
  margin-inline: auto;
}
</style>
