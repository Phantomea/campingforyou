<template>
  <div>
    <div v-if="saved" class="alert alert-success mb-3">
      <i class="bi bi-check-circle me-2"></i>
      Nastavení byla uložena.
    </div>

    <form @submit.prevent="saveSettings">
      <ul class="nav nav-tabs mb-0" role="tablist">
        <li class="nav-item" v-for="tab in tabs" :key="tab.id">
          <button
            class="nav-link"
            :class="{ active: activeTab === tab.id }"
            type="button"
            @click="activeTab = tab.id"
          >
            <i :class="`bi ${tab.icon} me-2`"></i>{{ tab.label }}
          </button>
        </li>
      </ul>

      <div class="card border-top-0 rounded-top-0">
        <div class="card-body">

          <!-- Základní informace -->
          <div v-show="activeTab === 'basic'">
            <div class="mb-3">
              <label class="form-label">Název webu</label>
              <input v-model="form.site_name" type="text" class="form-control" required />
            </div>
            <div class="mb-3">
              <label class="form-label">Popis webu</label>
              <textarea v-model="form.site_description" class="form-control" rows="3" required></textarea>
            </div>
          </div>

          <!-- Kontaktní údaje -->
          <div v-show="activeTab === 'contact'">
            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">Telefon</label>
                <input v-model="form.phone" type="text" class="form-control" required />
              </div>
              <div class="col-md-6">
                <label class="form-label">E-mail</label>
                <input v-model="form.email" type="email" class="form-control" required />
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Adresa</label>
              <textarea v-model="form.address" class="form-control" rows="3" required></textarea>
            </div>
          </div>

          <!-- Otevírací hodiny -->
          <div v-show="activeTab === 'hours'">
            <div v-for="(hours, day) in form.opening_hours" :key="day" class="row mb-2 align-items-center">
              <div class="col-3 col-md-2">
                <label class="form-label mb-0 fw-semibold">{{ day }}</label>
              </div>
              <div class="col-9 col-md-4">
                <input v-model="form.opening_hours[day]" type="text" class="form-control" placeholder="např. 8:00 - 17:00" required />
              </div>
            </div>
          </div>

          <!-- Rezervace -->
          <div v-show="activeTab === 'bookings'">
            <div class="row mb-3">
              <div class="col-md-4">
                <label class="form-label">
                  Uzávěrka objednávek (čas)
                  <span class="text-muted small ms-1">— prázdné = kdykoli</span>
                </label>
                <input v-model="form.booking_cutoff_time" type="time" class="form-control" />
                <div class="form-text">Po tomto čase nelze rezervovat na dnešní den.</div>
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-md-4">
                <label class="form-label">Čas převzetí karavanu</label>
                <input v-model="form.pickup_time" type="time" class="form-control" required />
                <div class="form-text">Nejdřívější čas převzetí v den příjezdu.</div>
              </div>
              <div class="col-md-4">
                <label class="form-label">Čas vrácení karavanu</label>
                <input v-model="form.return_time" type="time" class="form-control" required />
                <div class="form-text">Nejpozdější čas vrácení v den odjezdu.</div>
              </div>
            </div>

            <hr />
            <h6 class="fw-semibold mb-3">Platobné podmienky rezervácie</h6>
            <div class="row mb-3">
              <div class="col-md-4">
                <label class="form-label fw-semibold">Splatnosť rezervačnej zálohy (dni)</label>
                <input v-model="form.payment_deadline_days" type="number" min="1" max="60" class="form-control" placeholder="5" required />
                <div class="form-text">Počet dní do zaplatenia rezervačnej zálohy (nie zálohy za karavan).</div>
              </div>
              <div class="col-md-4">
                <label class="form-label fw-semibold">Splatnosť celej sumy (dni)</label>
                <input v-model="form.full_payment_deadline_days" type="number" min="0" max="365" class="form-control" placeholder="14" />
                <div class="form-text">Počet dní od vytvorenia rezervácie, do ktorých musí byť zaplatená celá suma. Ak je prevzatie karavanu skôr, platí sa celá suma naraz.</div>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold" for="upfrontPercent">Rezervačná záloha</label>
              <div class="input-group" style="max-width: 160px">
                <input
                  v-model.number="form.upfront_payment_percent"
                  id="upfrontPercent"
                  type="number"
                  min="0"
                  max="100"
                  step="1"
                  class="form-control"
                  required
                  @input="clampUpfront"
                />
                <span class="input-group-text">%</span>
              </div>
              <div class="form-text">Celé číslo 0 – 100. Koľko percent tvorí záloha z celkovej sumy, ktorú musí zaplatiť zákazník vopred pri rezervácii. Vzorec: (Cena karavanu × počet dní) a z toho %. Zvyšné percentá musí doplatiť do dátumu prevzatia a na mieste zložiť zálohu.</div>
            </div>
            <div class="alert alert-warning d-flex align-items-start gap-2 mb-0">
              <i class="bi bi-exclamation-triangle-fill flex-shrink-0 mt-1"></i>
              <span>
                Rezervačná záloha <strong>NIE JE</strong> záloha za karavan.
                Záloha za karavan (nastavená pri každom karavane zvlášť) sa platí na mieste pri prevzatí karavanu a slúži ako finančná záruka. Rezervačná záloha je suma, ktorú zákazník uhradí vopred pri vytvorení rezervácie.
              </span>
            </div>

            <hr />
            <h6 class="fw-semibold mb-1">Sezóna</h6>
            <p class="text-muted small mb-3">
              Ak je nakonfigurovaná sezóna a karavan má nastavené sezónne ceny, systém automaticky vypočíta cenu podľa dní v sezóne a mimo nej.
              Nechajte prázdne ak sezónne ceny nepoužívate.
            </p>
            <div class="row g-3 align-items-start">
              <div class="col-md-auto">
                <label class="form-label">Začiatok sezóny</label>
                <div class="d-flex gap-2">
                  <div>
                    <select v-model.number="form.season_start_month" class="form-select" style="min-width: 120px">
                      <option :value="null">— mesiac —</option>
                      <option v-for="(m, i) in monthNames" :key="i" :value="i + 1">{{ m }}</option>
                    </select>
                  </div>
                  <div style="width: 75px">
                    <input
                      v-model.number="form.season_start_day"
                      type="number" min="1" max="31" class="form-control"
                      placeholder="deň"
                      :class="{ 'is-invalid': seasonStartDayError }"
                    />
                  </div>
                </div>
                <div v-if="seasonStartDayError" class="invalid-feedback d-block">{{ seasonStartDayError }}</div>
              </div>
              <div class="col-md-auto">
                <label class="form-label">Koniec sezóny</label>
                <div class="d-flex gap-2">
                  <div>
                    <select v-model.number="form.season_end_month" class="form-select" style="min-width: 120px">
                      <option :value="null">— mesiac —</option>
                      <option v-for="(m, i) in monthNames" :key="i" :value="i + 1">{{ m }}</option>
                    </select>
                  </div>
                  <div style="width: 75px">
                    <input
                      v-model.number="form.season_end_day"
                      type="number" min="1" max="31" class="form-control"
                      placeholder="deň"
                      :class="{ 'is-invalid': seasonEndDayError }"
                    />
                  </div>
                </div>
                <div v-if="seasonEndDayError" class="invalid-feedback d-block">{{ seasonEndDayError }}</div>
              </div>
              <div v-if="seasonPreview" class="col-12">
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                  <i class="bi bi-sun me-1"></i>Sezóna: {{ seasonPreview }}
                </span>
              </div>
            </div>
          </div>

          <!-- Platební údaje -->
          <div v-show="activeTab === 'payment'">
            <div class="row mb-3">
              <div class="col-md-7">
                <label class="form-label">IBAN</label>
                <input v-model="form.iban" type="text" class="form-control" placeholder="CZ65 0800 0000 0000 0000 0001" required />
              </div>
              <div class="col-md-3">
                <label class="form-label">BIC / SWIFT</label>
                <input v-model="form.bank_bic" type="text" class="form-control" placeholder="GIBACZPX" required />
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">Název banky</label>
                <input v-model="form.bank_name" type="text" class="form-control" placeholder="Česká spořitelna" required />
              </div>
              <div class="col-md-2">
                <label class="form-label">Kód banky</label>
                <input v-model="form.bank_code" type="text" class="form-control" placeholder="0800" />
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-md-8">
                <label class="form-label">Majiteľ účtu <span class="text-muted fw-normal">(presný názov, povinné pre EU prevody)</span></label>
                <input v-model="form.account_holder_name" type="text" class="form-control" placeholder="Meno Priezvisko alebo Firma s.r.o." required />
              </div>
            </div>
          </div>

          <!-- Sociální sítě -->
          <div v-show="activeTab === 'social'">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">
                  <i class="bi bi-facebook me-1"></i>
                  Facebook URL
                </label>
                <input v-model="form.facebook" type="url" class="form-control" placeholder="https://facebook.com/..." />
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">
                  <i class="bi bi-instagram me-1"></i>
                  Instagram URL
                </label>
                <input v-model="form.instagram" type="url" class="form-control" placeholder="https://instagram.com/..." />
              </div>
            </div>
          </div>

        </div>
        <div class="card-footer bg-light">
          <button type="submit" class="btn btn-primary" :disabled="saving">
            <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
            <i v-else class="bi bi-check-lg me-1"></i>
            {{ saving ? 'Ukládám...' : 'Uložit nastavení' }}
          </button>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: 'admin',
  middleware: 'auth',
})

const api = useApi()
const route = useRoute()
const router = useRouter()
const saving = ref(false)
const saved = ref(false)

const tabs = [
  { id: 'basic',    label: 'Základní',    icon: 'bi-info-circle' },
  { id: 'contact',  label: 'Kontakt',     icon: 'bi-telephone' },
  { id: 'hours',    label: 'Hodiny',      icon: 'bi-clock' },
  { id: 'bookings', label: 'Rezervace',   icon: 'bi-calendar-check' },
  { id: 'payment',  label: 'Platba',      icon: 'bi-credit-card' },
  { id: 'social',   label: 'Sociální',    icon: 'bi-share' },
]

const validTabIds = tabs.map(t => t.id)
const activeTab = computed({
  get: () => {
    const q = route.query.tab as string
    return validTabIds.includes(q) ? q : 'basic'
  },
  set: (id: string) => {
    router.replace({ query: { ...route.query, tab: id } })
  },
})

const form = ref({
  site_name: '',
  site_description: '',
  phone: '',
  email: '',
  address: '',
  opening_hours: {
    'Po - Pá': '',
    'So': '',
    'Ne': '',
  } as Record<string, string>,
  facebook: '',
  instagram: '',
  booking_cutoff_time: '',
  payment_deadline_days: '5',
  full_payment_deadline_days: '14',
  pickup_time: '10:00',
  return_time: '18:00',
  iban: '',
  bank_bic: '',
  bank_name: '',
  bank_code: '',
  account_holder_name: '',
  upfront_payment_percent: 100,
  season_start_month: null as number | null,
  season_start_day: null as number | null,
  season_end_month: null as number | null,
  season_end_day: null as number | null,
})

const loadSettings = async () => {
  try {
    const data = await api.get<Record<string, any>>('/admin/settings')
    form.value = {
      site_name: data.site_name || '',
      site_description: data.site_description || '',
      phone: data.phone || '',
      email: data.email || '',
      address: data.address || '',
      opening_hours: data.opening_hours || {
        'Po - Pá': '8:00 - 17:00',
        'So': '9:00 - 12:00',
        'Ne': 'Zavřeno',
      },
      facebook: data.facebook || '',
      instagram: data.instagram || '',
      booking_cutoff_time: data.booking_cutoff_time || '',
      payment_deadline_days: data.payment_deadline_days || '5',
      full_payment_deadline_days: data.full_payment_deadline_days || '14',
      pickup_time: data.pickup_time || '10:00',
      return_time: data.return_time || '18:00',
      iban: data.iban || '',
      bank_bic: data.bank_bic || '',
      bank_name: data.bank_name || '',
      bank_code: data.bank_code || '',
      account_holder_name: data.account_holder_name || '',
      upfront_payment_percent: parseInt(data.upfront_payment_percent) || 100,
      season_start_month: parseInt(data.season_start_month) || null,
      season_start_day:   parseInt(data.season_start_day)   || null,
      season_end_month:   parseInt(data.season_end_month)   || null,
      season_end_day:     parseInt(data.season_end_day)     || null,
    }
  } catch {
    // Use defaults
  }
}

const saveSettings = async () => {
  saving.value = true
  saved.value = false
  try {
    await api.put('/admin/settings', form.value)
    saved.value = true
    setTimeout(() => { saved.value = false }, 3000)
  } catch (e: any) {
    alert(e.message || 'Chyba pri ukladaní')
  } finally {
    saving.value = false
  }
}

const clampUpfront = () => {
  const v = form.value.upfront_payment_percent
  if (v < 0)   form.value.upfront_payment_percent = 0
  if (v > 100) form.value.upfront_payment_percent = 100
}

const monthNames = ['Január', 'Február', 'Marec', 'Apríl', 'Máj', 'Jún', 'Júl', 'August', 'September', 'Október', 'November', 'December']

const daysInMonth = (month: number | null) => {
  if (!month) return 31
  return new Date(2000, month, 0).getDate()
}

const seasonStartDayError = computed(() => {
  const d = form.value.season_start_day
  if (!d) return null
  const max = daysInMonth(form.value.season_start_month)
  if (d < 1 || d > max) return `Deň musí byť 1 – ${max}`
  return null
})

const seasonEndDayError = computed(() => {
  const d = form.value.season_end_day
  if (!d) return null
  const max = daysInMonth(form.value.season_end_month)
  if (d < 1 || d > max) return `Deň musí byť 1 – ${max}`
  return null
})

const seasonPreview = computed(() => {
  const { season_start_month: sm, season_start_day: sd, season_end_month: em, season_end_day: ed } = form.value
  if (!sm || !sd || !em || !ed) return null
  return `${String(sd).padStart(2, '0')}. ${monthNames[sm - 1]} – ${String(ed).padStart(2, '0')}. ${monthNames[em - 1]}`
})

onMounted(loadSettings)
</script>
