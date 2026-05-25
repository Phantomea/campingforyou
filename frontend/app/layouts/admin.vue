<template>
  <div class="d-flex min-vh-100">
    <AdminSidebar />
    <div class="admin-content d-flex flex-column bg-light">
      <header class="bg-white border-bottom px-4 py-3">
        <div class="d-flex justify-content-between align-items-center">
          <h1 class="h5 mb-0 fw-semibold">{{ pageTitle }}</h1>
          <div class="d-flex align-items-center gap-3">
            <span class="text-muted">{{ user?.name }}</span>
            <button @click="handleLogout" class="btn btn-dark btn-sm">
              <i class="bi bi-box-arrow-right me-1"></i>
              Odhlásiť
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
    'admin-services': 'Služby',
    'admin-pricing': 'Cenník',
    'admin-pages': 'Stránky',
    'admin-settings': 'Nastavenia',
    'admin-bookings': 'Rezervácie',
    'admin-booking-blocks': 'Bloky termínov',
    'admin-super': 'Super Admin',
    'admin-super-users': 'Používatelia',
  }
  return titles[route.name as string] || 'Administrácia'
})

const handleLogout = async () => {
  await logout()
  navigateTo(localePath({ name: 'admin-login' }))
}
</script>
