<template>
  <div>
    <div class="d-flex justify-content-end mb-4">
      <button @click="openModal()" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>
        Pridať používateľa
      </button>
    </div>

    <div class="card">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th>Meno</th>
              <th>Email</th>
              <th>Rola</th>
              <th class="text-end">Akcie</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in users" :key="user.id">
              <td class="fw-medium">{{ user.name }}</td>
              <td class="text-muted">{{ user.email }}</td>
              <td>
                <span :class="['badge', user.role === 'super_admin' ? 'bg-danger' : 'bg-primary']">
                  {{ user.role === 'super_admin' ? 'Super Admin' : 'Majiteľ' }}
                </span>
              </td>
              <td class="text-end">
                <button @click="openModal(user)" class="btn btn-dark btn-sm me-1">
                  <i class="bi bi-pencil"></i>
                </button>
                <button
                  @click="deleteUser(user.id)"
                  class="btn btn-outline-danger btn-sm"
                  :disabled="user.id === currentUser?.id"
                >
                  <i class="bi bi-trash"></i>
                </button>
              </td>
            </tr>
            <tr v-if="users.length === 0">
              <td colspan="4" class="text-center text-muted py-4">
                <i class="bi bi-inbox display-6 d-block mb-2"></i>
                Žiadni používatelia
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Bootstrap Modal -->
    <div class="modal fade" id="userModal" tabindex="-1" ref="modalRef">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ editingUser ? 'Upraviť používateľa' : 'Pridať používateľa' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <form @submit.prevent="saveUser">
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label">Meno *</label>
                <input v-model="form.name" type="text" class="form-control" required />
              </div>
              <div class="mb-3">
                <label class="form-label">Email *</label>
                <input v-model="form.email" type="email" class="form-control" required />
              </div>
              <div class="mb-3">
                <label class="form-label">{{ editingUser ? 'Nové heslo (ponechajte prázdne)' : 'Heslo *' }}</label>
                <input
                  v-model="form.password"
                  type="password"
                  class="form-control"
                  :required="!editingUser"
                  minlength="8"
                />
              </div>
              <div class="mb-3">
                <label class="form-label">Rola *</label>
                <select v-model="form.role" class="form-select" required>
                  <option value="owner">Majiteľ</option>
                  <option value="super_admin">Super Admin</option>
                </select>
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
  middleware: 'super-admin',
})

interface User {
  id: number
  name: string
  email: string
  role: 'owner' | 'super_admin'
}

const api = useApi()
const { $bootstrap } = useNuxtApp()
const { user: currentUser } = useAuth()
const users = ref<User[]>([])
const modalRef = ref<HTMLElement | null>(null)
const modalInstance = ref<any>(null)
const editingUser = ref<User | null>(null)
const saving = ref(false)

const form = ref({
  name: '',
  email: '',
  password: '',
  role: 'owner' as 'owner' | 'super_admin',
})

const loadUsers = async () => {
  users.value = await api.get<User[]>('/admin/users')
}

const openModal = (user?: User) => {
  if (user) {
    editingUser.value = user
    form.value = {
      name: user.name,
      email: user.email,
      password: '',
      role: user.role,
    }
  } else {
    editingUser.value = null
    form.value = {
      name: '',
      email: '',
      password: '',
      role: 'owner',
    }
  }
  if (!modalInstance.value && modalRef.value) {
    modalInstance.value = new $bootstrap.Modal(modalRef.value)
  }
  modalInstance.value?.show()
}

const closeModal = () => {
  modalInstance.value?.hide()
  editingUser.value = null
}

const saveUser = async () => {
  saving.value = true
  try {
    const data = { ...form.value }
    if (editingUser.value && !data.password) {
      delete (data as any).password
    }

    if (editingUser.value) {
      await api.put(`/admin/users/${editingUser.value.id}`, data)
    } else {
      await api.post('/admin/users', data)
    }
    await loadUsers()
    closeModal()
  } catch (e: any) {
    alert(e.message || 'Chyba pri ukladaní')
  } finally {
    saving.value = false
  }
}

const deleteUser = async (id: number) => {
  if (!confirm('Naozaj chcete vymazať tohto používateľa?')) return
  try {
    await api.del(`/admin/users/${id}`)
    await loadUsers()
  } catch (e: any) {
    alert(e.message || 'Chyba pri mazaní')
  }
}

onMounted(loadUsers)
</script>
