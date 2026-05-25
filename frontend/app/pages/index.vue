<template>
  <div>
    <!-- Hero Section -->
    <section class="hero">
      <div class="container">
        <div class="row">
          <div class="col-lg-7">
            <span class="hero-eyebrow">Sezóna karavaningu je tu</span>
            <h1>Vydajte sa na cestu,<br>s komfortom domova</h1>
            <p class="hero-lead">
              Prenájom moderných karavanov pre vaše prázdniny a výlety.
              Rezervujte si karavan ešte dnes — termíny sa rýchlo obsadzujú.
            </p>
            <div class="d-flex gap-3 flex-wrap">
              <NuxtLink :to="localePath({ name: 'services' })" class="btn btn-primary btn-lg">
                <i class="bi bi-calendar-check me-2"></i>Rezervovať karavan
              </NuxtLink>
              <NuxtLink :to="localePath({ name: 'services' })" class="btn btn-dark btn-lg">
                <i class="bi bi-house-door me-2"></i>Všetky karavany
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
          <p class="section-subtitle">Moderné a dobre vybavené karavany pre vaše dovolenky</p>
        </div>
        <div class="row g-4">
          <div v-for="service in services" :key="service.id" class="col-lg-3 col-md-6">
            <div class="card h-100 service-card p-4">
              <h5 class="card-title mb-2">{{ service.title }}</h5>
              <p class="card-text text-muted small flex-grow-1">{{ service.description }}</p>
              <div class="d-flex align-items-center justify-content-between mt-3">
                <span v-if="service.price_per_day" class="fw-bold" style="color: var(--jg-primary)">{{ service.price_per_day }} €/deň</span>
                <span v-else-if="service.price" class="fw-bold" style="color: var(--jg-primary)">od {{ service.price }} €</span>
                <span v-else></span>
                <NuxtLink :to="localePath({ name: 'services-slug', params: { slug: service.slug } })" class="btn-link-arrow">
                  Viac <i class="bi bi-arrow-right"></i>
                </NuxtLink>
              </div>
            </div>
          </div>
        </div>
        <div class="text-center mt-5">
          <NuxtLink :to="localePath({ name: 'services' })" class="btn btn-dark px-4">
            Všetky karavany <i class="bi bi-arrow-right ms-1"></i>
          </NuxtLink>
        </div>
      </div>
    </section>

    <!-- Gallery Preview -->
    <section v-if="galleryImages.length" class="section" style="background-color: var(--jg-light)">
      <div class="container">
        <div class="text-center mb-5">
          <h2 class="section-title">Z našich karavanov</h2>
          <span class="section-title-line"></span>
          <p class="section-subtitle">Pozrite sa na naše karavany</p>
        </div>
        <div class="gallery-grid gallery-grid--home">
          <div
            v-for="(image, index) in galleryImages"
            :key="image.filename"
            class="gallery-item"
            @click="openLightbox(index)"
          >
            <img :src="image.url" :alt="`CampingForYou – foto ${index + 1}`" loading="lazy" />
            <div class="gallery-item-overlay">
              <i class="bi bi-zoom-in"></i>
            </div>
          </div>
        </div>
        <div class="text-center mt-5">
          <NuxtLink :to="localePath({ name: 'gallery' })" class="btn btn-dark px-4">
            Celá galéria <i class="bi bi-arrow-right ms-1"></i>
          </NuxtLink>
        </div>
      </div>
    </section>

    <!-- Lightbox -->
    <div v-if="lightboxIndex !== null" class="lightbox" @click.self="closeLightbox">
      <button class="lightbox-close" @click="closeLightbox" aria-label="Zavrieť">
        <i class="bi bi-x-lg"></i>
      </button>
      <button class="lightbox-prev" @click="prevImage" aria-label="Predchádzajúca">
        <i class="bi bi-chevron-left"></i>
      </button>
      <div class="lightbox-inner">
        <img :src="galleryImages[lightboxIndex ?? 0]?.url" :alt="`Foto ${(lightboxIndex ?? 0) + 1}`" />
        <p class="lightbox-counter">{{ (lightboxIndex ?? 0) + 1 }} / {{ galleryImages.length }}</p>
      </div>
      <button class="lightbox-next" @click="nextImage" aria-label="Nasledujúca">
        <i class="bi bi-chevron-right"></i>
      </button>
    </div>

    <!-- CTA Phone -->
    <section class="cta-phone text-center">
      <div class="container">
        <p class="cta-label"><i class="bi bi-telephone-fill me-2"></i>Zavolajte nám</p>
        <a :href="`tel:${phone}`" class="cta-phone-number d-block">{{ phone }}</a>
        <p v-if="openingHoursSummary" class="mt-2 mb-0 opacity-75 small">{{ openingHoursSummary }}</p>
      </div>
    </section>

    <!-- Why Us -->
    <section class="section" style="background-color: var(--jg-light)">
      <div class="container">
        <div class="row align-items-center g-5">
          <div class="col-lg-5">
            <h2 class="section-title text-start">Prečo práve my?</h2>
            <span class="section-title-line start"></span>
            <p class="text-muted mb-0">
              Sme rodinná firma so skúsenosťami v prenájme karavanov.
              Zakladáme si na poctivom prístupe, čistote karavanov a spokojnosti každého zákazníka.
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
                    <h5>Moderné karavany</h5>
                    <p>Pravidelne obnovovaný vozový park s karavanmi od prémiových výrobcov.</p>
                  </div>
                </div>
                <div class="feature-item pe-4">
                  <div class="feature-icon">
                    <i class="bi bi-currency-euro"></i>
                  </div>
                  <div>
                    <h5>Férové ceny</h5>
                    <p>Transparentné ceny bez skrytých poplatkov. Vždy viete, za čo platíte.</p>
                  </div>
                </div>
              </div>
              <div class="col-sm-6">
                <div class="feature-item">
                  <div class="feature-icon">
                    <i class="bi bi-patch-check-fill"></i>
                  </div>
                  <div>
                    <h5>Plné vybavenie</h5>
                    <p>Každý karavan je vybavený kuchyňou, spálňou a všetkým potrebným na cestovanie.</p>
                  </div>
                </div>
                <div class="feature-item">
                  <div class="feature-icon">
                    <i class="bi bi-headset"></i>
                  </div>
                  <div>
                    <h5>Podpora na ceste</h5>
                    <p>V prípade potreby sme vám k dispozícii kedykoľvek počas prenájmu.</p>
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
        <h2 class="fw-bold mb-2">Plánujete dovolenku s karavanom?</h2>
        <p class="mb-4 opacity-75">Kontaktujte nás ešte dnes a dohodnite si termín prenájmu.</p>
        <NuxtLink :to="localePath({ name: 'contact' })" class="btn btn-light btn-lg px-5 fw-semibold">
          <i class="bi bi-calendar-check me-2"></i>Rezervovať
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
  description: string
  price: number | null
  price_per_day: number | null
}

interface GalleryImage {
  filename: string
  url: string
}

const api = useApi()
const lightboxIndex = ref<number | null>(null)

const [{ data: servicesRaw }, { data: settingsRaw }, { data: galleryRaw }] = await Promise.all([
  useAsyncData('home-services', () => api.get<Service[]>('/services'), { default: () => [] as Service[] }),
  useAsyncData('home-settings', () => api.get<Record<string, any>>('/settings/public'), { default: () => ({} as Record<string, any>) }),
  useAsyncData('home-gallery', () => api.get<GalleryImage[]>('/gallery'), { default: () => [] as GalleryImage[] }),
])

const services = computed(() => (servicesRaw.value || []).slice(0, 4))
const phone = computed(() => settingsRaw.value?.phone || '+421 900 123 456')
const openingHoursSummary = computed(() => {
  const hours = settingsRaw.value?.opening_hours
  if (!hours || typeof hours !== 'object') return null
  return Object.entries(hours as Record<string, string>)
    .map(([day, time]) => `${day}: ${time}`)
    .join(' · ')
})
const galleryImages = computed(() => (galleryRaw.value || []).slice(0, 8))

useSeoMeta({
  title: 'Prenájom karavanov | CampingForYou',
  description: 'Prenájom moderných karavanov pre vaše prázdniny a výlety. Kvalitné karavany za férové ceny.',
  ogTitle: 'Prenájom karavanov | CampingForYou',
  ogDescription: 'Prenájom moderných karavanov pre vaše prázdniny a výlety. Kvalitné karavany za férové ceny.',
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

const onKeyDown = (e: KeyboardEvent) => {
  if (lightboxIndex.value === null) return
  if (e.key === 'ArrowLeft') prevImage()
  else if (e.key === 'ArrowRight') nextImage()
  else if (e.key === 'Escape') closeLightbox()
}

onMounted(() => window.addEventListener('keydown', onKeyDown))
onUnmounted(() => window.removeEventListener('keydown', onKeyDown))

const openLightbox = (index: number) => {
  lightboxIndex.value = index
  document.body.style.overflow = 'hidden'
}

const closeLightbox = () => {
  lightboxIndex.value = null
  document.body.style.overflow = ''
}

const prevImage = () => {
  if (lightboxIndex.value === null) return
  lightboxIndex.value = (lightboxIndex.value - 1 + galleryImages.value.length) % galleryImages.value.length
}

const nextImage = () => {
  if (lightboxIndex.value === null) return
  lightboxIndex.value = (lightboxIndex.value + 1) % galleryImages.value.length
}
</script>
