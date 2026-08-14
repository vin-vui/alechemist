<script setup lang="ts">
// Figtree (texte), Archivo (display) et IBM Plex Mono (donnees) : meme mecanisme
// fonts.bunny.net, une seule balise link combinee (cf. docs/design-system.md).
const { locale } = useI18n()

// Relais de la locale vers les formateurs de `utils/format`, qui sont des fonctions
// pures sans acces au contexte Nuxt. Ici et pas dans un plugin : un plugin appelant
// useI18n() s'execute avant celui de @nuxtjs/i18n et fait echouer l'initialisation
// de l'app entiere.
setFormatLocale(locale.value)
watch(locale, (code) => setFormatLocale(code))

// `lang` etait vide : un lecteur d'ecran lisait du francais avec la prononciation
// par defaut du navigateur, et Chrome proposait de traduire la page.
// `titleTemplate` : chaque page pose son propre titre, l'onglet et l'historique
// cessent d'etre une suite de « AleChemist » indistinguables.
useHead({
  htmlAttrs: { lang: () => locale.value },
  titleTemplate: (page) => (page ? `${page} - AleChemist` : 'AleChemist'),
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
