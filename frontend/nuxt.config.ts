import tailwindcss from '@tailwindcss/vite'

export default defineNuxtConfig({
  compatibilityDate: '2026-08-12',
  ssr: false,
  devtools: { enabled: true },
  modules: ['nuxt-auth-sanctum', '@nuxtjs/i18n', '@vite-pwa/nuxt'],
  css: ['~/assets/css/main.css'],
  vite: { plugins: [tailwindcss()] },

  // pathPrefix: false : les composants dans des sous-dossiers (ex. components/profile/)
  // gardent leur nom de fichier tel quel (ProfileInfoForm) au lieu du prefixe par
  // dossier par defaut de Nuxt (ProfileProfileInfoForm).
  components: [{ path: '~/components', pathPrefix: false }],

  sanctum: {
    // Le module expose ses options en runtimeConfig public : surchargeable au build
    // via NUXT_PUBLIC_SANCTUM_BASE_URL (utilise par la doc de deploiement, Tache 20).
    baseUrl: 'http://localhost:8000',
    mode: 'cookie',
    // F5 : le defaut du module est false, donc un 401 (session expiree) laissait
    // l'utilisateur sur une page morte sans redirection. true renvoie vers
    // redirect.onAuthOnly (deja /login ci-dessous).
    redirectIfUnauthenticated: true,
    endpoints: {
      csrf: '/sanctum/csrf-cookie',
      login: '/login',
      logout: '/logout',
      user: '/api/user',
    },
    redirect: {
      keepRequestedRoute: true,
      onLogin: '/',
      onLogout: '/login',
      onAuthOnly: '/login',
      onGuestOnly: '/',
    },
    globalMiddleware: {
      enabled: true,
      allow404WithoutAuth: true,
    },
  },

  i18n: {
    strategy: 'no_prefix',
    defaultLocale: 'fr',
    locales: [
      { code: 'fr', file: 'fr.json' },
      { code: 'en', file: 'en.json' },
    ],
    detectBrowserLanguage: false,
  },

  pwa: {
    registerType: 'autoUpdate',
    manifest: {
      name: 'AleChemist',
      short_name: 'AleChemist',
      description: 'Suivi de brassage',
      lang: 'fr',
      dir: 'ltr',
      display: 'standalone',
      orientation: 'portrait',
      start_url: '/',
      scope: '/',
      background_color: '#12121A',
      theme_color: '#12121A',
      icons: [
        { src: '/assets/logo/alechemist-192.png', sizes: '192x192', type: 'image/png', purpose: 'any' },
        { src: '/assets/logo/alechemist@512.png', sizes: '512x512', type: 'image/png', purpose: 'any' },
        { src: '/assets/logo/alechemist@512.png', sizes: '512x512', type: 'image/png', purpose: 'maskable' },
      ],
    },
    workbox: {
      globPatterns: ['**/*.{js,css,html,png,svg,ico,ttf,woff2}'],
      navigateFallback: '/',
      navigateFallbackDenylist: [/^\/api\//, /^\/sanctum\//, /^\/user\//, /^\/logout$/],
    },
  },
})
