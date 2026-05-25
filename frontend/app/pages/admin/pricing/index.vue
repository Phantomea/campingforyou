<template>
  <div>
    <div class="d-flex justify-content-end mb-4">
      <button @click="openModal()" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>
        Pridať položku
      </button>
    </div>

    <div class="card">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th>Názov</th>
              <th>Kategória</th>
              <th>Cena</th>
              <th class="text-end">Akcie</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in items" :key="item.id">
              <td class="fw-medium">{{ item.name }}</td>
              <td>
                <span v-if="item.category" class="badge bg-light text-dark">{{ item.category.name }}</span>
                <span v-else class="text-muted">-</span>
              </td>
              <td class="text-primary fw-medium">
                <template v-if="item.price_from && item.price_to">
                  {{ item.price_from }} - {{ item.price_to }} €
                </template>
                <template v-else-if="item.price_from">
                  od {{ item.price_from }} €
                </template>
                <template v-else>-</template>
              </td>
              <td class="text-end">
                <button @click="openModal(item)" class="btn btn-dark btn-sm me-1">
                  <i class="bi bi-pencil"></i>
                </button>
                <button @click="deleteItem(item.id)" class="btn btn-outline-danger btn-sm">
                  <i class="bi bi-trash"></i>
                </button>
              </td>
            </tr>
            <tr v-if="items.length === 0">
              <td colspan="4" class="text-center text-muted py-4">
                <i class="bi bi-inbox display-6 d-block mb-2"></i>
                Žiadne položky
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="pricingModal" tabindex="-1" ref="modalRef">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ editingItem ? 'Upraviť položku' : 'Pridať položku' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <form @submit.prevent="saveItem">
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label">Názov *</label>
                <input v-model="form.name" type="text" class="form-control" required />
              </div>
              <div class="mb-3">
                <label class="form-label">Kategória</label>
                <select v-model="form.pricing_category_id" class="form-select">
                  <option :value="null">— bez kategórie —</option>
                  <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                    {{ cat.name }}
                  </option>
                </select>
              </div>
              <div class="row mb-3">
                <div class="col-6">
                  <label class="form-label">Cena od (€)</label>
                  <input v-model="form.price_from" type="number" step="0.01" class="form-control" />
                </div>
                <div class="col-6">
                  <label class="form-label">Cena do (€)</label>
                  <input v-model="form.price_to" type="number" step="0.01" class="form-control" />
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label">Popis</label>
                <textarea v-model="form.description" class="form-control" rows="3"></textarea>
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

interface PricingCategory {
  id: number
  name: string
}

interface PricingItem {
  id: number
  name: string
  pricing_category_id: number | null
  category: PricingCategory | null
  price_from: number | null
  price_to: number | null
  description: string | null
  sort_order: number
}

const api = useApi()
const { $bootstrap } = useNuxtApp()
const items = ref<PricingItem[]>([])
const categories = ref<PricingCategory[]>([])
const modalRef = ref<HTMLElement | null>(null)
const modalInstance = ref<any>(null)
const editingItem = ref<PricingItem | null>(null)
const saving = ref(false)

const form = ref({
  name: '',
  pricing_category_id: null as number | null,
  price_from: null as number | null,
  price_to: null as number | null,
  description: '',
})

const loadItems = async () => {
  items.value = await api.get<PricingItem[]>('/admin/pricing')
}

const loadCategories = async () => {
  categories.value = await api.get<PricingCategory[]>('/admin/pricing-categories')
}

const openModal = (item?: PricingItem) => {
  if (item) {
    editingItem.value = item
    form.value = {
      name: item.name,
      pricing_category_id: item.pricing_category_id,
      price_from: item.price_from,
      price_to: item.price_to,
      description: item.description || '',
    }
  } else {
    editingItem.value = null
    form.value = { name: '', pricing_category_id: null, price_from: null, price_to: null, description: '' }
  }
  if (!modalInstance.value && modalRef.value) {
    modalInstance.value = new $bootstrap.Modal(modalRef.value)
  }
  modalInstance.value?.show()
}

const closeModal = () => {
  modalInstance.value?.hide()
  editingItem.value = null
}

const saveItem = async () => {
  saving.value = true
  try {
    if (editingItem.value) {
      await api.put(`/admin/pricing/${editingItem.value.id}`, form.value)
    } else {
      await api.post('/admin/pricing', form.value)
    }
    await loadItems()
    closeModal()
  } catch (e: any) {
    alert(e.message || 'Chyba pri ukladaní')
  } finally {
    saving.value = false
  }
}

const deleteItem = async (id: number) => {
  if (!confirm('Naozaj chcete vymazať túto položku?')) return
  try {
    await api.del(`/admin/pricing/${id}`)
    await loadItems()
  } catch (e: any) {
    alert(e.message || 'Chyba pri mazaní')
  }
}

onMounted(() => Promise.all([loadItems(), loadCategories()]))
</script>
