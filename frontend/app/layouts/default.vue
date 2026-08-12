<script setup lang="ts">
const sidebarOpen = ref(false)
</script>

<template>
  <div class="min-h-screen bg-anti-flash-white text-rich-black">
    <!-- Barre mobile -->
    <header class="flex h-12 items-center justify-between bg-rich-black px-4 lg:hidden">
      <button type="button" class="text-anti-flash-white" @click="sidebarOpen = !sidebarOpen">
        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>
      <img src="/assets/logo/alechemist@512.png" alt="AleChemist" class="size-8 rounded" />
    </header>
    <!-- Sidebar mobile off-canvas -->
    <div v-if="sidebarOpen" class="fixed inset-0 z-40 lg:hidden" @click.self="sidebarOpen = false">
      <div class="absolute inset-0 bg-black/50" />
      <div class="absolute inset-y-0 left-0 w-64" @click="sidebarOpen = false">
        <AppSidebar />
      </div>
    </div>
    <!-- Sidebar desktop -->
    <aside class="fixed inset-y-0 left-0 hidden w-64 lg:block">
      <AppSidebar />
    </aside>
    <main class="p-4 lg:pl-68">
      <slot />
    </main>
  </div>
</template>
