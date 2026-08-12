<script setup lang="ts">
// Meme police et meme URL que resources/views/layouts/app.blade.php (fonts.bunny.net).
useHead({
  title: 'AleChemist',
  link: [
    { rel: 'preconnect', href: 'https://fonts.bunny.net' },
    { rel: 'stylesheet', href: 'https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap' },
  ],
  bodyAttrs: {
    class: 'font-sans antialiased bg-anti-flash-white',
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
