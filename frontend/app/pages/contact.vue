<template>
  <div>
    <div class="page-header">
      <div class="container">
        <h1>Kontakt</h1>
      </div>
    </div>

    <section class="section">
      <div class="container">
        <div class="row g-5">
          <div class="col-lg-6">
            <h2 class="h4 mb-4">Kontaktní údaje</h2>

            <div class="contact-item">
              <i class="bi bi-telephone"></i>
              <div>
                <small class="text-muted d-block">Telefon</small>
                <a :href="`tel:${settings.phone}`" class="fs-5 text-decoration-none">
                  {{ settings.phone || '+420 900 123 456' }}
                </a>
              </div>
            </div>

            <div class="contact-item">
              <i class="bi bi-envelope"></i>
              <div>
                <small class="text-muted d-block">Email</small>
                <a :href="`mailto:${settings.email}`" class="fs-5 text-decoration-none">
                  {{ settings.email || 'info@campingforyou.cz' }}
                </a>
              </div>
            </div>

            <div class="contact-item">
              <i class="bi bi-geo-alt"></i>
              <div>
                <small class="text-muted d-block">Adresa</small>
                <a :href="`https://maps.google.com/?q=${encodeURIComponent(settings.address || 'Hlavní 1, 110 00 Praha')}`" target="_blank" rel="noopener" class="fs-5 text-decoration-none">
                  {{ settings.address || 'Hlavní 1, 110 00 Praha' }}
                </a>
              </div>
            </div>

            <h3 class="h5 mt-5 mb-3">Otevírací hodiny</h3>
            <div v-if="openingHours" class="card bg-light border-0">
              <div class="card-body">
                <dl class="opening-hours mb-0">
                  <template v-for="(hours, day) in openingHours" :key="day">
                    <div class="d-flex justify-content-between py-2 border-bottom">
                      <dt class="fw-normal">{{ day }}</dt>
                      <dd class="mb-0 fw-medium">{{ hours }}</dd>
                    </div>
                  </template>
                </dl>
              </div>
            </div>

            <div v-if="settings.facebook || settings.instagram" class="mt-5">
              <h3 class="h5 mb-3">Sledujte nás</h3>
              <div class="d-flex gap-2">
                <a
                  v-if="settings.facebook"
                  :href="settings.facebook"
                  target="_blank"
                  rel="noopener"
                  class="btn btn-dark"
                >
                  <i class="bi bi-facebook me-1"></i>
                  Facebook
                </a>
                <a
                  v-if="settings.instagram"
                  :href="settings.instagram"
                  target="_blank"
                  rel="noopener"
                  class="btn btn-dark"
                >
                  <i class="bi bi-instagram me-1"></i>
                  Instagram
                </a>
              </div>
            </div>
          </div>

          <div class="col-lg-6">
            <a :href="`https://maps.google.com/?q=${encodeURIComponent(settings.address || 'Hlavní 1, Praha')}`" target="_blank" rel="noopener" class="bg-secondary bg-opacity-10 rounded-3 d-flex align-items-center justify-content-center text-decoration-none" style="height: 400px;">
              <div class="text-center text-muted">
                <i class="bi bi-map display-4 d-block mb-2"></i>
                <p class="mb-0">Zobrazit na mapě</p>
                <small>{{ settings.address || 'Hlavní 1, Praha' }}</small>
              </div>
            </a>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
const api = useApi()

const { data: settingsData } = await useAsyncData(
  'settings-public',
  () => api.get<Record<string, any>>('/settings/public'),
  { default: () => ({} as Record<string, any>) }
)

const settings = computed(() => settingsData.value || {})
const openingHours = computed(() => settingsData.value?.opening_hours || null)

const url = useRequestURL()

useSeoMeta({
  title: "Kontakt | CampingForYou",
  description: 'Kontaktujte CampingForYou. Najdete nás na adrese, zavolejte nám nebo pošlete e-mail.',
  ogTitle: 'Kontakt | CampingForYou',
  ogDescription: 'Kontaktujte CampingForYou - pronájem karavanů. Telefon, adresa a otevírací hodiny.',
  ogType: 'website',
  ogUrl: url.href,
  ogSiteName: "CampingForYou",
})

useHead({
  link: [{ rel: 'canonical', href: url.href }],
  script: [{
    type: 'application/ld+json',
    innerHTML: JSON.stringify(useBusinessSchema(settingsData.value || {}, url.origin)),
  }],
})
</script>
