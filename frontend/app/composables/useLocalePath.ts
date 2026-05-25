const routeNameToPath: Record<string, string> = {
  'index':                '/',
  'services':             '/sluzby',
  'services-slug':        '/sluzby/:slug',
  'pricing':              '/cennik',
  'gallery':             '/galeria',
  'contact':              '/kontakt',
  'admin':                '/admin',
  'admin-login':          '/admin/prihlasenie',
  'admin-services':       '/admin/sluzby',
  'admin-pricing':            '/admin/cennik',
  'admin-pricing-categories': '/admin/cennik/kategorie',
  'admin-bookings':       '/admin/rezervacie',
  'admin-booking-blocks': '/admin/bloky-rezervacii',
  'admin-pages':          '/admin/stranky',
  'admin-settings':       '/admin/nastavenia',
  'admin-super':          '/admin/super',
  'admin-super-users':    '/admin/super/pouzivatelia',
}

interface LocalePathOptions {
  name: string
  params?: Record<string, string>
}

export function useLocalePath() {
  return (options: LocalePathOptions): string => {
    let path = routeNameToPath[options.name] ?? `/${options.name}`
    if (options.params) {
      for (const [key, value] of Object.entries(options.params)) {
        path = path.replace(`:${key}`, value)
      }
    }
    return path
  }
}
