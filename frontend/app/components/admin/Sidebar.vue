<template>
  <aside class="admin-sidebar d-flex flex-column">
    <div class="p-3 border-bottom border-secondary d-flex align-items-center gap-2">
      <NuxtLink :to="localePath({ name: 'index' })" class="text-white text-decoration-none fw-bold">
        John's Garage
      </NuxtLink>
      <span class="badge bg-primary small">Admin</span>
    </div>
    <nav class="nav flex-column py-3 flex-grow-1">
      <NuxtLink :to="localePath({ name: 'admin' })" class="nav-link" :class="{ active: route.name === 'admin' }">
        <i class="bi bi-speedometer2 me-2"></i>
        Dashboard
      </NuxtLink>
      <NuxtLink :to="localePath({ name: 'admin-services' })" class="nav-link" :class="{ active: route.name === 'admin-services' }">
        <i class="bi bi-tools me-2"></i>
        Služby
      </NuxtLink>
      <button
        class="nav-link d-flex justify-content-between align-items-center w-100 border-0 bg-transparent text-start"
        :class="{ active: isPricingActive }"
        @click="pricingOpen = !pricingOpen"
      >
        <span><i class="bi bi-currency-euro me-2"></i>Cenníky</span>
        <i :class="pricingOpen ? 'bi bi-chevron-up' : 'bi bi-chevron-down'" class="small"></i>
      </button>
      <div v-show="pricingOpen" class="ms-3">
        <NuxtLink :to="localePath({ name: 'admin-pricing' })" class="nav-link py-1" :class="{ active: route.name === 'admin-pricing' }">
          <i class="bi bi-list-ul me-2"></i>Prehľad
        </NuxtLink>
        <NuxtLink :to="localePath({ name: 'admin-pricing-categories' })" class="nav-link py-1" :class="{ active: route.name === 'admin-pricing-categories' }">
          <i class="bi bi-tags me-2"></i>Kategórie
        </NuxtLink>
      </div>
      <NuxtLink :to="localePath({ name: 'admin-bookings' })" class="nav-link" :class="{ active: route.name === 'admin-bookings' }">
        <i class="bi bi-calendar-check me-2"></i>
        Rezervácie
      </NuxtLink>
      <NuxtLink :to="localePath({ name: 'admin-booking-blocks' })" class="nav-link" :class="{ active: route.name === 'admin-booking-blocks' }">
        <i class="bi bi-slash-circle me-2"></i>
        Bloky termínov
      </NuxtLink>
      <!-- <NuxtLink :to="localePath({ name: 'admin-pages' })" class="nav-link" :class="{ active: route.name === 'admin-pages' }">
        <i class="bi bi-file-text me-2"></i>
        Stránky
      </NuxtLink> -->
      <NuxtLink :to="localePath({ name: 'admin-settings' })" class="nav-link" :class="{ active: route.name === 'admin-settings' }">
        <i class="bi bi-gear me-2"></i>
        Nastavenia
      </NuxtLink>

      <div v-if="isSuperAdmin" class="mt-3 pt-3 border-top border-secondary">
        <span class="px-3 text-uppercase small text-secondary fw-semibold">Super Admin</span>
        <NuxtLink :to="localePath({ name: 'admin-super' })" class="nav-link mt-2" :class="{ active: route.name === 'admin-super' }">
          <i class="bi bi-shield-check me-2"></i>
          Prehľad
        </NuxtLink>
        <NuxtLink :to="localePath({ name: 'admin-super-users' })" class="nav-link" :class="{ active: route.name === 'admin-super-users' }">
          <i class="bi bi-people me-2"></i>
          Používatelia
        </NuxtLink>
      </div>
    </nav>
    <div class="py-3 border-top border-secondary">
      <NuxtLink :to="localePath({ name: 'index' })" class="nav-link">
        <i class="bi bi-eye me-2"></i>
        Zobraziť web
      </NuxtLink>
    </div>
  </aside>
</template>

<script setup lang="ts">
const localePath = useLocalePath()
const route = useRoute()
const { isSuperAdmin } = useAuth()

const isPricingActive = computed(() =>
  typeof route.name === 'string' && route.name.startsWith('admin-pricing')
)
const pricingOpen = ref(isPricingActive.value)
</script>
