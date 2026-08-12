<script setup lang="ts">
const route = useRoute()
const links = [
  { to: '/', label: 'Dashboard', match: /^\/$/ },
  { to: '/recipes', label: 'Recipes', match: /^\/recipes/ },
]
</script>

<template>
  <nav class="flex h-full flex-col bg-rich-black text-anti-flash-white">
    <NuxtLink to="/" class="flex items-center gap-3 px-4 py-5">
      <img src="/assets/logo/alechemist@512.png" alt="AleChemist" class="size-10 rounded" />
      <span class="font-shangrila text-xl">AleChemist</span>
    </NuxtLink>
    <ul class="flex-1 space-y-1 px-2">
      <li v-for="link in links" :key="link.to">
        <NuxtLink
          :to="link.to"
          class="block rounded px-3 py-2 hover:bg-licorice"
          :class="{ 'bg-licorice text-xanthous': link.match.test(route.path) }"
        >
          {{ $t(link.label) }}
        </NuxtLink>
      </li>
    </ul>
    <div class="px-2 pb-4">
      <NuxtLink to="/profile" class="block rounded px-3 py-2 hover:bg-licorice">
        {{ $t('Profile') }}
      </NuxtLink>
    </div>
  </nav>
</template>
