<template>
  <div>
    <div class="row g-4 mb-5">
      <div class="col-md-4">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="bg-primary bg-opacity-10 rounded-3 p-3 me-3">
                <i class="bi bi-tools text-primary fs-4"></i>
              </div>
              <div>
                <p class="text-muted small mb-0">Služby</p>
                <h3 class="mb-0 text-primary">{{ stats.services }}</h3>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="bg-success bg-opacity-10 rounded-3 p-3 me-3">
                <i class="bi bi-currency-euro text-success fs-4"></i>
              </div>
              <div>
                <p class="text-muted small mb-0">Položky ceníku</p>
                <h3 class="mb-0 text-success">{{ stats.pricing }}</h3>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <div class="bg-info bg-opacity-10 rounded-3 p-3 me-3">
                <i class="bi bi-file-text text-info fs-4"></i>
              </div>
              <div>
                <p class="text-muted small mb-0">Stránky</p>
                <h3 class="mb-0 text-info">{{ stats.pages }}</h3>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <h5 class="mb-3">Rychlé akce</h5>
    <div class="row g-4">
      <div class="col-md-4">
        <NuxtLink :to="localePath({ name: 'admin-services' })" class="card border-0 shadow-sm text-decoration-none h-100">
          <div class="card-body">
            <h6 class="card-title mb-1">
              <i class="bi bi-tools me-2 text-primary"></i>
              Spravovat karavany
            </h6>
            <p class="card-text text-muted small mb-0">Přidat, upravit nebo odstranit karavany</p>
          </div>
        </NuxtLink>
      </div>
      <div class="col-md-4">
        <NuxtLink :to="localePath({ name: 'admin-pricing' })" class="card border-0 shadow-sm text-decoration-none h-100">
          <div class="card-body">
            <h6 class="card-title mb-1">
              <i class="bi bi-currency-euro me-2 text-primary"></i>
              Spravovat ceník
            </h6>
            <p class="card-text text-muted small mb-0">Aktualizovat ceny služeb</p>
          </div>
        </NuxtLink>
      </div>
      <div class="col-md-4">
        <NuxtLink :to="localePath({ name: 'admin-settings' })" class="card border-0 shadow-sm text-decoration-none h-100">
          <div class="card-body">
            <h6 class="card-title mb-1">
              <i class="bi bi-gear me-2 text-primary"></i>
              Nastavení
            </h6>
            <p class="card-text text-muted small mb-0">Kontaktní údaje a otevírací hodiny</p>
          </div>
        </NuxtLink>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'admin',
  middleware: 'auth',
})

const localePath = useLocalePath()
const api = useApi()
const stats = ref({
  services: 0,
  pricing: 0,
  pages: 0,
})

onMounted(async () => {
  try {
    const [services, pricing, pages] = await Promise.all([
      api.get<any[]>('/admin/services'),
      api.get<any[]>('/admin/pricing'),
      api.get<any[]>('/admin/pages'),
    ])
    stats.value = {
      services: services.length,
      pricing: pricing.length,
      pages: pages.length,
    }
  } catch {
    // Handle error
  }
})
</script>
