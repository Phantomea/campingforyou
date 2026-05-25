<template>
  <div>
    <div class="page-header">
      <div class="container">
        <h1>{{ service?.title || 'Načítavam...' }}</h1>
      </div>
    </div>

    <section class="section">
      <div class="container">
        <div v-if="service" class="row g-4">
          <div class="col-lg-8">
            <div class="card mb-4">
              <div class="card-body">
                <p class="lead text-muted mb-3">{{ service.description }}</p>

                <!-- Caravan details -->
                <div class="row g-3 mb-3">
                  <div v-if="service.manufacturer" class="col-sm-6 col-md-3">
                    <div class="text-muted small">Výrobca</div>
                    <div class="fw-semibold">{{ service.manufacturer }}</div>
                  </div>
                  <div v-if="service.capacity" class="col-sm-6 col-md-3">
                    <div class="text-muted small">Kapacita</div>
                    <div class="fw-semibold">{{ service.capacity }} osoby</div>
                  </div>
                  <div v-if="service.year" class="col-sm-6 col-md-3">
                    <div class="text-muted small">Rok výroby</div>
                    <div class="fw-semibold">{{ service.year }}</div>
                  </div>
                  <div v-if="service.price_per_day" class="col-sm-6 col-md-3">
                    <div class="text-muted small">Cena za deň</div>
                    <div class="fw-semibold h5 mb-0" style="color: var(--jg-primary)">{{ service.price_per_day }} €</div>
                  </div>
                </div>

                <p v-if="service.price && !service.price_per_day" class="h3 fw-bold mb-0" style="color: var(--jg-primary)">
                  <i class="bi bi-tag me-2"></i>od {{ service.price }} €
                </p>
              </div>
            </div>

            <!-- Rezervačný systém -->
            <div class="card">
              <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">
                  <i class="bi bi-calendar-check me-2" style="color: var(--jg-primary)"></i>
                  Rezervácia karavanu
                </h5>
              </div>
              <div class="card-body">
                <ClientOnly>
                  <BookingForm :service-id="service.id" :price-per-day="service.price_per_day" />
                </ClientOnly>
              </div>
            </div>
          </div>

          <div class="col-lg-4">
            <div class="card mb-3">
              <div class="card-body">
                <h5 class="card-title fw-bold">
                  <i class="bi bi-question-circle me-2" style="color: var(--jg-primary)"></i>
                  Potrebujete pomoc?
                </h5>
                <p class="card-text text-muted small">
                  Máte otázky k tomuto karavanu? Kontaktujte nás priamo.
                </p>
                <NuxtLink :to="localePath({ name: 'contact' })" class="btn btn-primary w-100">
                  <i class="bi bi-telephone me-2"></i>Kontaktujte nás
                </NuxtLink>
              </div>
            </div>

            <div class="card">
              <div class="card-body">
                <NuxtLink :to="localePath({ name: 'services' })" class="btn btn-dark w-100">
                  <i class="bi bi-arrow-left me-2"></i>Späť na karavany
                </NuxtLink>
              </div>
            </div>
          </div>
        </div>

        <div v-else class="text-center py-5">
          <div class="spinner-border" role="status" style="color: var(--jg-primary)">
            <span class="visually-hidden">Načítavam...</span>
          </div>
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
  license_plate: string | null
  widget_code: string | null
}

const localePath = useLocalePath()
const route = useRoute()
const api = useApi()

const { data: service, error } = await useAsyncData(
  `service-${route.params.slug}`,
  () => api.get<Service>(`/services/${route.params.slug}`)
)

if (error.value) {
  await navigateTo(localePath({ name: 'services' }))
}

const url = useRequestURL()

useSeoMeta({
  title: service.value ? `${service.value.title} | CampingForYou` : 'Karavan | CampingForYou',
  description: service.value?.description || 'Detail karavanu na prenájom od CampingForYou.',
  ogTitle: service.value ? `${service.value.title} | CampingForYou` : 'Karavan | CampingForYou',
  ogDescription: service.value?.description || 'Detail karavanu na prenájom od CampingForYou.',
  ogType: 'website',
  ogUrl: url.href,
  ogSiteName: 'CampingForYou',
})

const serviceSchema: Record<string, any> = {
  '@context': 'https://schema.org',
  '@type': 'Product',
  name: service.value?.title,
  description: service.value?.description,
  url: url.href,
  brand: {
    '@type': 'Brand',
    name: service.value?.manufacturer || 'CampingForYou',
  },
  offers: {
    '@type': 'Offer',
    price: service.value?.price_per_day || service.value?.price,
    priceCurrency: 'EUR',
    priceSpecification: {
      '@type': 'UnitPriceSpecification',
      price: service.value?.price_per_day,
      priceCurrency: 'EUR',
      unitCode: 'DAY',
    },
    availability: 'https://schema.org/InStock',
  },
}

useHead({
  link: [{ rel: 'canonical', href: url.href }],
  script: [{ type: 'application/ld+json', innerHTML: JSON.stringify(serviceSchema) }],
})
</script>
