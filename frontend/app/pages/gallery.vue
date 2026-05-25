<template>
  <div>
    <div class="page-header">
      <div class="container">
        <h1>Galéria</h1>
        <p class="page-header-sub">Pozrite sa na naše karavany</p>
      </div>
    </div>

    <section class="section bg-white">
      <div class="container">

        <div v-if="pending" class="text-center py-5">
          <div class="spinner-border text-secondary" role="status">
            <span class="visually-hidden">Načítavam...</span>
          </div>
        </div>

        <div v-else-if="images.length === 0" class="text-center py-5 text-muted">
          <i class="bi bi-images" style="font-size: 3rem; opacity: 0.3;"></i>
          <p class="mt-3">Fotografie čoskoro pribudnú.</p>
        </div>

        <div v-else class="gallery-grid">
          <div
            v-for="(image, index) in images"
            :key="image.filename"
            class="gallery-item"
            @click="openLightbox(index)"
          >
            <img :src="image.url" :alt="`Galéria CampingForYou – foto ${index + 1}`" loading="lazy" />
            <div class="gallery-item-overlay">
              <i class="bi bi-zoom-in"></i>
            </div>
          </div>
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
        <img :src="images[lightboxIndex].url" :alt="`Foto ${lightboxIndex + 1}`" />
        <p class="lightbox-counter">{{ lightboxIndex + 1 }} / {{ images.length }}</p>
      </div>
      <button class="lightbox-next" @click="nextImage" aria-label="Nasledujúca">
        <i class="bi bi-chevron-right"></i>
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
const url = useRequestURL()

interface GalleryImage {
  filename: string
  url: string
}

const api = useApi()
const lightboxIndex = ref<number | null>(null)

const { data: images, pending } = await useAsyncData(
  'gallery',
  () => api.get<GalleryImage[]>('/gallery'),
  { default: () => [] as GalleryImage[] }
)

useSeoMeta({
  title: "Galéria | CampingForYou",
  description: 'Fotogaléria karavanov CampingForYou — pozrite sa na naše karavany.',
  ogTitle: 'Galéria | CampingForYou',
  ogDescription: 'Fotografie karavanov CampingForYou.',
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
      '@type': 'ImageGallery',
      name: 'Galéria CampingForYou',
      description: 'Fotogaléria karavanov CampingForYou.',
      url: url.href,
      image: (images.value || []).map(img => img.url),
    }),
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
  lightboxIndex.value = (lightboxIndex.value - 1 + images.value.length) % images.value.length
}

const nextImage = () => {
  if (lightboxIndex.value === null) return
  lightboxIndex.value = (lightboxIndex.value + 1) % images.value.length
}
</script>
