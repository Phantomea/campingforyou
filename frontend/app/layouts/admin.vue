<template>
  <div class="admin-wrapper d-flex">
    <AdminSidebar />
    <div class="admin-content d-flex flex-column bg-light">
      <header class="bg-white border-bottom px-4 py-3">
        <div class="d-flex justify-content-between align-items-center">
          <h1 class="h5 mb-0 fw-semibold">{{ pageTitle }}</h1>
          <div class="d-flex align-items-center gap-3">
            <span class="text-muted">{{ user?.name }}</span>
            <button @click="handleLogout" class="btn btn-dark btn-sm">
              <i class="bi bi-box-arrow-right me-1"></i>
              Odhlásit
            </button>
          </div>
        </div>
      </header>
      <main class="flex-grow-1 p-4">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
const localePath = useLocalePath()
const route = useRoute()
const { user, logout } = useAuth()

const pageTitle = computed(() => {
  const titles: Record<string, string> = {
    'admin': 'Dashboard',
    'admin-services': 'Karavany',
    'admin-pricing': 'Ceník',
    'admin-pages': 'Stránky',
    'admin-settings': 'Nastavení',
    'admin-bookings': 'Rezervace',
    'admin-booking-blocks': 'Blokovaná data',
    'admin-super': 'Super Admin',
    'admin-super-users': 'Uživatelé',
  }
  return titles[route.name as string] || 'Administrace'
})

const handleLogout = async () => {
  await logout()
  navigateTo(localePath({ name: 'admin-login' }))
}
</script>
