<template>
  <div>
    <div class="page-header">
      <div class="container">
        <h1>Ceník</h1>
      </div>
    </div>

    <section class="section">
      <div class="container">
        <div class="alert alert-info mb-4">
          <i class="bi bi-info-circle me-2"></i>
          Uvedené ceny jsou orientační. Cena pronájmu závisí na zvoleném karavanu a délce pobytu.
          Pro přesnou cenovou nabídku nás kontaktujte.
        </div>

        <div v-for="(items, category) in pricing" :key="category" class="pricing-category">
          <h3 class="mb-3">{{ category }}</h3>
          <div class="card mb-4">
            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Služba</th>
                    <th>Popis</th>
                    <th class="text-end">Cena</th>

                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in items" :key="item.id">
                    <td class="fw-medium">{{ item.name }}</td>
                    <td class="text-muted">{{ item.description || '-' }}</td>
                    <td class="text-end fw-semibold text-nowrap">
                      <template v-if="item.price_from && item.price_to">
                        {{ item.price_from }} - {{ item.price_to }} Kč
                      </template>
                      <template v-else-if="item.price_from">
                        od {{ item.price_from }} Kč
                      </template>
                      <template v-else>
                        Na dotaz
                      </template>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div v-if="Object.keys(pricing).length === 0" class="text-center text-muted py-5">
          <i class="bi bi-inbox display-4 d-block mb-3"></i>
          Ceník není k dispozici.
        </div>

        <div class="card bg-light border-0 mt-5">
          <div class="card-body text-center" style="padding-top: 4rem; padding-bottom: 4rem;">
            <h4 class="mb-2">Potřebujete přesnější cenovou nabídku?</h4>
            <p class="text-muted mb-3">Kontaktujte nás s termínem a počtem dní pronájmu.</p>
            <NuxtLink :to="localePath({ name: 'contact' })" class="btn btn-primary btn-lg mt-15px">
              <i class="bi bi-envelope me-2"></i>
              Kontaktujte nás
            </NuxtLink>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
interface PricingItem {
  id: number
  name: string
  price_from: number | null
  price_to: number | null
  description: string | null
  category: string
}

const localePath = useLocalePath()
const api = useApi()

const { data: pricing } = await useAsyncData(
  'pricing',
  () => api.get<Record<string, PricingItem[]>>('/pricing'),
  { default: () => ({} as Record<string, PricingItem[]>) }
)

const url = useRequestURL()

useSeoMeta({
  title: "Ceník | CampingForYou",
  description: 'Ceník pronájmu karavanů CampingForYou. Orientační ceny za den pronájmu a doplňkové služby.',
  ogTitle: 'Ceník | CampingForYou',
  ogDescription: 'Orientační ceny pronájmu karavanů a doplňkových služeb CampingForYou.',
  ogType: 'website',
  ogUrl: url.href,
  ogSiteName: "CampingForYou",
})

useHead({
  link: [{ rel: 'canonical', href: url.href }],
  script: [{
    type: 'application/ld+json',
    innerHTML: JSON.stringify({
      '@context': 'https://schema.org',
      '@type': 'WebPage',
      name: 'Cenník | CampingForYou',
      description: 'Ceník pronájmu karavanů.',
      url: url.href,
      breadcrumb: {
        '@type': 'BreadcrumbList',
        itemListElement: [
          { '@type': 'ListItem', position: 1, name: 'Domov', item: url.origin },
          { '@type': 'ListItem', position: 2, name: 'Cenník', item: url.href },
        ],
      },
    }),
  }],
})
</script>
