<template>
  <div>
    <form @submit.prevent="saveSettings" class="card">
      <div class="card-body">
        <div v-if="saved" class="alert alert-success">
          <i class="bi bi-check-circle me-2"></i>
          Nastavenia boli uložené.
        </div>

        <h5 class="mb-3">
          <i class="bi bi-info-circle me-2 text-primary"></i>
          Základné informácie
        </h5>
        <div class="mb-3">
          <label class="form-label">Názov webu</label>
          <input v-model="form.site_name" type="text" class="form-control" />
        </div>
        <div class="mb-4">
          <label class="form-label">Popis webu</label>
          <textarea v-model="form.site_description" class="form-control" rows="2"></textarea>
        </div>

        <h5 class="mb-3 mt-4">
          <i class="bi bi-telephone me-2 text-primary"></i>
          Kontaktné údaje
        </h5>
        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label">Telefón</label>
            <input v-model="form.phone" type="text" class="form-control" />
          </div>
          <div class="col-md-6">
            <label class="form-label">Email</label>
            <input v-model="form.email" type="email" class="form-control" />
          </div>
        </div>
        <div class="mb-4">
          <label class="form-label">Adresa</label>
          <textarea v-model="form.address" class="form-control" rows="2"></textarea>
        </div>

        <h5 class="mb-3 mt-4">
          <i class="bi bi-clock me-2 text-primary"></i>
          Otváracie hodiny
        </h5>
        <div v-for="(hours, day) in form.opening_hours" :key="day" class="row mb-2 align-items-center">
          <div class="col-3">
            <label class="form-label mb-0">{{ day }}</label>
          </div>
          <div class="col-9">
            <input v-model="form.opening_hours[day]" type="text" class="form-control" placeholder="napr. 8:00 - 17:00" />
          </div>
        </div>

        <h5 class="mb-3 mt-4">
          <i class="bi bi-share me-2 text-primary"></i>
          Sociálne siete
        </h5>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">
              <i class="bi bi-facebook me-1"></i>
              Facebook URL
            </label>
            <input v-model="form.facebook" type="url" class="form-control" placeholder="https://facebook.com/..." />
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">
              <i class="bi bi-instagram me-1"></i>
              Instagram URL
            </label>
            <input v-model="form.instagram" type="url" class="form-control" placeholder="https://instagram.com/..." />
          </div>
        </div>
      </div>
      <div class="card-footer bg-light">
        <button type="submit" class="btn btn-primary" :disabled="saving">
          <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
          <i v-else class="bi bi-check-lg me-1"></i>
          {{ saving ? 'Ukladám...' : 'Uložiť nastavenia' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'admin',
  middleware: 'auth',
})

const api = useApi()
const saving = ref(false)
const saved = ref(false)

const form = ref({
  site_name: '',
  site_description: '',
  phone: '',
  email: '',
  address: '',
  opening_hours: {
    'Po - Pi': '',
    'So': '',
    'Ne': '',
  } as Record<string, string>,
  facebook: '',
  instagram: '',
})

const loadSettings = async () => {
  try {
    const data = await api.get<Record<string, any>>('/admin/settings')
    form.value = {
      site_name: data.site_name || '',
      site_description: data.site_description || '',
      phone: data.phone || '',
      email: data.email || '',
      address: data.address || '',
      opening_hours: data.opening_hours || {
        'Po - Pi': '8:00 - 17:00',
        'So': '9:00 - 12:00',
        'Ne': 'Zatvorené',
      },
      facebook: data.facebook || '',
      instagram: data.instagram || '',
    }
  } catch {
    // Use defaults
  }
}

const saveSettings = async () => {
  saving.value = true
  saved.value = false
  try {
    await api.put('/admin/settings', form.value)
    saved.value = true
    setTimeout(() => { saved.value = false }, 3000)
  } catch (e: any) {
    alert(e.message || 'Chyba pri ukladaní')
  } finally {
    saving.value = false
  }
}

onMounted(loadSettings)
</script>
