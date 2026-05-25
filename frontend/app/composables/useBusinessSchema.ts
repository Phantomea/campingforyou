export function useBusinessSchema(settings: Record<string, any>, siteOrigin: string) {
  const schema: Record<string, any> = {
    '@context': 'https://schema.org',
    '@type': 'LocalBusiness',
    name: 'CampingForYou',
    description: 'Prenájom karavanov pre nezabudnuteľné prázdniny a výlety. Moderné a dobre vybavené karavany.',
    url: siteOrigin,
  }
  if (settings.phone) schema.telephone = settings.phone
  if (settings.email) schema.email = settings.email
  if (settings.address) {
    schema.address = {
      '@type': 'PostalAddress',
      streetAddress: settings.address,
      addressCountry: 'SK',
    }
  }
  const sameAs = [settings.facebook, settings.instagram].filter(Boolean)
  if (sameAs.length) schema.sameAs = sameAs
  return schema
}
