<template>
  <div class="sticky-top">
    <!-- Top Info Bar -->
    <div class="top-bar">
      <div class="container">
        <div class="d-flex flex-wrap justify-content-md-between align-items-center">
          <div class="d-flex flex-wrap">
            <span v-if="openingHoursSummary" class="top-bar-item">
              <i class="bi bi-clock"></i>
              {{ openingHoursSummary }}
            </span>
            <a :href="`https://maps.google.com/?q=${encodeURIComponent(settings.address || 'Hlavná 123, Bratislava')}`" target="_blank" rel="noopener" class="top-bar-item">
              <i class="bi bi-geo-alt"></i>
              {{ settings.address || 'Hlavná 123, Bratislava' }}
            </a>
          </div>
          <div class="d-flex flex-wrap">
            <a :href="`tel:${settings.phone || '+421900123456'}`" class="top-bar-item">
              <i class="bi bi-telephone-fill"></i>
              {{ settings.phone || '+421 900 123 456' }}
            </a>
            <a :href="`mailto:${settings.email || 'info@campingforyou.sk'}`" class="top-bar-item">
              <i class="bi bi-envelope-fill"></i>
              {{ settings.email || 'info@campingforyou.sk' }}
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Navigation -->
    <nav class="main-navbar navbar navbar-expand-lg">
      <div class="container">
        <NuxtLink :to="localePath({ name: 'index' })" class="navbar-brand p-0">
          <img src="/logo.png" alt="CampingForYou" />
        </NuxtLink>

        <button
          class="navbar-toggler border-0 p-1"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#mainNav"
          aria-controls="mainNav"
          aria-expanded="false"
          aria-label="Toggle navigation"
        >
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
          <ul class="navbar-nav ms-auto align-items-lg-center">
            <li class="nav-item">
              <NuxtLink :to="localePath({ name: 'index' })" class="nav-link" exact>Úvod</NuxtLink>
            </li>
            <li class="nav-item">
              <NuxtLink :to="localePath({ name: 'services' })" class="nav-link">Karavany</NuxtLink>
            </li>
            <li class="nav-item">
              <NuxtLink :to="localePath({ name: 'pricing' })" class="nav-link">Cenník</NuxtLink>
            </li>
            <li class="nav-item">
              <NuxtLink :to="localePath({ name: 'gallery' })" class="nav-link">Galéria</NuxtLink>
            </li>
            <li class="nav-item ms-lg-3">
              <NuxtLink :to="localePath({ name: 'contact' })" class="btn btn-outline-light px-4">
                Kontakt
              </NuxtLink>
            </li>
          </ul>

        </div>
      </div>
    </nav>
  </div>
</template>

<script setup lang="ts">
const localePath = useLocalePath()
const api = useApi()
const settings = ref<Record<string, any>>({})
const openingHoursSummary = computed(() => {
  const hours = settings.value?.opening_hours
  if (!hours || typeof hours !== 'object') return null
  return Object.entries(hours as Record<string, string>)
    .map(([day, time]) => `${day}: ${time}`)
    .join(' · ')
})

onMounted(async () => {
  try {
    const data = await api.get<Record<string, any>>('/settings/public')
    settings.value = data
  } catch {
    // Use defaults
  }
})
</script>
