<template>
  <div class="booking-form-wrap">
    <!-- Úspešné odoslanie -->
    <div v-if="success" class="text-center py-4">
      <div class="mb-3 booking-success-icon">
        <i class="bi bi-check-circle-fill"></i>
      </div>
      <h4 class="fw-bold mb-2">Rezervácia prijatá!</h4>
      <p class="text-muted mb-1">
        <strong>{{ formatDate(submittedData?.date_from) }}</strong>
        –
        <strong>{{ formatDate(submittedData?.date_to) }}</strong>
      </p>
      <p v-if="submittedData?.total_days" class="text-muted mb-1">
        Počet dní: <strong>{{ submittedData.total_days }}</strong>
        <template v-if="submittedData.total_price">
          · Celková cena: <strong>{{ submittedData.total_price }} €</strong>
        </template>
      </p>
      <p class="text-muted small">
        Rezervácia čaká na schválenie. O potvrdení vás informujeme na {{ submittedData?.customer_email }}.
      </p>
      <button @click="reset" class="btn btn-dark mt-3 btn-sm">
        <i class="bi bi-plus me-1"></i>Nová rezervácia
      </button>
    </div>

    <!-- Formulár -->
    <form v-else @submit.prevent="submit">

      <!-- Dátumy -->
      <div class="row g-3 mb-3">
        <div class="col-sm-6">
          <label class="form-label fw-semibold">
            <i class="bi bi-calendar-event me-1" style="color: var(--jg-primary)"></i>Dátum prevzatia *
          </label>
          <input
            v-model="form.date_from"
            type="date"
            class="form-control"
            :min="minDate"
            required
            @change="onDateFromChange"
          />
        </div>
        <div class="col-sm-6">
          <label class="form-label fw-semibold">
            <i class="bi bi-calendar-check me-1" style="color: var(--jg-primary)"></i>Dátum vrátenia *
          </label>
          <input
            v-model="form.date_to"
            type="date"
            class="form-control"
            :min="minDateTo"
            required
            @change="onDateToChange"
          />
        </div>
      </div>

      <!-- Výpočet ceny -->
      <div v-if="calculatedDays > 0" class="alert alert-info py-2 mb-3 small">
        <i class="bi bi-info-circle me-1"></i>
        Počet dní: <strong>{{ calculatedDays }}</strong>
        <template v-if="pricePerDay && calculatedDays > 0">
          · Celková cena: <strong>{{ calculatedPrice }} €</strong>
        </template>
      </div>

      <!-- Validačná správa pre dátumy -->
      <div v-if="dateError" class="alert alert-danger py-2 small mb-3">
        <i class="bi bi-exclamation-triangle me-1"></i>{{ dateError }}
      </div>

      <!-- Kontaktné údaje -->
      <template v-if="form.date_from && form.date_to && !dateError">
        <hr class="my-3" />
        <p class="fw-semibold mb-3 small text-uppercase text-muted">Vaše kontaktné údaje</p>

        <div class="mb-3">
          <label class="form-label">Meno a priezvisko *</label>
          <input v-model="form.customer_name" type="text" class="form-control" required placeholder="Ján Novák" />
        </div>
        <div class="row g-3 mb-3">
          <div class="col-sm-6">
            <label class="form-label">Telefón *</label>
            <input v-model="form.customer_phone" type="tel" class="form-control" required placeholder="+421 900 000 000" />
          </div>
          <div class="col-sm-6">
            <label class="form-label">E-mail *</label>
            <input v-model="form.customer_email" type="email" class="form-control" required placeholder="jan@example.sk" />
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">Poznámka <span class="text-muted">(nepovinné)</span></label>
          <textarea v-model="form.note" class="form-control" rows="2" placeholder="Napr. špeciálne požiadavky, miesto odovzdania..."></textarea>
        </div>

        <div v-if="submitError" class="alert alert-danger py-2 small">
          <i class="bi bi-exclamation-triangle me-1"></i>{{ submitError }}
        </div>

        <button type="submit" class="btn btn-primary w-100" :disabled="submitting">
          <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>
          <i v-else class="bi bi-calendar-check me-2"></i>
          {{ submitting ? 'Odosielam...' : 'Rezervovať termín prenájmu' }}
        </button>
      </template>
    </form>
  </div>
</template>

<script setup lang="ts">
const props = defineProps<{
  serviceId: number
  pricePerDay?: number | null
}>()

const api = useApi()

const today = new Date()
today.setHours(0, 0, 0, 0)

const minDate = computed(() => {
  const d = new Date()
  d.setDate(d.getDate() + 1) // minimum tomorrow
  return d.toISOString().split('T')[0]
})

const minDateTo = computed(() => {
  if (!form.value.date_from) return minDate.value
  const d = new Date(form.value.date_from)
  d.setDate(d.getDate() + 1)
  return d.toISOString().split('T')[0]
})

const form = ref({
  date_from: '',
  date_to: '',
  customer_name: '',
  customer_phone: '',
  customer_email: '',
  note: '',
})

const dateError = ref('')
const submitting = ref(false)
const success = ref(false)
const submitError = ref('')
const submittedData = ref<any>(null)

const calculatedDays = computed(() => {
  if (!form.value.date_from || !form.value.date_to) return 0
  const from = new Date(form.value.date_from)
  const to = new Date(form.value.date_to)
  const diff = Math.round((to.getTime() - from.getTime()) / (1000 * 60 * 60 * 24))
  return diff > 0 ? diff : 0
})

const calculatedPrice = computed(() => {
  if (!props.pricePerDay || calculatedDays.value <= 0) return null
  return Math.round(props.pricePerDay * calculatedDays.value * 100) / 100
})

const validateDates = () => {
  dateError.value = ''
  if (!form.value.date_from || !form.value.date_to) return
  const from = new Date(form.value.date_from)
  const to = new Date(form.value.date_to)
  if (to <= from) {
    dateError.value = 'Dátum vrátenia musí byť po dátume prevzatia.'
    return
  }
  if (calculatedDays.value < 1) {
    dateError.value = 'Minimálna doba prenájmu je 1 deň.'
  }
}

const onDateFromChange = () => {
  // Reset date_to if it's before new date_from
  if (form.value.date_to && form.value.date_to <= form.value.date_from) {
    form.value.date_to = ''
  }
  validateDates()
}

const onDateToChange = () => {
  validateDates()
}

onMounted(async () => {
  await api.getCsrfCookie()
})

const formatDate = (dateStr: string) => {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleDateString('sk-SK', {
    day: 'numeric', month: 'long', year: 'numeric',
  })
}

const submit = async () => {
  validateDates()
  if (dateError.value) return

  submitError.value = ''
  submitting.value = true
  try {
    const response = await api.post<any>('/bookings', {
      service_id: props.serviceId,
      date_from: form.value.date_from,
      date_to: form.value.date_to,
      customer_name: form.value.customer_name,
      customer_phone: form.value.customer_phone,
      customer_email: form.value.customer_email,
      note: form.value.note,
    })
    submittedData.value = response
    success.value = true
  } catch (e: any) {
    submitError.value = e.message || 'Chyba pri odosielaní. Skúste znova.'
  } finally {
    submitting.value = false
  }
}

const reset = () => {
  success.value = false
  submittedData.value = null
  submitError.value = ''
  dateError.value = ''
  form.value = {
    date_from: '',
    date_to: '',
    customer_name: '',
    customer_phone: '',
    customer_email: '',
    note: '',
  }
}
</script>

<style scoped>
.booking-success-icon i {
  font-size: 3rem;
  color: #198754;
}
</style>
