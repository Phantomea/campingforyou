<template>
  <div class="booking-form-wrap">

    <!-- Úspešné odoslanie -->
    <div v-if="success" class="py-4">
      <div class="text-center mb-3">
        <div class="mb-3 booking-success-icon">
          <i class="bi bi-check-circle-fill"></i>
        </div>
        <h4 class="fw-bold mb-2">Rezervace přijata!</h4>
        <p class="text-muted mb-1">
          <strong>{{ formatDate(submittedData?.date_from) }}</strong>
          –
          <strong>{{ formatDate(submittedData?.date_to) }}</strong>
        </p>
        <p v-if="submittedData?.total_days" class="text-muted mb-1">
          Počet dní: <strong>{{ submittedData.total_days }}</strong>
          <template v-if="submittedData.total_price">
            · Celková cena: <strong>{{ submittedData.total_price }} Kč</strong>
          </template>
        </p>
        <p class="text-muted small mb-3">
          Rezervace čeká na schválení. O potvrzení vás informujeme na {{ submittedData?.customer_email }}.
        </p>
      </div>

      <!-- Platobný plán -->
      <div v-if="submittedData" class="success-pp-card mb-3">
        <div class="success-pp-head">
          <i class="bi bi-calendar-event me-2"></i>Platobný plán
        </div>
        <div class="success-pp-body">
          <template v-if="submittedData.single_payment">
            <div class="success-pp-row">
              <span class="success-pp-lbl">Platba</span>
              <span class="success-pp-val">{{ submittedData.upfront_amount }} Kč</span>
            </div>
            <div class="success-pp-row">
              <span class="success-pp-lbl">Splatnosť</span>
              <span class="success-pp-deadline">do {{ formatDate(submittedData.payment_deadline) }}</span>
            </div>
          </template>
          <template v-else>
            <div class="success-pp-row">
              <span class="success-pp-lbl">1. Záloha</span>
              <span class="success-pp-val">{{ submittedData.upfront_amount }} Kč</span>
            </div>
            <div class="success-pp-row mb-1">
              <span class="success-pp-lbl">Splatnosť</span>
              <span class="success-pp-deadline">do {{ formatDate(submittedData.payment_deadline) }}</span>
            </div>
            <div class="success-pp-row mt-2">
              <span class="success-pp-lbl">2. Doplatek</span>
              <span class="success-pp-val">{{ submittedData.remaining_amount }} Kč</span>
            </div>
            <div class="success-pp-row">
              <span class="success-pp-lbl">Splatnosť</span>
              <span class="success-pp-deadline">do {{ formatDate(submittedData.full_payment_deadline) }}</span>
            </div>
          </template>
        </div>
      </div>

      <div class="text-center">
        <button @click="reset" class="btn btn-dark btn-sm">
          <i class="bi bi-plus me-1"></i>Nová rezervace
        </button>
      </div>
    </div>

    <!-- Formulár -->
    <form v-else @submit.prevent="submit">

      <!-- Krok 1: výber termínu cez kalendár -->
      <div class="mb-3">
        <!-- Stav výberu -->
        <div class="selection-hint mb-2" :class="selectionPhase === 'from' ? 'hint-from' : 'hint-to'">
          <template v-if="selectionPhase === 'from'">
            <i class="bi bi-calendar-event me-1"></i>
            Klikněte na <strong>datum převzetí</strong>
          </template>
          <template v-else-if="selectionPhase === 'to'">
            <i class="bi bi-calendar-check me-1"></i>
            Nyní klikněte na <strong>datum vrácení</strong>
            <button type="button" class="btn-reset-dates ms-2" @click="resetDates">
              <i class="bi bi-x"></i>
            </button>
          </template>
          <template v-else>
            <i class="bi bi-check-circle me-1 text-success"></i>
            <span class="text-success">Termín vybrán</span>
            <button type="button" class="btn-reset-dates ms-2" @click="resetDates">
              <i class="bi bi-pencil"></i> Změnit
            </button>
          </template>
        </div>

        <!-- Vybrané dátumy -->
        <div v-if="dateFrom || dateTo" class="selected-range mb-2">
          <span class="range-badge from">
            <i class="bi bi-box-arrow-right me-1"></i>
            {{ dateFrom ? formatDate(dateFrom) : '—' }}
          </span>
          <i class="bi bi-arrow-right mx-2 text-muted"></i>
          <span class="range-badge to" :class="{ 'to--empty': !dateTo }">
            <i class="bi bi-box-arrow-left me-1"></i>
            {{ dateTo ? formatDate(dateTo) : '...' }}
          </span>
          <span v-if="calculatedDays > 0" class="ms-3 days-badge">
            {{ calculatedDays }} {{ calculatedDays === 1 ? 'den' : calculatedDays < 5 ? 'dny' : 'dní' }}
            <template v-if="previewPrice !== null"> · {{ formatPrice(previewPrice) }} Kč</template>
            <template v-if="deposit && deposit > 0"> + záloha {{ deposit }} Kč</template>
          </span>
        </div>

        <!-- Kalendár -->
        <BookingCalendar
          :dateFrom="dateFrom"
          :dateTo="dateTo"
          :blockedDays="blockedDays"
          :selectingEnd="selectionPhase === 'to'"
          @update:dateFrom="onDateFromUpdate"
          @update:dateTo="onDateToUpdate"
          @monthChange="onMonthChange"
        />
      </div>

      <!-- Krok 2: výber doplnkových služieb (len po výbere oboch dátumov) -->
      <template v-if="selectionPhase === 'done' && addonServices.length > 0">
        <hr class="my-3" />
        <p class="fw-semibold mb-3 small text-uppercase text-muted">Doplnkové služby</p>

        <div class="addon-services-list">
          <div
            v-for="addon in addonServices"
            :key="addon.id"
            class="addon-service-item"
            :class="{ 'addon-service-item--selected': selectedAddonIds.includes(addon.id), 'addon-service-item--premium': addon.is_premium }"
            @click="toggleAddon(addon)"
          >
            <div class="addon-check">
              <div class="addon-checkbox" :class="{ checked: selectedAddonIds.includes(addon.id) }">
                <svg v-if="selectedAddonIds.includes(addon.id)" xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16">
                  <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z"/>
                </svg>
              </div>
            </div>
            <div class="addon-body">
              <div class="addon-name">
                {{ addon.name }}
                <span v-if="addon.is_premium" class="addon-premium-badge">Premium</span>
              </div>
              <div v-if="addon.description" class="addon-desc">{{ addon.description }}</div>
            </div>
            <div class="addon-price">+ {{ formatPrice(addon.price) }} Kč</div>
          </div>
        </div>

        <div v-if="selectedAddons.length > 0" class="addon-summary mt-2">
          <span class="text-muted small">Doplnkové služby:</span>
          <span class="fw-semibold small ms-1">+ {{ formatPrice(addonsTotal) }} Kč</span>
          <span v-if="previewPrice !== null" class="text-muted small ms-2">
            (celkom {{ formatPrice(previewPrice + addonsTotal) }} Kč)
          </span>
        </div>
      </template>

      <!-- Krok 3: kontaktné údaje (len po výbere oboch dátumov) -->
      <template v-if="selectionPhase === 'done'">
        <hr class="my-3" />
        <p class="fw-semibold mb-3 small text-uppercase text-muted">Vaše kontaktní údaje</p>

        <!-- Faktura na firmu — prvá položka -->
        <div class="form-check mb-3">
          <input type="checkbox" class="form-check-input" id="wantInvoiceBf" v-model="wantInvoice" />
          <label class="form-check-label fw-semibold" for="wantInvoiceBf">Chci fakturu na firmu</label>
        </div>

        <!-- IČO / firma / DIČ — nad menom, len ak wantInvoice -->
        <template v-if="wantInvoice">
          <div class="mb-3">
            <label class="form-label">IČO</label>
            <input v-model="form.billing_ico" type="text" class="form-control" placeholder="12345678" maxlength="8" />
          </div>
          <div class="mb-3">
            <label class="form-label">Název firmy</label>
            <input v-model="form.billing_company" type="text" class="form-control" placeholder="Firma s.r.o." />
          </div>
          <div class="mb-3">
            <label class="form-label">DIČ <span class="text-muted">(nepovinné)</span></label>
            <input v-model="form.billing_dic" type="text" class="form-control" placeholder="CZ12345678" />
          </div>
          <hr class="mb-3" />
        </template>

        <div class="mb-3">
          <label class="form-label">Jméno a příjmení *</label>
          <input v-model="form.customer_name" type="text" class="form-control" required placeholder="Jan Novák" />
        </div>
        <div class="row g-3 mb-3">
          <div class="col-sm-6">
            <label class="form-label">Telefon *</label>
            <input v-model="form.customer_phone" type="tel" class="form-control" required placeholder="+421 900 000 000" />
          </div>
          <div class="col-sm-6">
            <label class="form-label">E-mail *</label>
            <input v-model="form.customer_email" type="email" class="form-control" required placeholder="jan@example.sk" />
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">Ulice a číslo</label>
          <input v-model="form.billing_street" type="text" class="form-control" placeholder="Hlavní 1" />
        </div>
        <div class="row g-3 mb-3">
          <div class="col-4">
            <label class="form-label">PSČ</label>
            <input v-model="form.billing_zip" type="text" class="form-control" placeholder="110 00" />
          </div>
          <div class="col-8">
            <label class="form-label">Město</label>
            <input v-model="form.billing_city" type="text" class="form-control" placeholder="Praha" />
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">Poznámka <span class="text-muted">(nepovinné)</span></label>
          <textarea v-model="form.note" class="form-control" rows="2" placeholder="Napr. špeciálne požiadavky, miesto predania..."></textarea>
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
interface AddonService {
  id: number
  name: string
  description: string | null
  price: number
  is_premium: boolean
  is_active: boolean
}

const props = defineProps<{
  serviceId: number
  deposit?: number | null
}>()

const api = useApi()

// Dates
const dateFrom = ref('')
const dateTo   = ref('')
const blockedDays = ref<string[]>([])

// 'from' | 'to' | 'done'
const selectionPhase = computed(() => {
  if (!dateFrom.value) return 'from'
  if (!dateTo.value)   return 'to'
  return 'done'
})

// Addon services
const addonServices = ref<AddonService[]>([])
const selectedAddonIds = ref<number[]>([])

const selectedAddons = computed(() =>
  addonServices.value.filter(a => selectedAddonIds.value.includes(a.id))
)

const addonsTotal = computed(() =>
  selectedAddons.value.reduce((sum, a) => sum + Number(a.price), 0)
)

const toggleAddon = (addon: AddonService) => {
  const isSelected = selectedAddonIds.value.includes(addon.id)

  if (addon.is_premium) {
    // Výber premium — zruší všetky ostatné
    selectedAddonIds.value = isSelected ? [] : [addon.id]
  } else {
    if (isSelected) {
      selectedAddonIds.value = selectedAddonIds.value.filter(id => id !== addon.id)
    } else {
      // Odstráni premium ak bol vybraný, pridá túto službu
      selectedAddonIds.value = [
        ...selectedAddonIds.value.filter(id => {
          const a = addonServices.value.find(s => s.id === id)
          return a && !a.is_premium
        }),
        addon.id,
      ]
    }
  }
}

// Contact form
const wantInvoice = ref(false)
const form = ref({
  customer_name:   '',
  customer_phone:  '',
  customer_email:  '',
  note:            '',
  billing_ico:     '',
  billing_company: '',
  billing_dic:     '',
  billing_street:  '',
  billing_city:    '',
  billing_zip:     '',
})

const submitting   = ref(false)
const success      = ref(false)
const submitError  = ref('')
const submittedData = ref<any>(null)

// Price calculation
const calculatedDays = computed(() => {
  if (!dateFrom.value || !dateTo.value) return 0
  const from = new Date(dateFrom.value)
  const to   = new Date(dateTo.value)
  return Math.max(0, Math.round((to.getTime() - from.getTime()) / 86400000))
})

const previewPrice = ref<number | null>(null)

const loadPreview = async () => {
  if (!dateFrom.value || !dateTo.value) { previewPrice.value = null; return }
  try {
    const data = await api.get<{ total_price: number | null }>(
      `/bookings/preview?service_id=${props.serviceId}&date_from=${dateFrom.value}&date_to=${dateTo.value}`
    )
    previewPrice.value = data.total_price
  } catch {
    previewPrice.value = null
  }
}

watch([() => dateFrom.value, () => dateTo.value], ([from, to]) => {
  if (from && to) loadPreview()
  else previewPrice.value = null
})

const formatPrice = (val: number) =>
  Number(val).toLocaleString('sk-SK', { minimumFractionDigits: 0, maximumFractionDigits: 2 })

// Load addon services once
const loadAddonServices = async () => {
  try {
    addonServices.value = await api.get<AddonService[]>('/addon-services')
  } catch {}
}

// Load blocked days for current month
const loadBlockedDays = async (year: number, month: number) => {
  try {
    const data = await api.get<{ blocked_days: string[] }>(
      `/bookings/blocked-days?caravan_id=${props.serviceId}&year=${year}&month=${month}`
    )
    const incoming = data.blocked_days || []
    const existing = blockedDays.value.filter(d => {
      const parts = d.split('-')
      return !(parseInt(parts[0]) === year && parseInt(parts[1]) === month)
    })
    blockedDays.value = [...existing, ...incoming]
  } catch {}
}

onMounted(async () => {
  await api.getCsrfCookie()
  const now = new Date()
  await Promise.all([
    loadBlockedDays(now.getFullYear(), now.getMonth() + 1),
    loadAddonServices(),
  ])
})

const onMonthChange = (year: number, month: number) => {
  loadBlockedDays(year, month)
}

const onDateFromUpdate = (val: string) => {
  dateFrom.value = val
  dateTo.value   = ''
}

const onDateToUpdate = (val: string) => {
  dateTo.value = val
}

const resetDates = () => {
  dateFrom.value     = ''
  dateTo.value       = ''
  previewPrice.value = null
  selectedAddonIds.value = []
}

const formatDate = (dateStr: string | null | undefined) => {
  if (!dateStr) return ''
  return new Date(dateStr + 'T00:00:00').toLocaleDateString('sk-SK', {
    day: 'numeric', month: 'long', year: 'numeric',
  })
}

const submit = async () => {
  submitError.value = ''
  submitting.value  = true
  try {
    const payload: Record<string, any> = {
      service_id:        props.serviceId,
      date_from:         dateFrom.value,
      date_to:           dateTo.value,
      customer_name:     form.value.customer_name,
      customer_phone:    form.value.customer_phone,
      customer_email:    form.value.customer_email,
      note:              form.value.note    || null,
      billing_street:    form.value.billing_street || null,
      billing_city:      form.value.billing_city   || null,
      billing_zip:       form.value.billing_zip    || null,
      addon_service_ids: selectedAddonIds.value,
    }
    if (wantInvoice.value) {
      payload.billing_ico     = form.value.billing_ico     || null
      payload.billing_company = form.value.billing_company || null
      payload.billing_dic     = form.value.billing_dic     || null
    }
    const response = await api.post<any>('/bookings', payload)
    submittedData.value = response
    success.value = true
  } catch (e: any) {
    submitError.value = e.message || 'Chyba pri odosielaní. Skúste znova.'
  } finally {
    submitting.value = false
  }
}

const reset = () => {
  success.value       = false
  submittedData.value = null
  submitError.value   = ''
  dateFrom.value      = ''
  dateTo.value        = ''
  previewPrice.value  = null
  selectedAddonIds.value = []
  wantInvoice.value = false
  form.value = {
    customer_name: '', customer_phone: '', customer_email: '', note: '',
    billing_ico: '', billing_company: '', billing_dic: '',
    billing_street: '', billing_city: '', billing_zip: '',
  }
}
</script>

<style scoped>
.booking-success-icon i {
  font-size: 3rem;
  color: #198754;
}

/* Selection hint bar */
.selection-hint {
  display: flex;
  align-items: center;
  padding: 0.45rem 0.75rem;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  font-weight: 500;
}
.hint-from {
  background: rgba(139, 26, 26, 0.07);
  color: var(--jg-primary);
  border: 1px solid rgba(139, 26, 26, 0.2);
}
.hint-to {
  background: rgba(45, 106, 79, 0.07);
  color: #2d6a4f;
  border: 1px solid rgba(45, 106, 79, 0.25);
}

/* Reset/edit button inside hint */
.btn-reset-dates {
  background: none;
  border: none;
  padding: 0 0.25rem;
  font-size: 0.8rem;
  cursor: pointer;
  opacity: 0.7;
  line-height: 1;
}
.btn-reset-dates:hover { opacity: 1; }

/* Selected range display */
.selected-range {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.25rem;
  font-size: 0.875rem;
}

.range-badge {
  display: inline-flex;
  align-items: center;
  padding: 0.25rem 0.6rem;
  border-radius: 1rem;
  font-weight: 600;
  white-space: nowrap;
}
.range-badge.from {
  background-color: var(--jg-primary);
  color: #fff;
}
.range-badge.to {
  background-color: #2d6a4f;
  color: #fff;
}
.range-badge.to--empty {
  background-color: #e9ecef;
  color: #888;
  font-weight: 400;
}

.days-badge {
  font-size: 0.8125rem;
  font-weight: 600;
  color: #555;
  background: #f0f0f0;
  padding: 0.2rem 0.6rem;
  border-radius: 1rem;
  white-space: nowrap;
}

/* Addon services */
.addon-services-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.addon-service-item {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 0.65rem 0.75rem;
  border: 1.5px solid #dee2e6;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: border-color 0.15s, background 0.15s;
  user-select: none;
}

.addon-service-item:hover {
  border-color: #adb5bd;
  background: #f8f9fa;
}

.addon-service-item--selected {
  border-color: #2d6a4f;
  background: rgba(45, 106, 79, 0.06);
}

.addon-service-item--premium.addon-service-item--selected {
  border-color: var(--jg-primary);
  background: rgba(139, 26, 26, 0.05);
}

.addon-check {
  flex-shrink: 0;
  padding-top: 0.1rem;
}

.addon-checkbox {
  width: 18px;
  height: 18px;
  border: 2px solid #adb5bd;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.15s, border-color 0.15s;
}

.addon-checkbox.checked {
  background: #2d6a4f;
  border-color: #2d6a4f;
  color: #fff;
}

.addon-service-item--premium .addon-checkbox.checked {
  background: var(--jg-primary);
  border-color: var(--jg-primary);
}

.addon-body {
  flex: 1;
  min-width: 0;
}

.addon-name {
  font-size: 0.9rem;
  font-weight: 600;
  line-height: 1.3;
}

.addon-desc {
  font-size: 0.8rem;
  color: #6c757d;
  margin-top: 0.15rem;
  line-height: 1.4;
}

.addon-premium-badge {
  display: inline-block;
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  background: var(--jg-primary);
  color: #fff;
  border-radius: 0.25rem;
  padding: 0.1rem 0.35rem;
  margin-left: 0.4rem;
  vertical-align: middle;
}

.addon-price {
  flex-shrink: 0;
  font-size: 0.85rem;
  font-weight: 700;
  color: #333;
  white-space: nowrap;
  padding-top: 0.1rem;
}

.addon-summary {
  padding: 0.4rem 0.75rem;
  background: #f0f0f0;
  border-radius: 0.375rem;
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.25rem;
}

/* Platobný plán v success stave */
.success-pp-card { background: #f8f9fa; border-radius: 0.5rem; border: 1px solid #e5e5e5; overflow: hidden; }
.success-pp-head { padding: 0.6rem 0.9rem; background: var(--jg-primary); color: #fff; font-weight: 600; font-size: 0.8rem; }
.success-pp-body { padding: 0.75rem 0.9rem; }
.success-pp-row { display: flex; justify-content: space-between; align-items: baseline; gap: 0.5rem; padding: 0.15rem 0; font-size: 0.85rem; }
.success-pp-lbl { color: #777; flex-shrink: 0; }
.success-pp-val { font-weight: 700; color: #1a1a1a; }
.success-pp-deadline { color: #c0392b; font-weight: 600; }
</style>
