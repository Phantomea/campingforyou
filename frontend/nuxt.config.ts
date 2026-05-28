// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  ssr: true,
  devtools: { enabled: true },

  routeRules: {
    '/admin/**': { ssr: false },
  },

  devServer: {
    port: 9001,
  },

  vite: {
    server: {
      allowedHosts: ['frontend.campingforyou.ddev.site'],
    },
  },

  runtimeConfig: {
    apiBaseServer: process.env.NUXT_API_BASE_SERVER || 'http://localhost:8000/api',
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8000/api',
      backendUrl: process.env.NUXT_PUBLIC_BACKEND_URL || 'http://localhost:8000',
    }
  },

  app: {
    head: {
      title: "CampingForYou",
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
        { name: 'description', content: 'Prenájom karavanov pre nezabudnuteľné prázdniny' }
      ],
      link: [
        { rel: 'preconnect', href: 'https://fonts.googleapis.com' },
        { rel: 'preconnect', href: 'https://fonts.gstatic.com', crossorigin: '' },
        { rel: 'stylesheet', href: 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap' },
        { rel: 'icon', type: 'image/x-icon', href: '/favicon.ico' },
        { rel: 'icon', type: 'image/png', sizes: '16x16', href: '/favicon-16x16.png' },
        { rel: 'icon', type: 'image/png', sizes: '32x32', href: '/favicon-32x32.png' },
        { rel: 'apple-touch-icon', sizes: '180x180', href: '/apple-touch-icon.png' },
        { rel: 'manifest', href: '/site.webmanifest' },
      ],
      meta: [
        { name: 'theme-color', content: '#8B1A1A' },
      ]
    }
  },

  css: [
    '@/assets/scss/bootstrap.scss',
    'bootstrap-icons/font/bootstrap-icons.css',
    '@/assets/css/main.css',
  ],
})
