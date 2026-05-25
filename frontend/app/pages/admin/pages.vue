<template>
  <div>
    <div class="d-flex justify-content-end mb-4">
      <button @click="openModal()" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>
        Pridať stránku
      </button>
    </div>

    <div class="card">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th>Názov</th>
              <th>Slug</th>
              <th class="text-end">Akcie</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="page in pages" :key="page.id">
              <td class="fw-medium">{{ page.title }}</td>
              <td><code class="text-muted">/{{ page.slug }}</code></td>
              <td class="text-end">
                <button @click="openModal(page)" class="btn btn-dark btn-sm me-1">
                  <i class="bi bi-pencil"></i>
                </button>
                <button @click="deletePage(page.id)" class="btn btn-outline-danger btn-sm">
                  <i class="bi bi-trash"></i>
                </button>
              </td>
            </tr>
            <tr v-if="pages.length === 0">
              <td colspan="3" class="text-center text-muted py-4">
                <i class="bi bi-inbox display-6 d-block mb-2"></i>
                Žiadne stránky
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Bootstrap Modal -->
    <div class="modal fade" id="pageModal" tabindex="-1" ref="modalRef">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ editingPage ? 'Upraviť stránku' : 'Pridať stránku' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <form @submit.prevent="savePage">
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label">Názov *</label>
                <input v-model="form.title" type="text" class="form-control" required />
              </div>
              <div class="mb-3">
                <label class="form-label">Slug *</label>
                <input v-model="form.slug" type="text" class="form-control" required placeholder="napr. about, contact" />
              </div>
              <div class="mb-3">
                <label class="form-label">Obsah</label>
                <textarea v-model="form.content" class="form-control font-monospace" rows="10"></textarea>
                <div class="form-text">Môžete použiť HTML formátovanie</div>
              </div>
              <div class="mb-3">
                <label class="form-label">Meta title</label>
                <input v-model="form.meta_title" type="text" class="form-control" />
              </div>
              <div class="mb-3">
                <label class="form-label">Meta description</label>
                <textarea v-model="form.meta_description" class="form-control" rows="2"></textarea>
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

interface Page {
  id: number
  title: string
  slug: string
  content: string | null
  meta_title: string | null
  meta_description: string | null
}

const api = useApi()
const { $bootstrap } = useNuxtApp()
const pages = ref<Page[]>([])
const modalRef = ref<HTMLElement | null>(null)
const modalInstance = ref<any>(null)
const editingPage = ref<Page | null>(null)
const saving = ref(false)

const form = ref({
  title: '',
  slug: '',
  content: '',
  meta_title: '',
  meta_description: '',
})

const loadPages = async () => {
  pages.value = await api.get<Page[]>('/admin/pages')
}

const openModal = (page?: Page) => {
  if (page) {
    editingPage.value = page
    form.value = {
      title: page.title,
      slug: page.slug,
      content: page.content || '',
      meta_title: page.meta_title || '',
      meta_description: page.meta_description || '',
    }
  } else {
    editingPage.value = null
    form.value = {
      title: '',
      slug: '',
      content: '',
      meta_title: '',
      meta_description: '',
    }
  }
  if (!modalInstance.value && modalRef.value) {
    modalInstance.value = new $bootstrap.Modal(modalRef.value)
  }
  modalInstance.value?.show()
}

const closeModal = () => {
  modalInstance.value?.hide()
  editingPage.value = null
}

const savePage = async () => {
  saving.value = true
  try {
    if (editingPage.value) {
      await api.put(`/admin/pages/${editingPage.value.id}`, form.value)
    } else {
      await api.post('/admin/pages', form.value)
    }
    await loadPages()
    closeModal()
  } catch (e: any) {
    alert(e.message || 'Chyba pri ukladaní')
  } finally {
    saving.value = false
  }
}

const deletePage = async (id: number) => {
  if (!confirm('Naozaj chcete vymazať túto stránku?')) return
  try {
    await api.del(`/admin/pages/${id}`)
    await loadPages()
  } catch (e: any) {
    alert(e.message || 'Chyba pri mazaní')
  }
}

onMounted(loadPages)
</script>
