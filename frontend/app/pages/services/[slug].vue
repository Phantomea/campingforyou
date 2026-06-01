<template>
  <div>
    <div class="page-header">
      <div class="container">
        <h1>{{ service?.title || 'Načítání...' }}</h1>
      </div>
    </div>

    <section class="section">
      <div class="container">
        <div v-if="service" class="row g-4">
          <div class="col-lg-8">
            <div class="card mb-4">
              <div class="card-body">
                <div v-if="service.description" class="service-description mb-3" v-html="service.description"></div>

                <!-- Caravan details -->
                <div class="row g-3 mb-3">
                  <div v-if="service.manufacturer" class="col-sm-6 col-md-3">
                    <div class="text-muted small">Výrobce</div>
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
                  <div v-if="service.price_formatted" class="col-sm-6 col-md-3">
                    <div class="text-muted small">Cena za den</div>
                    <div class="fw-semibold h5 mb-0" style="color: var(--bs-primary)">{{ service.price_formatted }} Kč</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Booking form -->
            <div class="card">
              <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">
                  <i class="bi bi-calendar-check me-2" style="color: var(--bs-primary)"></i>
                  Rezervace karavanu
                </h5>
              </div>
              <div class="card-body">
                <ClientOnly>
                  <BookingForm :service-id="service.id" :deposit="service.deposit" />
                </ClientOnly>
              </div>
            </div>
          </div>

          <div class="col-lg-4">
            <div class="card mb-3">
              <div class="card-body">
                <h5 class="card-title fw-bold">
                  <i class="bi bi-question-circle me-2" style="color: var(--bs-primary)"></i>
                  Potřebujete pomoc?
                </h5>
                <p class="card-text text-muted small">
                  Máte otázky k tomuto karavanu? Kontaktujte nás přímo.
                </p>
                <NuxtLink :to="localePath({ name: 'contact' })" class="btn btn-primary w-100">
                  <i class="bi bi-telephone me-2"></i>Kontaktujte nás
                </NuxtLink>
              </div>
            </div>

            <div class="card">
              <div class="card-body">
                <NuxtLink :to="localePath({ name: 'services' })" class="btn btn-dark w-100">
                  <i class="bi bi-arrow-left me-2"></i>Zpět na karavany
                </NuxtLink>
              </div>
            </div>
          </div>
        </div>

        <div v-else class="text-center py-5">
          <div class="spinner-border" role="status" style="color: var(--bs-primary)">
            <span class="visually-hidden">Načítání...</span>
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
  price_formatted: string | null
  deposit: number | null
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
  description: service.value?.description || 'Detail karavanu k pronájmu od CampingForYou.',
  ogTitle: service.value ? `${service.value.title} | CampingForYou` : 'Karavan | CampingForYou',
  ogDescription: service.value?.description || 'Detail karavanu k pronájmu od CampingForYou.',
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
    price: service.value?.price,
    priceCurrency: 'CZK',
    priceSpecification: {
      '@type': 'UnitPriceSpecification',
      price: service.value?.price,
      priceCurrency: 'CZK',
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

<style scoped>
.service-description :deep(p) { margin-bottom: 0.5rem; }
.service-description :deep(ul),
.service-description :deep(ol) { padding-left: 1.25rem; margin-bottom: 0.5rem; }
.service-description :deep(a) { color: var(--bs-primary); }
</style>
