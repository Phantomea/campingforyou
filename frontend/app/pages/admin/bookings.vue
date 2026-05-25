<template>
  <div>
    <!-- Štatistiky -->
    <div class="row g-3 mb-4">
      <div class="col-sm-4">
        <div class="card text-center border-0 bg-warning bg-opacity-10">
          <div class="card-body py-3">
            <div class="h3 fw-bold text-warning mb-0">{{ stats.pending }}</div>
            <div class="small text-muted">Čakajúce</div>
          </div>
        </div>
      </div>
      <div class="col-sm-4">
        <div class="card text-center border-0 bg-success bg-opacity-10">
          <div class="card-body py-3">
            <div class="h3 fw-bold text-success mb-0">{{ stats.confirmed }}</div>
            <div class="small text-muted">Potvrdené</div>
          </div>
        </div>
      </div>
      <div class="col-sm-4">
        <div class="card text-center border-0 bg-primary bg-opacity-10">
          <div class="card-body py-3">
            <div class="h3 fw-bold mb-0" style="color: var(--jg-primary)">{{ stats.today }}</div>
            <div class="small text-muted">Práve prebieha</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filter -->
    <div class="card mb-3">
      <div class="card-body py-2">
        <div class="d-flex gap-2 align-items-center flex-wrap">
          <label class="small fw-semibold text-muted me-1">Filter:</label>
          <button
            v-for="f in filters"
            :key="f.value"
            :class="['btn btn-sm', activeFilter === f.value ? 'btn-primary' : 'btn-dark']"
            @click="setFilter(f.value)"
          >
            {{ f.label }}
          </button>
          <div class="ms-auto">
            <input
              v-model="dateFilter"
              type="date"
              class="form-control form-control-sm"
              style="width: 160px"
              title="Filtrovať podľa dátumu (zobrazí rezervácie, ktoré v daný deň prebiehajú)"
              @change="loadBookings"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Tabuľka -->
    <div class="card">
      <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
          <thead class="table-light">
            <tr>
              <th>Termín prenájmu</th>
              <th>Zákazník</th>
              <th>Karavan</th>
              <th>Dni / Cena</th>
              <th>Stav</th>
              <th class="text-end">Akcie</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="booking in bookings" :key="booking.id">
              <td>
                <div class="fw-semibold">{{ formatDate(booking.date_from) }}</div>
                <div class="small text-muted">– {{ formatDate(booking.date_to) }}</div>
              </td>
              <td>
                <div class="fw-medium">{{ booking.customer_name }}</div>
                <div class="small text-muted">
                  <a :href="`tel:${booking.customer_phone}`" class="text-muted text-decoration-none me-2">
                    <i class="bi bi-telephone me-1"></i>{{ booking.customer_phone }}
                  </a>
                </div>
                <div class="small text-muted">
                  <a :href="`mailto:${booking.customer_email}`" class="text-muted text-decoration-none">
                    <i class="bi bi-envelope me-1"></i>{{ booking.customer_email }}
                  </a>
                </div>
                <div v-if="booking.note" class="small text-muted fst-italic mt-1">
                  <i class="bi bi-chat-left-text me-1"></i>{{ booking.note }}
                </div>
              </td>
              <td class="small">{{ booking.service?.title }}</td>
              <td class="small">
                <div v-if="booking.total_days">{{ booking.total_days }} dní</div>
                <div v-if="booking.total_price" class="fw-semibold" style="color: var(--jg-primary)">{{ booking.total_price }} €</div>
              </td>
              <td>
                <span :class="['badge', statusClass(booking.status)]">
                  {{ statusLabel(booking.status) }}
                </span>
              </td>
              <td class="text-end">
                <div class="d-flex gap-1 justify-content-end">
                  <button
                    v-if="booking.status === 'pending'"
                    @click="updateStatus(booking, 'confirmed')"
                    class="btn btn-outline-success btn-sm"
                    title="Potvrdiť"
                  >
                    <i class="bi bi-check-lg"></i>
                  </button>
                  <button
                    v-if="booking.status !== 'cancelled'"
                    @click="updateStatus(booking, 'cancelled')"
                    class="btn btn-outline-warning btn-sm"
                    title="Zrušiť"
                  >
                    <i class="bi bi-x-lg"></i>
                  </button>
                  <button
                    @click="deleteBooking(booking.id)"
                    class="btn btn-outline-danger btn-sm"
                    title="Vymazať"
                  >
                    <i class="bi bi-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="bookings.length === 0">
              <td colspan="6" class="text-center text-muted py-4">
                <i class="bi bi-calendar-x display-6 d-block mb-2"></i>
                Žiadne rezervácie
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'admin',
  middleware: 'auth',
})

interface Booking {
  id: number
  date_from: string
  date_to: string
  total_days: number | null
  total_price: number | null
  customer_name: string
  customer_phone: string
  customer_email: string
  note: string | null
  status: 'pending' | 'confirmed' | 'cancelled'
  service: { id: number; title: string } | null
}

const api = useApi()
const bookings = ref<Booking[]>([])
const stats = ref({ pending: 0, confirmed: 0, today: 0 })
const activeFilter = ref('all')
const dateFilter = ref('')

const filters = [
  { label: 'Všetky', value: 'all' },
  { label: 'Čakajúce', value: 'pending' },
  { label: 'Potvrdené', value: 'confirmed' },
  { label: 'Zrušené', value: 'cancelled' },
]

const loadBookings = async () => {
  const params = new URLSearchParams()
  if (activeFilter.value !== 'all') params.set('status', activeFilter.value)
  if (dateFilter.value) params.set('date', dateFilter.value)
  bookings.value = await api.get<Booking[]>(`/admin/bookings?${params}`)
}

const loadStats = async () => {
  stats.value = await api.get<typeof stats.value>('/admin/bookings/stats')
}

const setFilter = (f: string) => {
  activeFilter.value = f
  loadBookings()
}

const updateStatus = async (booking: Booking, status: string) => {
  try {
    const updated = await api.patch<Booking>(`/admin/bookings/${booking.id}/status`, { status })
    const idx = bookings.value.findIndex(b => b.id === booking.id)
    if (idx !== -1) bookings.value[idx] = updated
    await loadStats()
  } catch (e: any) {
    alert(e.message)
  }
}

const deleteBooking = async (id: number) => {
  if (!confirm('Naozaj vymazať túto rezerváciu?')) return
  try {
    await api.del(`/admin/bookings/${id}`)
    bookings.value = bookings.value.filter(b => b.id !== id)
    await loadStats()
  } catch (e: any) {
    alert(e.message)
  }
}

const formatDate = (dateStr: string) => {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('sk-SK', {
    day: 'numeric', month: 'numeric', year: 'numeric',
  })
}

const statusLabel = (s: string) => ({ pending: 'Čaká', confirmed: 'Potvrdená', cancelled: 'Zrušená' }[s] ?? s)
const statusClass = (s: string) => ({ pending: 'bg-warning text-dark', confirmed: 'bg-success', cancelled: 'bg-secondary' }[s] ?? 'bg-secondary')

onMounted(() => {
  loadBookings()
  loadStats()
})
</script>
