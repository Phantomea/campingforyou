<template>
  <div class="min-vh-100 d-flex align-items-center justify-content-center bg-light p-3">
    <div class="card shadow-sm" style="max-width: 400px; width: 100%;">
      <div class="card-body p-4">
        <h1 class="h4 mb-1">Prihlásenie</h1>
        <p class="text-muted mb-4">Zadajte svoje prihlasovacie údaje</p>

        <form @submit.prevent="handleLogin">
          <div v-if="error" class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i>
            {{ error }}
          </div>

          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input
              id="email"
              v-model="email"
              type="email"
              class="form-control"
              required
              autocomplete="email"
            />
          </div>

          <div class="mb-3">
            <label for="password" class="form-label">Heslo</label>
            <input
              id="password"
              v-model="password"
              type="password"
              class="form-control"
              required
              autocomplete="current-password"
            />
          </div>

          <button type="submit" class="btn btn-primary w-100" :disabled="loading">
            <span v-if="loading" class="spinner-border spinner-border-sm me-2" role="status"></span>
            {{ loading ? 'Prihlasujem...' : 'Prihlásiť sa' }}
          </button>
        </form>

        <div class="mt-4 text-center">
          <NuxtLink :to="localePath({ name: 'index' })" class="text-muted text-decoration-none">
            <i class="bi bi-arrow-left me-1"></i>
            Späť na web
          </NuxtLink>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: false,
  middleware: 'guest',
})

const localePath = useLocalePath()
const { login } = useAuth()

const email = ref('')
const password = ref('')
const error = ref('')
const loading = ref(false)

const handleLogin = async () => {
  error.value = ''
  loading.value = true

  try {
    await login(email.value, password.value)
    navigateTo(localePath({ name: 'admin' }))
  } catch (e: any) {
    error.value = e.message || 'Prihlásenie zlyhalo'
  } finally {
    loading.value = false
  }
}

useHead({
  title: "Prihlásenie | John's Garage Admin",
})
</script>
