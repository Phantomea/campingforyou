<template>
  <div class="wiz-summary" v-if="service">
    <div class="wiz-summary__title">Přehled rezervace</div>
    <div class="wiz-summary__item">
      <span class="wiz-summary__lbl"><i class="bi bi-house-door me-1"></i>Karavan</span>
      <span class="wiz-summary__val fw-bold">{{ service.title }}</span>
    </div>
    <div class="wiz-summary__item" v-if="service.capacity">
      <span class="wiz-summary__lbl"><i class="bi bi-people-fill me-1"></i>Kapacita</span>
      <span class="wiz-summary__val">{{ service.capacity }} os.</span>
    </div>
    <hr class="my-2" />
    <div class="wiz-summary__item">
      <span class="wiz-summary__lbl"><i class="bi bi-calendar-event me-1"></i>Převzetí</span>
      <span class="wiz-summary__val">{{ formatDate(dateFrom) }}<span v-if="pickupTime" class="wiz-summary__time"> od {{ pickupTime }}</span></span>
    </div>
    <div class="wiz-summary__item">
      <span class="wiz-summary__lbl"><i class="bi bi-calendar-check me-1"></i>Vrácení</span>
      <span class="wiz-summary__val">{{ formatDate(dateTo) }}<span v-if="returnTime" class="wiz-summary__time"> do {{ returnTime }}</span></span>
    </div>
    <div class="wiz-summary__item" v-if="totalDays > 0">
      <span class="wiz-summary__lbl"><i class="bi bi-clock me-1"></i>Počet dní</span>
      <span class="wiz-summary__val">{{ totalDays }} {{ daysWord(totalDays) }}</span>
    </div>
    <template v-if="totalPrice">
      <hr class="my-2" />
      <div v-if="deposit && deposit > 0" class="wiz-summary__item">
        <span class="wiz-summary__lbl"><i class="bi bi-shield-lock me-1"></i>Záloha <span class="wiz-summary__note-inline">(pri prevzatí — teraz neplatíte)</span></span>
        <span class="wiz-summary__val wiz-summary__deposit">{{ formatPrice(deposit) }} Kč</span>
      </div>
      <div v-if="singlePayment" class="wiz-summary__item wiz-summary__item--total">
        <span class="wiz-summary__lbl fw-bold">Celková cena</span>
        <span class="wiz-summary__val wiz-summary__total">{{ formatPrice(totalPrice) }} Kč</span>
      </div>
      <template v-if="!singlePayment && upfrontAmount && remainingAmount">
        <hr class="my-2" />
        <div class="wiz-summary__item">
          <span class="wiz-summary__lbl">
            Záloha {{ upfrontPct }}%
            <span v-if="paymentDeadline" class="wiz-summary__note-inline">do {{ formatDateShort(paymentDeadline) }}</span>
          </span>
          <span class="wiz-summary__val">{{ formatPrice(upfrontAmount) }} Kč</span>
        </div>
        <div class="wiz-summary__item">
          <span class="wiz-summary__lbl">
            Doplatok
            <span v-if="fullPaymentDeadline" class="wiz-summary__note-inline">do {{ formatDateShort(fullPaymentDeadline) }}</span>
          </span>
          <span class="wiz-summary__val">{{ formatPrice(remainingAmount) }} Kč</span>
        </div>
        <hr class="my-2" />
        <div class="wiz-summary__item">
          <span class="wiz-summary__lbl">Celková cena</span>
          <span class="wiz-summary__val">{{ formatPrice(totalPrice) }} Kč</span>
        </div>
        <div class="wiz-summary__item wiz-summary__item--total">
          <span class="wiz-summary__lbl fw-bold">Teraz zaplatíte</span>
          <span class="wiz-summary__val wiz-summary__total">{{ formatPrice(upfrontAmount) }} Kč</span>
        </div>
      </template>
    </template>
  </div>
  <div class="wiz-summary wiz-summary--empty" v-else>
    <i class="bi bi-house-door fs-2 mb-2 opacity-50"></i>
    <p class="small text-muted mb-0">Přehled se zobrazí po výběru karavanu</p>
  </div>
</template>

<script setup lang="ts">
const props = defineProps<{
  service:              { title: string; capacity?: number } | null
  dateFrom:             string
  dateTo:               string
  totalDays:            number
  totalPrice:           number | null
  deposit?:             number | null
  upfrontAmount?:       number | null
  remainingAmount?:     number | null
  paymentDeadline?:     string | null
  fullPaymentDeadline?: string | null
  singlePayment?:       boolean
  pickupTime?:          string | null
  returnTime?:          string | null
}>()

const upfrontPct = computed(() => {
  if (!props.upfrontAmount || !props.totalPrice) return 0
  return Math.round(props.upfrontAmount / props.totalPrice * 100)
})

const formatPrice = (n: number | null | undefined) =>
  n != null ? new Intl.NumberFormat('cs-CZ', { maximumFractionDigits: 0 }).format(n) : '—'

const formatDate = (s: string) =>
  s ? new Date(s).toLocaleDateString('cs-CZ', { day: 'numeric', month: 'long', year: 'numeric' }) : '—'

const formatDateShort = (s: string) =>
  s ? new Date(s + 'T00:00:00').toLocaleDateString('cs-CZ', { day: 'numeric', month: 'long' }) : ''

const daysWord = (n: number) => n === 1 ? 'den' : n < 5 ? 'dny' : 'dní'
</script>

<style scoped>
.wiz-summary {
  background: #fff; border: 1px solid #e5e5e5; border-radius: 0.75rem;
  padding: 1.5rem; position: sticky; top: 5rem;
}
.wiz-summary--empty {
  display: flex; flex-direction: column; align-items: center;
  justify-content: center; min-height: 150px; color: #aaa;
}
.wiz-summary__title {
  font-weight: 700; font-size: 0.875rem; text-transform: uppercase;
  letter-spacing: 0.05em; color: #888; margin-bottom: 1rem;
}
.wiz-summary__item {
  display: flex; justify-content: space-between; align-items: flex-start;
  gap: 0.5rem; padding: 0.35rem 0; font-size: 0.9rem;
}
.wiz-summary__item--total { padding-top: 0.5rem; }
.wiz-summary__lbl { color: #666; flex-shrink: 0; }
.wiz-summary__val { text-align: right; color: #1a1a1a; }
.wiz-summary__total { font-size: 1.3rem; font-weight: 800; color: #2d6a4f; }
.wiz-summary__time { font-size: 0.8rem; color: #888; font-weight: 400; }
.wiz-summary__deposit { font-weight: 600; color: #555; }
.wiz-summary__note-inline { font-size: 0.75rem; font-weight: 400; color: #999; }
</style>
