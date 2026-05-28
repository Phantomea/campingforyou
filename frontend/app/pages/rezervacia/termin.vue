<template>
  <div>
    <WizardSteps :current="2" />

    <section class="wiz-body">
      <div class="container">
        <h2 class="wiz-step-heading">Vyberte termín</h2>
        <div class="row g-4 mt-1">
          <!-- Kalendář -->
          <div class="col-lg-7">
            <div class="sel-hint mb-3" :class="selHintClass">
              <template v-if="selectionPhase === 'from'">
                <i class="bi bi-calendar-event me-2"></i>
                Klikněte na <strong>datum převzetí</strong> karavanu
              </template>
              <template v-else-if="selectionPhase === 'to'">
                <i class="bi bi-calendar-check me-2"></i>
                Nyní klikněte na <strong>datum vrácení</strong>
                <button type="button" class="sel-hint__reset ms-auto" @click="resetDates">
                  <i class="bi bi-x-circle me-1"></i>Zrušit
                </button>
              </template>
              <template v-else>
                <i class="bi bi-check-circle-fill me-2"></i>
                <span>Termín vybrán</span>
                <button type="button" class="sel-hint__reset ms-auto" @click="resetDates">
                  <i class="bi bi-pencil me-1"></i>Změnit
                </button>
              </template>
            </div>

            <div v-if="dateFrom || dateTo" class="sel-range mb-3">
              <span class="sel-badge sel-badge--from">
                <i class="bi bi-box-arrow-right me-1"></i>{{ dateFrom ? formatDate(dateFrom) : '—' }}
              </span>
              <i class="bi bi-arrow-right mx-2 text-muted"></i>
              <span class="sel-badge sel-badge--to" :class="{ 'sel-badge--empty': !dateTo }">
                <i class="bi bi-box-arrow-left me-1"></i>{{ dateTo ? formatDate(dateTo) : '...' }}
              </span>
              <span v-if="preview?.total_days" class="sel-days-badge ms-3">
                {{ preview.total_days }} {{ daysWord(preview.total_days) }}
                <template v-if="preview.total_price"> · {{ new Intl.NumberFormat('cs-CZ', { maximumFractionDigits: 0 }).format(preview.total_price) }} Kč</template>
              </span>
            </div>

            <BookingCalendar
              :dateFrom="dateFrom"
              :dateTo="dateTo"
              :blockedDays="blockedDays"
              :selectingEnd="selectionPhase === 'to'"
              :minDate="minDate"
              @update:dateFrom="onDateFrom"
              @update:dateTo="onDateTo"
              @monthChange="onMonthChange"
            />
          </div>

          <!-- Summary -->
          <div class="col-lg-5">
            <BookingSummary
              :service="service"
              :dateFrom="dateFrom"
              :dateTo="dateTo"
              :totalDays="preview?.total_days ?? 0"
              :totalPrice="preview?.total_price ?? null"
              :deposit="preview?.deposit ?? null"
              :upfrontAmount="preview?.upfront_amount ?? null"
              :remainingAmount="preview?.remaining_amount ?? null"
              :paymentDeadline="preview?.payment_deadline ?? null"
              :fullPaymentDeadline="preview?.full_payment_deadline ?? null"
              :singlePayment="preview?.single_payment ?? true"
              :pickupTime="settings.pickup_time"
              :returnTime="settings.return_time"
            />
          </div>
        </div>

        <div class="wiz-nav mt-4">
          <NuxtLink to="/rezervacia" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Zpět
          </NuxtLink>
          <button class="btn btn-primary btn-lg px-5" :disabled="selectionPhase !== 'done'" @click="next">
            Pokračovat <i class="bi bi-arrow-right ms-2"></i>
          </button>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
interface Service {
  id: number; title: string; capacity?: number
  image?: string | null; images?: string[] | null
}
interface Preview {
  total_days: number
  total_price: number | null
  deposit: number | null
  upfront_amount: number
  remaining_amount: number
  payment_deadline: string
  full_payment_deadline: string | null
  single_payment: boolean
}

const route  = useRoute()
const api    = useApi()
const karavanId = Number(route.query.karavan)

if (!karavanId) { await navigateTo('/rezervacia') }

const [{ data: serviceData }, { data: settingsData }] = await Promise.all([
  useAsyncData(`wiz-service-${karavanId}`, () =>
    api.get<Service[]>('/services').then((list: any) => (list as Service[]).find((s: Service) => s.id === karavanId) ?? null)
  ),
  useAsyncData('wiz-settings', () => api.get<Record<string, string>>('/settings/public'), { default: () => ({}) }),
])

if (!serviceData.value) { await navigateTo('/rezervacia') }

const service  = computed(() => serviceData.value!)
const settings = computed(() => (settingsData.value || {}) as Record<string, string>)

const dateFrom    = ref('')
const dateTo      = ref('')
const blockedDays = ref<string[]>([])
const preview     = ref<Preview | null>(null)
const minDate     = ref<string>(new Date().toISOString().slice(0, 10))

const selectionPhase = computed(() => {
  if (!dateFrom.value) return 'from'
  if (!dateTo.value)   return 'to'
  return 'done'
})
const selHintClass = computed(() => ({
  'sel-hint--from': selectionPhase.value === 'from',
  'sel-hint--to':   selectionPhase.value === 'to',
  'sel-hint--done': selectionPhase.value === 'done',
}))

const formatDate = (s: string | null | undefined) =>
  s ? new Date(s + 'T00:00:00').toLocaleDateString('cs-CZ', { day: 'numeric', month: 'long', year: 'numeric' }) : ''
const daysWord = (n: number) => n === 1 ? 'den' : n < 5 ? 'dny' : 'dní'

const resetDates = () => { dateFrom.value = ''; dateTo.value = '' }
const onDateFrom = (v: string) => { dateFrom.value = v; dateTo.value = '' }
const onDateTo   = (v: string) => { dateTo.value = v }

const fetchPreview = async () => {
  if (!dateFrom.value || !dateTo.value) { preview.value = null; return }
  try {
    preview.value = await api.get<Preview>(
      `/bookings/preview?service_id=${karavanId}&date_from=${dateFrom.value}&date_to=${dateTo.value}`
    )
  } catch { preview.value = null }
}
watch([dateFrom, dateTo], fetchPreview)

const loadBlockedDays = async (year: number, month: number) => {
  try {
    const data = await api.get<{ blocked_days: string[]; min_date: string }>(
      `/bookings/blocked-days?caravan_id=${karavanId}&year=${year}&month=${month}`
    )
    const incoming = data.blocked_days || []
    const existing = blockedDays.value.filter(d => {
      const [y, mo] = d.split('-').map(Number)
      return !(y === year && mo === month)
    })
    blockedDays.value = [...existing, ...incoming]
    if (data.min_date) minDate.value = data.min_date
  } catch {}
}

onMounted(async () => {
  const now = new Date()
  await Promise.all(
    Array.from({ length: 3 }, (_, i) => {
      const d = new Date(now.getFullYear(), now.getMonth() + i, 1)
      return loadBlockedDays(d.getFullYear(), d.getMonth() + 1)
    })
  )
})

const onMonthChange = (year: number, month: number) => loadBlockedDays(year, month)

const next = () => {
  if (selectionPhase.value !== 'done') return
  navigateTo(`/rezervacia/udaje?karavan=${karavanId}&od=${dateFrom.value}&do=${dateTo.value}`)
}

useSeoMeta({ title: 'Rezervace — výběr termínu | CampingForYou' })
</script>

<style scoped>
.wiz-body { padding: 3rem 0 4rem; background: var(--jg-light, #f8f4ec); min-height: 60vh; }
.wiz-step-heading { font-size: 1.5rem; font-weight: 700; color: #1a1a1a; margin-bottom: 0.25rem; }
.wiz-nav { display: flex; align-items: center; justify-content: space-between; gap: 1rem; }

.sel-hint { display: flex; align-items: center; padding: 0.6rem 1rem; border-radius: 0.5rem; font-size: 0.9rem; font-weight: 500; }
.sel-hint--from { background: rgba(139,26,26,0.07); color: var(--jg-primary); border: 1px solid rgba(139,26,26,0.2); }
.sel-hint--to, .sel-hint--done { background: rgba(45,106,79,0.07); color: #2d6a4f; border: 1px solid rgba(45,106,79,0.25); }
.sel-hint__reset { background: none; border: none; cursor: pointer; font-size: 0.8rem; padding: 0 0.25rem; opacity: 0.8; color: inherit; }
.sel-hint__reset:hover { opacity: 1; }

.sel-range { display: flex; align-items: center; flex-wrap: wrap; gap: 0.25rem; }
.sel-badge { display: inline-flex; align-items: center; padding: 0.3rem 0.75rem; border-radius: 2rem; font-weight: 600; font-size: 0.875rem; white-space: nowrap; }
.sel-badge--from { background: var(--jg-primary); color: #fff; }
.sel-badge--to   { background: #2d6a4f; color: #fff; }
.sel-badge--empty { background: #e9ecef; color: #888; font-weight: 400; }
.sel-days-badge { font-size: 0.8rem; font-weight: 600; color: #555; background: #f0f0f0; padding: 0.25rem 0.65rem; border-radius: 2rem; white-space: nowrap; }

</style>
