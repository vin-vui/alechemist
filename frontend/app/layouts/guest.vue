<template>
  <div class="guest-shell">
    <div class="guest-shell__content">
      <slot />
    </div>
  </div>
</template>

<style scoped>
.guest-shell {
  min-height: 100vh;
  background-color: var(--color-ink);
  color: var(--color-text);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}

.guest-shell__content {
  width: 100%;
  max-width: 24rem;
}
</style>
