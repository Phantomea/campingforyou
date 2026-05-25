import type { RouterConfig } from '@nuxt/schema'

const skPaths: Record<string, string> = {
  '/services':          '/sluzby',
  '/services/:slug()':  '/sluzby/:slug()',
  '/pricing':           '/cennik',
  '/gallery':           '/galeria',
  '/contact':           '/kontakt',
  '/admin/login':       '/admin/prihlasenie',
  '/admin/services':    '/admin/sluzby',
  '/admin/pricing':     '/admin/cennik',
  '/admin/pricing/categories': '/admin/cennik/kategorie',
  '/admin/bookings':    '/admin/rezervacie',
  '/admin/booking-blocks': '/admin/bloky-rezervacii',
  '/admin/pages':       '/admin/stranky',
  '/admin/settings':    '/admin/nastavenia',
  '/admin/super/users': '/admin/super/pouzivatelia',
}

export default <RouterConfig> {
  routes: (_routes) =>
    _routes.map((route) => ({
      ...route,
      path: skPaths[route.path] ?? route.path,
    })),
}
