<script setup lang="ts">
// Figtree (texte), Archivo (display) et IBM Plex Mono (donnees) : meme mecanisme
// fonts.bunny.net, une seule balise link combinee (cf. docs/design-system.md).
useHead({
  title: 'AleChemist',
  link: [
    { rel: 'preconnect', href: 'https://fonts.bunny.net' },
    {
      rel: 'stylesheet',
      href: 'https://fonts.bunny.net/css?family=figtree:400,500,600|archivo:700,800|ibm-plex-mono:400,500&display=swap',
    },
  ],
  bodyAttrs: {
    class: 'font-sans antialiased bg-ink',
  },
})
</script>

<template>
  <div>
    <NuxtRouteAnnouncer />
    <NuxtLayout>
      <NuxtPage />
    </NuxtLayout>
  </div>
</template>
