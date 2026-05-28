export function useBusinessSchema(settings: Record<string, any>, siteOrigin: string) {
  const schema: Record<string, any> = {
    '@context': 'https://schema.org',
    '@type': 'LocalBusiness',
    name: 'CampingForYou',
    description: 'Pronájem karavanů pro nezapomenutelné prázdniny a výlety. Moderní a dobře vybavené karavany.',
    url: siteOrigin,
  }
  if (settings.phone) schema.telephone = settings.phone
  if (settings.email) schema.email = settings.email
  if (settings.address) {
    schema.address = {
      '@type': 'PostalAddress',
      streetAddress: settings.address,
      addressCountry: 'CZ',
    }
  }
  const sameAs = [settings.facebook, settings.instagram].filter(Boolean)
  if (sameAs.length) schema.sameAs = sameAs
  return schema
}
