<template>
  <div>
    <div class="row g-4">

      <!-- Formulár: pridanie bloku -->
      <div class="col-lg-4">
        <div class="card">
          <div class="card-header bg-white fw-bold py-3">
            <i class="bi bi-slash-circle me-2 text-danger"></i>Zablokovat termín
          </div>
          <div class="card-body">
            <form @submit.prevent="addBlock">

              <div class="mb-3">
                <label class="form-label small fw-semibold">Karavan</label>
                <select v-model="form.service_id" class="form-select form-select-sm">
                  <option :value="null">— Všechny karavany —</option>
                  <option v-for="s in services" :key="s.id" :value="s.id">{{ s.title }}</option>
                </select>
                <div class="form-text">Prázdné = blok platí pro všechny karavany</div>
              </div>

              <div class="mb-3">
                <label class="form-label small fw-semibold">Datum od *</label>
                <input
                  v-model="form.block_date_from"
                  type="date"
                  class="form-control form-control-sm"
                  required
                />
              </div>

              <div class="mb-3">
                <label class="form-label small fw-semibold">Datum do <span class="text-muted fw-normal">(nepovinné, pro jednodení blok nechte prázdné)</span></label>
                <input
                  v-model="form.block_date_to"
                  type="date"
                  class="form-control form-control-sm"
                  :min="form.block_date_from || undefined"
                />
              </div>

              <div class="mb-3">
                <label class="form-label small fw-semibold">Důvod <span class="text-muted fw-normal">(nepovinné)</span></label>
                <input v-model="form.reason" type="text" class="form-control form-control-sm" placeholder="Např. Údržba, Rezervováno..." />
              </div>

              <button type="submit" class="btn btn-danger w-100 btn-sm" :disabled="saving">
                <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
                <i v-else class="bi bi-slash-circle me-1"></i>
                Zablokovat
              </button>
            </form>
          </div>
        </div>
      </div>

      <!-- Zoznam blokov -->
      <div class="col-lg-8">
        <div class="card">
          <div class="card-header bg-white py-3 d-flex align-items-center gap-3">
            <span class="fw-bold">Blokovaná data</span>
            <div class="ms-auto d-flex gap-2">
              <input v-model="filterDate" type="month" class="form-control form-control-sm" style="width:160px" @change="loadBlocks" />
            </div>
          </div>
          <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
              <thead class="table-light">
                <tr>
                  <th>Datum od</th>
                  <th>Datum do</th>
                  <th>Karavan</th>
                  <th>Důvod</th>
                  <th class="text-end">Akce</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="block in blocks" :key="block.id">
                  <td class="fw-semibold">{{ formatDate(block.block_date_from) }}</td>
                  <td>{{ block.block_date_to ? formatDate(block.block_date_to) : '—' }}</td>
                  <td class="small text-muted">{{ block.service?.title || '— Všechny —' }}</td>
                  <td class="small text-muted fst-italic">{{ block.reason || '—' }}</td>
                  <td class="text-end">
                    <button @click="deleteBlock(block.id)" class="btn btn-outline-danger btn-sm">
                      <i class="bi bi-trash"></i>
                    </button>
                  </td>
                </tr>
                <tr v-if="blocks.length === 0">
                  <td colspan="5" class="text-center text-muted py-4">
                    <i class="bi bi-calendar-check display-6 d-block mb-2"></i>
                    Žádná blokovaná data
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'admin', middleware: 'auth' })

interface Block {
  id: number
  block_date_from: string
  block_date_to: string | null
  reason: string | null
  service: { id: number; title: string } | null
}

interface Service {
  id: number
  title: string
}

const api = useApi()
const blocks   = ref<Block[]>([])
const services = ref<Service[]>([])
const saving   = ref(false)
const filterDate = ref('')

const form = ref({
  service_id: null as number | null,
  block_date_from: '',
  block_date_to: '',
  reason: '',
})

const loadBlocks = async () => {
  const params = new URLSearchParams()
  if (filterDate.value) params.set('from', `${filterDate.value}-01`)
  blocks.value = await api.get<Block[]>(`/admin/booking-blocks?${params}`)
}

const loadServices = async () => {
  services.value = await api.get<Service[]>('/admin/services')
}

const addBlock = async () => {
  saving.value = true
  try {
    await api.post('/admin/booking-blocks', {
      service_id:      form.value.service_id,
      block_date_from: form.value.block_date_from,
      block_date_to:   form.value.block_date_to || null,
      reason:          form.value.reason,
    })
    form.value = { service_id: null, block_date_from: '', block_date_to: '', reason: '' }
    await loadBlocks()
  } catch (e: any) {
    alert(e.message || 'Chyba při ukládání')
  } finally {
    saving.value = false
  }
}

const deleteBlock = async (id: number) => {
  if (!confirm('Odstranit tento blok?')) return
  try {
    await api.del(`/admin/booking-blocks/${id}`)
    blocks.value = blocks.value.filter(b => b.id !== id)
  } catch (e: any) {
    alert(e.message)
  }
}

const formatDate = (d: string) =>
  new Date(d).toLocaleDateString('cs-CZ', { day: 'numeric', month: 'numeric', year: 'numeric' })

onMounted(() => { loadBlocks(); loadServices() })
</script>
