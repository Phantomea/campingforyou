<template>
  <div>
    <!-- Hero Section -->
    <section class="hero">
      <div class="container">
        <div class="row">
          <div class="col-xl-8 offset-xl-2 text-center">
            <h1>Vydejte se na cestu,<br>s komfortem domova</h1>

            <p class="perex mt-30px">
              Pronájem moderních karavanů pro vaše prázdniny a výlety.
              Rezervujte si karavan ještě dnes — termíny se rychle obsazují.
            </p>

            <div class="d-flex flex-wrap justify-content-center gap-10px mt-50px">
              <NuxtLink :to="localePath({ name: 'services' })" class="btn btn-primary btn-lg">
                <i class="bi bi-calendar-check me-2"></i>Rezervovat karavan
              </NuxtLink>
              <NuxtLink :to="localePath({ name: 'services' })" class="btn btn-dark btn-lg">
                <i class="bi bi-house-door me-2"></i>Všechny karavany
              </NuxtLink>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Caravans Preview -->
    <section class="section bg-white">
      <div class="container">
        <div class="text-center mb-5">
          <h2 class="section-title">Naše karavany</h2>
          <span class="section-title-line"></span>
          <p class="section-subtitle">Moderní a dobře vybavené karavany pro vaše dovolené</p>
        </div>
        <ClientOnly>
          <SpinnerLoader v-if="loading" text="Načítávám..." />
          <div v-else class="d-flex flex-column flex-xl-row flex-wrap justify-content-center gap-20px">
            <div v-for="service in services" :key="service.id" class="w-100 maxw-xl-500px">
              <CaravanCard :service="service" />
            </div>
          </div>
          <template #fallback>
            <SpinnerLoader text="Načítávám..." />
          </template>
        </ClientOnly>
        <div class="text-center mt-5">
          <NuxtLink :to="localePath({ name: 'services' })" class="btn btn-dark px-4">
            Všechny karavany <i class="bi bi-arrow-right ms-1"></i>
          </NuxtLink>
        </div>
      </div>
    </section>

    <!-- CTA Phone -->
    <section class="cta-phone text-center">
      <div class="container">
        <p class="cta-label"><i class="bi bi-telephone-fill me-2"></i>Zavolejte nám</p>
        <a :href="`tel:${phone}`" class="cta-phone-number d-block">{{ phone }}</a>
        <p v-if="openingHoursSummary" class="mt-2 mb-0 opacity-75 small">{{ openingHoursSummary }}</p>
      </div>
    </section>

    <!-- Why Us -->
    <section class="section" style="background-color: var(--jg-light)">
      <div class="container">
        <div class="row align-items-center g-5">
          <div class="col-lg-5">
            <h2 class="section-title text-start">Proč právě my?</h2>
            <span class="section-title-line start"></span>
            <p class="text-muted mb-0">
              Jsme rodinná firma se zkušenostmi v pronájmu karavanů.
              Zakládáme si na poctivém přístupu, čistotě karavanů a spokojenosti každého zákazníka.
            </p>
          </div>
          <div class="col-lg-7">
            <div class="row g-0">
              <div class="col-sm-6">
                <div class="feature-item pe-4">
                  <div class="feature-icon">
                    <i class="bi bi-award-fill"></i>
                  </div>
                  <div>
                    <h5>Moderní karavany</h5>
                    <p>Pravidelně obnovovaný vozový park s karavany od prémiových výrobců.</p>
                  </div>
                </div>
                <div class="feature-item pe-4">
                  <div class="feature-icon">
                    <i class="bi bi-coin"></i>
                  </div>
                  <div>
                    <h5>Férové ceny</h5>
                    <p>Transparentní ceny bez skrytých poplatků. Vždy víte, za co platíte.</p>
                  </div>
                </div>
              </div>
              <div class="col-sm-6">
                <div class="feature-item">
                  <div class="feature-icon">
                    <i class="bi bi-patch-check-fill"></i>
                  </div>
                  <div>
                    <h5>Plné vybavení</h5>
                    <p>Každý karavan je vybaven kuchyní, ložnicí a vším potřebným na cestování.</p>
                  </div>
                </div>
                <div class="feature-item">
                  <div class="feature-icon">
                    <i class="bi bi-headset"></i>
                  </div>
                  <div>
                    <h5>Podpora na cestě</h5>
                    <p>V případě potřeby jsme vám k dispozici kdykoliv během pronájmu.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Banner -->
    <section class="cta-banner text-white">
      <div class="container text-center">
        <h2 class="fw-bold mb-2">Plánujete dovolenou s karavanem?</h2>
        <p class="mb-4 opacity-75">Kontaktujte nás ještě dnes a dohodněte si termín pronájmu.</p>
        <NuxtLink :to="localePath({ name: 'contact' })" class="btn btn-light btn-lg px-5 fw-semibold">
          <i class="bi bi-calendar-check me-2"></i>Rezervovat
        </NuxtLink>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
const localePath = useLocalePath()
const url = useRequestURL()

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

const api = useApi()

const { data: settingsRaw } = await useAsyncData('home-settings', () => api.get<Record<string, any>>('/settings/public'), { default: () => ({} as Record<string, any>) })

const services = ref<Service[]>([])
const loading = ref(true)
onMounted(async () => {
  try {
    services.value = (await api.get<Service[]>('/services')).slice(0, 4)
  } finally {
    loading.value = false
  }
})
const phone = computed(() => settingsRaw.value?.phone || '+420 900 123 456')
const openingHoursSummary = computed(() => {
  const hours = settingsRaw.value?.opening_hours
  if (!hours || typeof hours !== 'object') return null
  return Object.entries(hours as Record<string, string>)
    .map(([day, time]) => `${day}: ${time}`)
    .join(' · ')
})

useSeoMeta({
  title: 'Pronájem karavanů | CampingForYou',
  description: 'Pronájem moderních karavanů pro vaše prázdniny a výlety. Kvalitní karavany za férové ceny.',
  ogTitle: 'Pronájem karavanů | CampingForYou',
  ogDescription: 'Pronájem moderních karavanů pro vaše prázdniny a výlety. Kvalitní karavany za férové ceny.',
  ogType: 'website',
  ogUrl: url.href,
  ogSiteName: 'CampingForYou',
})

useHead({
  link: [{ rel: 'canonical', href: url.href }],
  script: [{
    type: 'application/ld+json',
    innerHTML: JSON.stringify(useBusinessSchema(settingsRaw.value || {}, url.origin)),
  }],
})
</script>
