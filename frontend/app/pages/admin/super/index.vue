<template>
  <div>
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-body">
        <h4 class="card-title mb-1">
          <i class="bi bi-shield-check text-primary me-2"></i>
          Super Admin Panel
        </h4>
        <p class="card-text text-muted mb-0">Tu máte prístup k rozšíreným funkciám systému.</p>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-md-4">
        <NuxtLink :to="localePath({ name: 'admin-super-users' })" class="card border-0 shadow-sm text-decoration-none h-100">
          <div class="card-body">
            <div class="d-flex align-items-center mb-3">
              <div class="bg-primary bg-opacity-10 rounded-3 p-3 me-3">
                <i class="bi bi-people text-primary fs-4"></i>
              </div>
              <div>
                <h6 class="card-title mb-0">Používatelia</h6>
                <small class="text-primary fw-semibold">{{ userCount }} používateľov</small>
              </div>
            </div>
            <p class="card-text text-muted small mb-0">
              Spravujte používateľov a ich prístupové práva
            </p>
          </div>
        </NuxtLink>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'admin',
  middleware: 'super-admin',
})

const localePath = useLocalePath()
const api = useApi()
const userCount = ref(0)

onMounted(async () => {
  try {
    const users = await api.get<any[]>('/admin/users')
    userCount.value = users.length
  } catch {
    // Handle error
  }
})
</script>
