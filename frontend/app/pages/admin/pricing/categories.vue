<template>
  <div>
    <div class="card">
      <div class="card-body border-bottom">
        <form class="d-flex gap-2" @submit.prevent="addCategory">
          <input
            v-model="newCategoryName"
            type="text"
            class="form-control"
            placeholder="Názov novej kategórie"
            style="max-width:300px"
          />
          <button type="submit" class="btn btn-primary" :disabled="!newCategoryName.trim() || saving">
            <i class="bi bi-plus-lg me-1"></i>Pridať kategóriu
          </button>
        </form>
      </div>
      <ul class="list-group list-group-flush">
        <li
          v-for="cat in categories"
          :key="cat.id"
          class="list-group-item d-flex align-items-center gap-2 py-2"
        >
          <template v-if="renamingId === cat.id">
            <input
              v-model="renameValue"
              type="text"
              class="form-control form-control-sm"
              style="max-width:300px"
              @keyup.enter="confirmRename(cat)"
              @keyup.esc="renamingId = null"
            />
            <button @click="confirmRename(cat)" class="btn btn-sm btn-primary" :disabled="saving">
              <i class="bi bi-check-lg"></i>
            </button>
            <button @click="renamingId = null" class="btn btn-sm btn-secondary">
              <i class="bi bi-x-lg"></i>
            </button>
          </template>
          <template v-else>
            <span class="flex-grow-1">
              {{ cat.name }}
              <span class="badge bg-secondary ms-1">{{ cat.items_count }}</span>
            </span>
            <button @click="startRename(cat)" class="btn btn-dark btn-sm">
              <i class="bi bi-pencil"></i>
            </button>
            <button @click="deleteCategory(cat)" class="btn btn-outline-danger btn-sm">
              <i class="bi bi-trash"></i>
            </button>
          </template>
        </li>
        <li v-if="categories.length === 0" class="list-group-item text-center text-muted py-4">
          <i class="bi bi-inbox display-6 d-block mb-2"></i>
          Žiadne kategórie
        </li>
      </ul>
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
  sort_order: number
  items_count: number
}

const api = useApi()
const categories = ref<PricingCategory[]>([])
const newCategoryName = ref('')
const renamingId = ref<number | null>(null)
const renameValue = ref('')
const saving = ref(false)

const loadCategories = async () => {
  categories.value = await api.get<PricingCategory[]>('/admin/pricing-categories')
}

const addCategory = async () => {
  const name = newCategoryName.value.trim()
  if (!name) return
  saving.value = true
  try {
    const created = await api.post<PricingCategory>('/admin/pricing-categories', { name })
    categories.value.push(created)
    newCategoryName.value = ''
  } catch (e: any) {
    alert(e.message || 'Chyba pri pridávaní')
  } finally {
    saving.value = false
  }
}

const startRename = (cat: PricingCategory) => {
  renamingId.value = cat.id
  renameValue.value = cat.name
}

const confirmRename = async (cat: PricingCategory) => {
  const name = renameValue.value.trim()
  if (!name || name === cat.name) {
    renamingId.value = null
    return
  }
  saving.value = true
  try {
    const updated = await api.put<PricingCategory>(`/admin/pricing-categories/${cat.id}`, { name })
    const idx = categories.value.findIndex(c => c.id === cat.id)
    if (idx !== -1) categories.value[idx] = updated
    renamingId.value = null
  } catch (e: any) {
    alert(e.message || 'Chyba pri premenovaní')
  } finally {
    saving.value = false
  }
}

const deleteCategory = async (cat: PricingCategory) => {
  const msg = cat.items_count > 0
    ? `Odstrániť kategóriu "${cat.name}"? Kategória bude odstránená z ${cat.items_count} položiek.`
    : `Odstrániť kategóriu "${cat.name}"?`
  if (!confirm(msg)) return
  try {
    await api.del(`/admin/pricing-categories/${cat.id}`)
    categories.value = categories.value.filter(c => c.id !== cat.id)
  } catch (e: any) {
    alert(e.message || 'Chyba pri mazaní')
  }
}

onMounted(loadCategories)
</script>
