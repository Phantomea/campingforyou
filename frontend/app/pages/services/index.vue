<template>
  <div>
    <div class="page-header">
      <div class="container">
        <h1>Naše karavany</h1>
      </div>
    </div>

    <section class="section">
      <div class="container">
        <SpinnerLoader v-if="pending" text="Načítávám..." />
        <template v-else>
          <div class="row g-4">
            <div v-for="service in services" :key="service.id" class="col-lg-4 col-md-6">
              <CaravanCard :service="service" />
            </div>
          </div>
          <div v-if="!services.length" class="text-center text-muted py-5">
            <i class="bi bi-inbox display-4 d-block mb-3"></i>
            Žádné karavany nejsou k dispozici.
          </div>
        </template>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
interface Service {
  id: number
  title: string
  slug: string
  description: string | null
  price: number | null
  price_formatted: string | null
  deposit: number | null
  manufacturer: string | null
  capacity: number | null
  year: number | null
  image: string | null
  images: string[] | null
}

const localePath = useLocalePath()
const api = useApi()

const { data: services, pending } = await useAsyncData(
  'services',
  () => api.get<Service[]>('/services'),
  { default: () => [] as Service[] }
)

const url = useRequestURL()

useSeoMeta({
  title: 'Karavany | CampingForYou',
  description: 'Seznam karavanů dostupných k pronájmu v CampingForYou. Moderní karavany pro vaše prázdniny.',
  ogTitle: 'Karavany | CampingForYou',
  ogDescription: 'Moderní karavany k pronájmu pro vaše prázdniny a výlety.',
  ogType: 'website',
  ogUrl: url.href,
  ogSiteName: 'CampingForYou',
})

useHead({
  link: [{ rel: 'canonical', href: url.href }],
  script: [{
    type: 'application/ld+json',
    innerHTML: JSON.stringify({
      '@context': 'https://schema.org',
      '@type': 'ItemList',
      name: 'Karavany CampingForYou',
      url: url.href,
      itemListElement: (services.value || []).map((s, i) => ({
        '@type': 'ListItem',
        position: i + 1,
        name: s.title,
        description: s.description,
        url: `${url.origin}/karavany/${s.slug}`,
      })),
    }),
  }],
})
</script>
