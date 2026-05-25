<template>
  <div>
    <div class="d-flex justify-content-end mb-4">
      <button @click="openModal()" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>
        Pridať karavan
      </button>
    </div>

    <div class="card">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th>Názov</th>
              <th>Výrobca</th>
              <th>Kapacita</th>
              <th>Rok</th>
              <th>Cena/deň</th>
              <th>Stav</th>
              <th class="text-end">Akcie</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="service in services" :key="service.id">
              <td class="fw-medium">{{ service.title }}</td>
              <td>{{ service.manufacturer || '-' }}</td>
              <td>{{ service.capacity ? `${service.capacity} os.` : '-' }}</td>
              <td>{{ service.year || '-' }}</td>
              <td>{{ service.price_per_day ? `${service.price_per_day} €` : '-' }}</td>
              <td>
                <span :class="['badge', service.is_active ? 'bg-success' : 'bg-secondary']">
                  {{ service.is_active ? 'Aktívny' : 'Neaktívny' }}
                </span>
              </td>
              <td class="text-end">
                <button @click="openModal(service)" class="btn btn-dark btn-sm me-1">
                  <i class="bi bi-pencil"></i>
                </button>
                <button @click="deleteService(service.id)" class="btn btn-outline-danger btn-sm">
                  <i class="bi bi-trash"></i>
                </button>
              </td>
            </tr>
            <tr v-if="services.length === 0">
              <td colspan="7" class="text-center text-muted py-4">
                <i class="bi bi-inbox display-6 d-block mb-2"></i>
                Žiadne karavany
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Bootstrap Modal -->
    <div class="modal fade" id="serviceModal" tabindex="-1" ref="modalRef">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ editingService ? 'Upraviť karavan' : 'Pridať karavan' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <form @submit.prevent="saveService">
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label">Názov *</label>
                <input v-model="form.title" type="text" class="form-control" required />
              </div>
              <div class="mb-3">
                <label class="form-label">Slug</label>
                <input v-model="form.slug" type="text" class="form-control" placeholder="Automaticky z názvu" />
              </div>
              <div class="mb-3">
                <label class="form-label">Popis</label>
                <textarea v-model="form.description" class="form-control" rows="3"></textarea>
              </div>

              <div class="row g-3 mb-3">
                <div class="col-sm-6">
                  <label class="form-label">Výrobca</label>
                  <input v-model="form.manufacturer" type="text" class="form-control" placeholder="napr. Hobby, Fendt" />
                </div>
                <div class="col-sm-6">
                  <label class="form-label">Kapacita (os.)</label>
                  <input v-model.number="form.capacity" type="number" min="1" max="12" class="form-control" />
                </div>
              </div>

              <div class="row g-3 mb-3">
                <div class="col-sm-6">
                  <label class="form-label">Rok výroby</label>
                  <input v-model.number="form.year" type="number" min="1990" max="2100" class="form-control" />
                </div>
                <div class="col-sm-6">
                  <label class="form-label">ŠPZ</label>
                  <input v-model="form.license_plate" type="text" class="form-control" placeholder="BA123AB" />
                </div>
              </div>

              <div class="row g-3 mb-3">
                <div class="col-sm-6">
                  <label class="form-label">Cena/deň (€)</label>
                  <input v-model.number="form.price_per_day" type="number" step="0.01" min="0" class="form-control" />
                </div>
                <div class="col-sm-6">
                  <label class="form-label">Cena od (€) <span class="text-muted small">(voliteľné)</span></label>
                  <input v-model.number="form.price" type="number" step="0.01" min="0" class="form-control" />
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label">Widget kód</label>
                <textarea v-model="form.widget_code" class="form-control" rows="2" placeholder="Voliteľný HTML kód"></textarea>
              </div>

              <div class="form-check mb-2">
                <input v-model="form.is_active" type="checkbox" class="form-check-input" id="isActive" />
                <label class="form-check-label" for="isActive">Aktívny</label>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zrušiť</button>
              <button type="submit" class="btn btn-primary" :disabled="saving">
                <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
                {{ saving ? 'Ukladám...' : 'Uložiť' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'admin',
  middleware: 'auth',
})

interface Service {
  id: number
  title: string
  slug: string
  description: string
  price: number | null
  price_per_day: number | null
  manufacturer: string | null
  capacity: number | null
  year: number | null
  license_plate: string | null
  widget_code: string | null
  is_active: boolean
  sort_order: number
}

const api = useApi()
const { $bootstrap } = useNuxtApp()
const services = ref<Service[]>([])
const modalRef = ref<HTMLElement | null>(null)
const modalInstance = ref<any>(null)
const editingService = ref<Service | null>(null)
const saving = ref(false)

const form = ref({
  title: '',
  slug: '',
  description: '',
  price: null as number | null,
  price_per_day: null as number | null,
  manufacturer: '',
  capacity: 4 as number | null,
  year: null as number | null,
  license_plate: '',
  widget_code: '',
  is_active: true,
})

const loadServices = async () => {
  services.value = await api.get<Service[]>('/admin/services')
}

const openModal = (service?: Service) => {
  if (service) {
    editingService.value = service
    form.value = {
      title: service.title,
      slug: service.slug,
      description: service.description || '',
      price: service.price,
      price_per_day: service.price_per_day,
      manufacturer: service.manufacturer || '',
      capacity: service.capacity,
      year: service.year,
      license_plate: service.license_plate || '',
      widget_code: service.widget_code || '',
      is_active: service.is_active,
    }
  } else {
    editingService.value = null
    form.value = {
      title: '',
      slug: '',
      description: '',
      price: null,
      price_per_day: null,
      manufacturer: '',
      capacity: 4,
      year: null,
      license_plate: '',
      widget_code: '',
      is_active: true,
    }
  }
  if (!modalInstance.value && modalRef.value) {
    modalInstance.value = new $bootstrap.Modal(modalRef.value)
  }
  modalInstance.value?.show()
}

const closeModal = () => {
  modalInstance.value?.hide()
  editingService.value = null
}

const saveService = async () => {
  saving.value = true
  try {
    if (editingService.value) {
      await api.put(`/admin/services/${editingService.value.id}`, form.value)
    } else {
      await api.post('/admin/services', form.value)
    }
    await loadServices()
    closeModal()
  } catch (e: any) {
    alert(e.message || 'Chyba pri ukladaní')
  } finally {
    saving.value = false
  }
}

const deleteService = async (id: number) => {
  if (!confirm('Naozaj chcete vymazať tento karavan?')) return
  try {
    await api.del(`/admin/services/${id}`)
    await loadServices()
  } catch (e: any) {
    alert(e.message || 'Chyba pri mazaní')
  }
}

onMounted(loadServices)
</script>
