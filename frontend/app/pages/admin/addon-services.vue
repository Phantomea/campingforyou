<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="h4 mb-0">Doplnkové služby</h1>
      <button class="btn btn-primary btn-sm" @click="openCreate">
        <i class="bi bi-plus me-1"></i>Pridať službu
      </button>
    </div>

    <div v-if="loading" class="text-center py-5 text-muted">
      <div class="spinner-border spinner-border-sm me-2"></div>Načítava sa...
    </div>

    <div v-else-if="services.length === 0" class="text-center py-5 text-muted">
      Žiadne doplnkové služby. Pridajte prvú.
    </div>

    <div v-else class="table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>Názov</th>
            <th>Popis</th>
            <th>Cena</th>
            <th>Premium</th>
            <th>Aktívna</th>
            <th>Poradie</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="s in services" :key="s.id">
            <td class="fw-semibold">{{ s.name }}</td>
            <td class="text-muted small" style="max-width:280px">{{ s.description || '—' }}</td>
            <td class="text-nowrap">{{ formatPrice(s.price) }} Kč</td>
            <td>
              <span v-if="s.is_premium" class="badge bg-danger">Premium</span>
              <span v-else class="text-muted small">—</span>
            </td>
            <td>
              <span :class="s.is_active ? 'badge bg-success' : 'badge bg-secondary'">
                {{ s.is_active ? 'Áno' : 'Nie' }}
              </span>
            </td>
            <td class="text-muted small">{{ s.sort_order }}</td>
            <td class="text-end text-nowrap">
              <button class="btn btn-sm btn-outline-secondary me-1" @click="openEdit(s)">
                <i class="bi bi-pencil"></i>
              </button>
              <button class="btn btn-sm btn-outline-danger" @click="confirmDelete(s)">
                <i class="bi bi-trash"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <div ref="modalRef" class="modal fade" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ editing ? 'Upraviť službu' : 'Nová doplnková služba' }}</h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <form @submit.prevent="save">
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label">Názov *</label>
                <input v-model="form.name" type="text" class="form-control" required />
              </div>
              <div class="mb-3">
                <label class="form-label">Popis</label>
                <textarea v-model="form.description" class="form-control" rows="3"></textarea>
              </div>
              <div class="row g-3 mb-3">
                <div class="col-sm-6">
                  <label class="form-label">Cena (€) *</label>
                  <input v-model.number="form.price" type="number" min="0" step="0.01" class="form-control" required />
                </div>
                <div class="col-sm-6">
                  <label class="form-label">Poradie</label>
                  <input v-model.number="form.sort_order" type="number" class="form-control" />
                </div>
              </div>
              <div class="mb-3 d-flex gap-4">
                <div class="form-check">
                  <input v-model="form.is_active" type="checkbox" class="form-check-input" id="chkActive" />
                  <label class="form-check-label" for="chkActive">Aktívna</label>
                </div>
                <div class="form-check">
                  <input v-model="form.is_premium" type="checkbox" class="form-check-input" id="chkPremium" />
                  <label class="form-check-label" for="chkPremium">
                    Premium <span class="text-muted small">(nemožno kombinovať s inými)</span>
                  </label>
                </div>
              </div>

              <div v-if="saveError" class="alert alert-danger py-2 small">{{ saveError }}</div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary btn-sm" @click="closeModal">Zrušiť</button>
              <button type="submit" class="btn btn-primary btn-sm" :disabled="saving">
                <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
                {{ editing ? 'Uložiť' : 'Vytvoriť' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'admin', middleware: 'auth' })

interface AddonService {
  id: number
  name: string
  description: string | null
  price: number
  is_premium: boolean
  is_active: boolean
  sort_order: number
}

const api = useApi()
const { $bootstrap } = useNuxtApp()

const services = ref<AddonService[]>([])
const loading  = ref(true)
const editing  = ref<AddonService | null>(null)
const saving   = ref(false)
const saveError = ref('')
const modalRef = ref<HTMLElement | null>(null)
let modalInstance: any = null

const emptyForm = () => ({
  name: '',
  description: '',
  price: 0,
  is_premium: false,
  is_active: true,
  sort_order: 0,
})

const form = ref(emptyForm())

const formatPrice = (val: number) =>
  Number(val).toLocaleString('sk-SK', { minimumFractionDigits: 0, maximumFractionDigits: 2 })

const load = async () => {
  loading.value = true
  try {
    services.value = await api.get<AddonService[]>('/admin/addon-services')
  } finally {
    loading.value = false
  }
}

const openModal = () => {
  if (!modalInstance && modalRef.value) {
    modalInstance = new $bootstrap.Modal(modalRef.value)
  }
  modalInstance?.show()
}

const closeModal = () => modalInstance?.hide()

const openCreate = () => {
  editing.value = null
  form.value = emptyForm()
  saveError.value = ''
  openModal()
}

const openEdit = (s: AddonService) => {
  editing.value = s
  form.value = {
    name: s.name,
    description: s.description ?? '',
    price: Number(s.price),
    is_premium: s.is_premium,
    is_active: s.is_active,
    sort_order: s.sort_order,
  }
  saveError.value = ''
  openModal()
}

const save = async () => {
  saving.value = true
  saveError.value = ''
  try {
    if (editing.value) {
      const updated = await api.put<AddonService>(`/admin/addon-services/${editing.value.id}`, form.value)
      const idx = services.value.findIndex(s => s.id === editing.value!.id)
      if (idx !== -1) services.value[idx] = updated
    } else {
      const created = await api.post<AddonService>('/admin/addon-services', form.value)
      services.value.push(created)
    }
    closeModal()
  } catch (e: any) {
    saveError.value = e.message || 'Nastala chyba pri ukladaní.'
  } finally {
    saving.value = false
  }
}

const confirmDelete = async (s: AddonService) => {
  if (!confirm(`Naozaj odstrániť „${s.name}"?`)) return
  try {
    await api.del(`/admin/addon-services/${s.id}`)
    services.value = services.value.filter(x => x.id !== s.id)
  } catch {
    alert('Nepodarilo sa odstrániť.')
  }
}

onMounted(load)
</script>
