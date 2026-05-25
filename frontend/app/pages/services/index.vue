<template>
  <div>
    <div class="page-header">
      <div class="container">
        <h1>Naše karavany</h1>
      </div>
    </div>

    <section class="section">
      <div class="container">
        <div class="row g-4">
          <div v-for="service in services" :key="service.id" class="col-lg-4 col-md-6">
            <div class="card h-100 service-card">
              <div class="card-body d-flex flex-column">
                <h5 class="card-title">{{ service.title }}</h5>
                <p class="card-text text-muted flex-grow-1">{{ service.description }}</p>
                <div class="d-flex gap-3 small text-muted mb-3">
                  <span v-if="service.manufacturer"><i class="bi bi-tag me-1"></i>{{ service.manufacturer }}</span>
                  <span v-if="service.capacity"><i class="bi bi-people me-1"></i>{{ service.capacity }} os.</span>
                  <span v-if="service.year"><i class="bi bi-calendar me-1"></i>{{ service.year }}</span>
                </div>
                <p v-if="service.price_per_day" class="text-primary fw-semibold mb-3">{{ service.price_per_day }} €/deň</p>
                <p v-else-if="service.price" class="text-primary fw-semibold mb-3">od {{ service.price }} €</p>
                <NuxtLink :to="localePath({ name: 'services-slug', params: { slug: service.slug } })" class="btn btn-primary">
                  Rezervovať
                  <i class="bi bi-arrow-right ms-1"></i>
                </NuxtLink>
              </div>
            </div>
          </div>
        </div>

        <div v-if="services.length === 0" class="text-center text-muted py-5">
          <i class="bi bi-inbox display-4 d-block mb-3"></i>
          Žiadne karavany nie sú k dispozícii.
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
interface Service {
  id: number
  title: string
  slug: string
  description: string
  price: number | null
  price_per_day: number | null
  manufacturer: string | null
  capacity: number | null
  year: number | null
}

const localePath = useLocalePath()
const api = useApi()

const { data: services } = await useAsyncData(
  'services',
  () => api.get<Service[]>('/services'),
  { default: () => [] as Service[] }
)

const url = useRequestURL()

useSeoMeta({
  title: 'Karavany | CampingForYou',
  description: 'Zoznam karavanov dostupných na prenájom v CampingForYou. Moderné karavany pre vaše prázdniny.',
  ogTitle: 'Karavany | CampingForYou',
  ogDescription: 'Moderné karavany na prenájom pre vaše prázdniny a výlety.',
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
        url: `${url.origin}/sluzby/${s.slug}`,
      })),
    }),
  }],
})
</script>
