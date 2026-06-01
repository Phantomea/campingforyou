<template>
  <div>
    <section class="status-hero">
      <div class="container">
        <h1 class="status-hero-title">Stav rezervace</h1>
        <p class="status-hero-sub">CampingForYou — pronájem karavanů</p>
      </div>
    </section>

    <section class="status-body">
      <div class="container">
        <div class="status-wrap">

          <!-- Načítání -->
          <div v-if="pending" class="text-center py-5">
            <div class="spinner-border text-secondary" role="status"></div>
            <p class="mt-3 text-muted">Načítání rezervace…</p>
          </div>

          <!-- Chyba / nenalezeno -->
          <div v-else-if="error" class="status-not-found">
            <i class="bi bi-exclamation-circle fs-1 text-muted mb-3 d-block"></i>
            <h2>Rezervace nebyla nalezena</h2>
            <p class="text-muted">Odkaz může být neplatný nebo vypršal.</p>
            <NuxtLink to="/" class="btn btn-outline-secondary mt-2">
              <i class="bi bi-house me-1"></i>Domů
            </NuxtLink>
          </div>

          <!-- Obsah -->
          <template v-else-if="booking">
            <!-- Status badge -->
            <div class="status-badge-wrap mb-4">
              <span class="status-badge" :class="`status-badge--${booking.status}`">
                <i class="bi me-2" :class="statusIcon"></i>{{ statusLabel }}
              </span>
            </div>

            <div class="row align-items-xl-start g-4">
              <!-- Detaily rezervace -->
              <div class="col-lg-7">
                <div class="status-card">
                  <div class="status-card__head">
                    <i class="bi bi-clipboard-check me-2"></i>Detaily rezervace
                  </div>
                  <div class="status-card__body">

                    <!-- Fotka karavanu -->
                    <div v-if="serviceImageUrl" class="caravan-photo-wrap mb-3">
                      <NuxtLink v-if="serviceDetailUrl" :to="serviceDetailUrl">
                        <img :src="serviceImageUrl" :alt="booking.service?.title ?? 'Karavan'" class="caravan-photo" />
                      </NuxtLink>
                      <img v-else :src="serviceImageUrl" :alt="booking.service?.title ?? 'Karavan'" class="caravan-photo" />
                    </div>

                    <table class="detail-table">
                      <tbody>
                        <tr>
                          <td class="detail-lbl">Karavan</td>
                          <td class="detail-val fw-bold">
                            <NuxtLink v-if="serviceDetailUrl" :to="serviceDetailUrl" class="detail-link">
                              {{ booking.service?.title }}
                            </NuxtLink>
                            <template v-else>{{ booking.service?.title }}</template>
                          </td>
                        </tr>
                        <tr>
                          <td class="detail-lbl">Převzetí</td>
                          <td class="detail-val">
                            {{ booking.date_from_formatted }}
                            <span v-if="settings.pickup_time" class="detail-time">
                              od {{ settings.pickup_time }}<template v-if="booking.latest_pickup_time"> do {{ booking.latest_pickup_time }}</template>
                            </span>
                          </td>
                        </tr>
                        <tr>
                          <td class="detail-lbl">Vrácení</td>
                          <td class="detail-val">
                            {{ booking.date_to_formatted }}
                            <span v-if="settings.return_time" class="detail-time">do {{ settings.return_time }}</span>
                          </td>
                        </tr>
                        <tr>
                          <td class="detail-lbl">Počet dní</td>
                          <td class="detail-val">{{ booking.total_days }}</td>
                        </tr>
                        <tr>
                          <td class="detail-lbl">Zákazník</td>
                          <td class="detail-val">{{ booking.customer_name }}</td>
                        </tr>
                        <tr v-if="booking.customer_phone">
                          <td class="detail-lbl">Telefón</td>
                          <td class="detail-val">
                            <a :href="`tel:${booking.customer_phone}`" class="detail-link">{{ booking.customer_phone }}</a>
                          </td>
                        </tr>
                        <tr v-if="booking.customer_email">
                          <td class="detail-lbl">E-mail</td>
                          <td class="detail-val">
                            <a :href="`mailto:${booking.customer_email}`" class="detail-link">{{ booking.customer_email }}</a>
                          </td>
                        </tr>
                        <tr v-if="booking.note">
                          <td class="detail-lbl">Poznámka</td>
                          <td class="detail-val detail-note">{{ booking.note }}</td>
                        </tr>
                        <template v-if="booking.billing_company || booking.billing_ico || booking.billing_street">
                          <tr class="detail-section-row">
                            <td colspan="2" class="detail-section-lbl">Fakturačné údaje</td>
                          </tr>
                          <tr v-if="booking.billing_company">
                            <td class="detail-lbl">Firma</td>
                            <td class="detail-val">{{ booking.billing_company }}</td>
                          </tr>
                          <tr v-if="booking.billing_ico">
                            <td class="detail-lbl">IČO</td>
                            <td class="detail-val">{{ booking.billing_ico }}</td>
                          </tr>
                          <tr v-if="booking.billing_dic">
                            <td class="detail-lbl">DIČ</td>
                            <td class="detail-val">{{ booking.billing_dic }}</td>
                          </tr>
                          <tr v-if="booking.billing_street">
                            <td class="detail-lbl">Adresa</td>
                            <td class="detail-val">
                              {{ booking.billing_street }}<br v-if="booking.billing_city" />
                              {{ [booking.billing_zip, booking.billing_city].filter(Boolean).join(' ') }}
                            </td>
                          </tr>
                        </template>
                        <tr class="detail-total-row">
                          <td class="detail-lbl fw-bold">Celková částka</td>
                          <td class="detail-val fw-bold detail-total">{{ formatPrice(booking.total_price) }} Kč</td>
                        </tr>
                        <template v-if="!booking.single_payment">
                          <tr>
                            <td class="detail-lbl detail-split-lbl">
                              Záloha <span class="detail-split-date">do {{ formatDeadline(booking.payment_deadline) }}</span>
                            </td>
                            <td class="detail-val detail-split-val">{{ formatPrice(booking.upfront_amount) }} Kč</td>
                          </tr>
                          <tr>
                            <td class="detail-lbl detail-split-lbl">
                              Doplatok <span class="detail-split-date">do {{ formatDeadline(booking.full_payment_deadline) }}</span>
                            </td>
                            <td class="detail-val detail-split-val">{{ formatPrice(booking.remaining_amount) }} Kč</td>
                          </tr>
                        </template>
                      </tbody>
                    </table>

                    <!-- Stavové info -->
                    <div class="status-info mt-4" :class="`status-info--${booking.status}`">
                      <i class="bi me-2" :class="statusIcon"></i>
                      <span>{{ statusDescription }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Platební info (jen pokud není cancelled) -->
              <div v-if="booking.status !== 'cancelled'" class="col-lg-5">
                <div class="status-card h-100">
                  <div class="status-card__head">
                    <i class="bi bi-credit-card me-2"></i>Platba převodem
                  </div>
                  <div class="status-card__body">

                    <!-- Tabs (len pri splite) -->
                    <div v-if="!booking.single_payment" class="qr-tabs mb-3">
                      <button :class="['qr-tab', activeQr === 1 && 'qr-tab--active']" @click="activeQr = 1">
                        1. Záloha
                      </button>
                      <button :class="['qr-tab', activeQr === 2 && 'qr-tab--active']" @click="activeQr = 2">
                        2. Doplatok
                      </button>
                    </div>

                    <!-- Suma + splatnosť (reaktívne) -->
                    <div class="pay-block mb-3">
                      <div class="pay-block__title">{{ activePayTitle }}</div>
                      <div class="pay-block__amount">{{ formatPrice(activePayAmount) }} Kč</div>
                      <div class="pay-block__deadline">
                        <i class="bi bi-calendar-check me-1"></i>splatné do {{ formatDeadline(activePayDeadline) }}
                      </div>
                    </div>

                    <!-- QR kód + stiahnutie -->
                    <div class="text-center mb-3">
                      <img v-if="activeQrDataUrl" :src="activeQrDataUrl" class="qr-img" />
                      <div class="d-flex flex-column gap-10px align-items-center mt-10px ">
                        <p class="small text-muted mb-0 align-self-center">Naskenujte QR kód aplikací vaší banky</p>

                        <a v-if="activeQrDataUrl" :href="activeQrDataUrl" :download="activeQrFilename"
                           class="btn btn-secondary">
                          <i class="bi bi-download me-1"></i>Stiahnuť
                        </a>
                      </div>
                    </div>

                    <hr class="my-3">

                    <!-- Bankové údaje — jeden blok, bez duplikátov -->
                    <div class="pay-manual">
                      <div class="pay-manual__row" v-if="settings.account_holder_name">
                        <span class="pay-manual__lbl">Príjemca</span>
                        <span class="pay-manual__val fw-bold">{{ settings.account_holder_name }}</span>
                      </div>
                      <div class="pay-manual__row" v-if="settings.iban">
                        <span class="pay-manual__lbl">IBAN</span>
                        <span class="pay-manual__val pay-manual__iban">{{ settings.iban }}</span>
                      </div>
                      <div class="pay-manual__row" v-if="settings.bank_name">
                        <span class="pay-manual__lbl">Banka</span>
                        <span class="pay-manual__val">
                          {{ settings.bank_name }}<template v-if="settings.bank_code"> ({{ settings.bank_code }})</template>
                        </span>
                      </div>
                      <div class="pay-manual__row" v-if="settings.bank_bic">
                        <span class="pay-manual__lbl">BIC / SWIFT</span>
                        <span class="pay-manual__val">{{ settings.bank_bic }}</span>
                      </div>
                      <div class="pay-manual__row">
                        <span class="pay-manual__lbl">Variabilný symbol</span>
                        <span class="pay-manual__val fw-bold">{{ booking.id }}</span>
                      </div>
                      <div class="pay-manual__row">
                        <span class="pay-manual__lbl">Poznámka</span>
                        <span class="pay-manual__val">{{ activeQrMsg }}</span>
                      </div>
                    </div>

                  </div>
                </div>
              </div>
            </div>

            <div class="text-center mt-5">
              <NuxtLink to="/" class="btn btn-outline-secondary">
                <i class="bi bi-house me-1"></i>Domů
              </NuxtLink>
            </div>
          </template>

        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
const route = useRoute()
const token = route.params.token as string
const api = useApi()

interface BookingStatus {
  id: number
  token: string
  status: 'pending' | 'confirmed' | 'cancelled'
  date_from: string
  date_to: string
  date_from_formatted: string
  date_to_formatted: string
  total_days: number
  total_price: number
  customer_name: string
  customer_phone: string | null
  customer_email: string | null
  note: string | null
  billing_company: string | null
  billing_ico: string | null
  billing_dic: string | null
  billing_street: string | null
  billing_city: string | null
  billing_zip: string | null
  created_at: string
  upfront_amount: number
  remaining_amount: number
  payment_deadline: string
  full_payment_deadline: string | null
  single_payment: boolean
  latest_pickup_time: string | null
  service: { id: number; title: string; slug: string | null; image: string | null; images: string[] | null } | null
}

const [{ data: bookingData, pending, error }, { data: settingsData }] = await Promise.all([
  useAsyncData(`booking-status-${token}`, () => api.get<BookingStatus>(`/bookings/status/${token}`)),
  useAsyncData('status-settings', () => api.get<Record<string, string>>('/settings/public'), { default: () => ({}) }),
])

const booking = computed(() => bookingData.value)
const settings = computed(() => (settingsData.value || {}) as Record<string, string>)

const statusLabel = computed(() => ({
  pending:   'Čeká na potvrzení',
  confirmed: 'Potvrzena',
  cancelled: 'Zrušena',
}[booking.value?.status ?? 'pending']))

const statusIcon = computed(() => ({
  pending:   'bi-hourglass-split',
  confirmed: 'bi-check-circle-fill',
  cancelled: 'bi-x-circle-fill',
}[booking.value?.status ?? 'pending']))

const statusDescription = computed(() => ({
  pending:   'Vaše rezervace čeká na potvrzení. Budeme vás informovat e-mailem.',
  confirmed: 'Rezervace je potvrzena. Těšíme se na vás!',
  cancelled: 'Tato rezervace byla zrušena. Kontaktujte nás pro více informací.',
}[booking.value?.status ?? 'pending']))

const formatPrice = (n: number | null | undefined) =>
  n != null ? new Intl.NumberFormat('cs-CZ', { maximumFractionDigits: 0 }).format(n) : '—'

const backendUrl = useRuntimeConfig().public.backendUrl as string
const serviceImageUrl = computed(() => {
  const svc = booking.value?.service
  if (!svc) return null
  if (svc.images?.length) return `${backendUrl}/storage/${svc.images[0]}`
  if (svc.image) return svc.image
  return null
})
const serviceDetailUrl = computed(() => {
  const slug = booking.value?.service?.slug
  return slug ? `/services/${slug}` : null
})

const formatDeadline = (dateStr: string | null | undefined) => {
  if (!dateStr) return ''
  return new Date(dateStr + 'T00:00:00')
    .toLocaleDateString('cs-CZ', { day: 'numeric', month: 'long', year: 'numeric' })
}

// QR kódy
const activeQr   = ref<1 | 2>(1)
const qrDataUrl1 = ref<string>('')
const qrDataUrl2 = ref<string>('')

const qrMsg1 = computed(() => {
  if (!booking.value) return ''
  const id = booking.value.id
  return booking.value.single_payment ? `Rezervace - ${id}` : `Zaloha - ${id}`
})
const qrMsg2 = computed(() => booking.value ? `Doplatek - ${booking.value.id}` : '')

const activePayTitle = computed(() => {
  if (booking.value?.single_payment) return 'Celková platba'
  return activeQr.value === 1 ? 'Rezervačná záloha' : 'Doplatok'
})
const activePayAmount = computed(() =>
  (!booking.value?.single_payment && activeQr.value === 2)
    ? (booking.value?.remaining_amount ?? 0)
    : (booking.value?.upfront_amount ?? 0)
)
const activePayDeadline = computed(() =>
  (!booking.value?.single_payment && activeQr.value === 2)
    ? booking.value?.full_payment_deadline ?? null
    : booking.value?.payment_deadline ?? null
)
const activeQrDataUrl = computed(() =>
  activeQr.value === 2 ? qrDataUrl2.value : qrDataUrl1.value
)
const activeQrFilename = computed(() => {
  const id = booking.value?.id ?? 'x'
  if (booking.value?.single_payment) return `qr-rezervace-${id}.png`
  return activeQr.value === 1 ? `qr-zaloha-${id}.png` : `qr-doplatek-${id}.png`
})
const activeQrMsg = computed(() =>
  activeQr.value === 2 ? qrMsg2.value : qrMsg1.value
)

const buildSpayd = (amount: number, msg: string): string => {
  const iban      = (settings.value.iban || 'CZ65 0800 0000 0000 0000 0001').replace(/\s/g, '')
  const rn        = settings.value.account_holder_name || ''
  const amountStr = Number(amount).toFixed(2)
  const id        = booking.value?.id ?? ''
  const parts     = [`SPD*1.0*ACC:${iban}`]
  if (rn) parts.push(`RN:${rn}`)
  parts.push(`AM:${amountStr}`, `CC:CZK`, `X-VS:${id}`, `MSG:${msg}`)
  return parts.join('*')
}

onMounted(async () => {
  if (!booking.value || booking.value.status === 'cancelled') return
  try {
    const QRCode = (await import('qrcode')).default
    const opts   = { width: 180, margin: 2, color: { dark: '#1a1a1a', light: '#ffffff' } }
    qrDataUrl1.value = await QRCode.toDataURL(buildSpayd(booking.value.upfront_amount, qrMsg1.value), opts)
    if (!booking.value.single_payment && booking.value.remaining_amount > 0) {
      qrDataUrl2.value = await QRCode.toDataURL(buildSpayd(booking.value.remaining_amount, qrMsg2.value), opts)
    }
  } catch {}
})

definePageMeta({ layout: 'rezervacia' })
useSeoMeta({
  title: 'Stav rezervace | CampingForYou',
  robots: 'noindex',
})
</script>

<style scoped>
.status-hero {
  background: transparent;
  color: var(--bs-body-color);
  padding: 3rem 0 2rem;
}
.status-hero-title {
  font-size: clamp(1.6rem, 4vw, 2.2rem);
  font-weight: 800;
  margin-bottom: 0.3rem;
}
.status-hero-sub { opacity: 0.6; margin: 0; }

.status-body {
  background: transparent;
  min-height: 60vh;
  padding: 0 0 4rem;
}
.status-wrap { max-width: 900px; margin: 0 auto; }

.status-not-found { text-align: center; padding: 4rem 1rem; }

/* Badge */
.status-badge-wrap { text-align: center; }
.status-badge {
  display: inline-flex;
  align-items: center;
  padding: 0.5rem 1.5rem;
  border-radius: 2rem;
  font-weight: 700;
  font-size: 1rem;
}
.status-badge--pending   { background: #fff8e1; color: #856404; border: 1px solid #ffe082; }
.status-badge--confirmed { background: #e8f5e9; color: #2d6a4f; border: 1px solid #a5d6a7; }
.status-badge--cancelled { background: #fce4ec; color: #b71c1c; border: 1px solid #ef9a9a; }

/* Karta */
.status-card {
  background: #fff;
  border-radius: 0.75rem;
  border: 1px solid #e5e5e5;
  overflow: hidden;
}
.status-card__head {
  padding: 0.85rem 1.25rem;
  background: var(--bs-primary);
  color: #fff;
  font-weight: 600;
  font-size: 0.925rem;
}
.status-card__body { padding: 1.5rem; }

/* Fotka karavanu */
.caravan-photo-wrap { border-radius: 0.5rem; overflow: hidden; }
.caravan-photo {
  display: block;
  width: 100%;
  height: 200px;
  object-fit: cover;
  border-radius: 0.5rem;
  transition: opacity 0.15s;
}
a:hover .caravan-photo { opacity: 0.9; }

/* Tabulka detailů */
.detail-table { width: 100%; border-collapse: collapse; }
.detail-table tr { border-bottom: 1px solid #f0f0f0; }
.detail-table tr:last-child { border-bottom: none; }
.detail-lbl { padding: 0.6rem 0.5rem 0.6rem 0; color: #666; font-size: 0.875rem; width: 40%; }
.detail-val { padding: 0.6rem 0; font-size: 0.9rem; color: #1a1a1a; text-align: right; }
.detail-split-lbl { padding: 0.35rem 0.5rem 0.35rem 0.75rem; color: #888; font-size: 0.8rem; }
.detail-split-val { padding: 0.35rem 0; font-size: 0.8rem; color: #555; text-align: right; }
.detail-split-date { display: block; font-size: 0.72rem; color: #aaa; margin-top: 1px; }
.detail-link { color: var(--bs-primary); text-decoration: none; }
.detail-link:hover { text-decoration: underline; }
.detail-note { color: #555; font-size: 0.85rem; white-space: pre-line; }
.detail-section-row { background: #f7f7f7; }
.detail-section-lbl { padding: 0.4rem 0.5rem; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #999; }
.detail-total-row { border-top: 2px solid #e5e5e5; }
.detail-total { font-size: 1.3rem; color: var(--bs-primary); }
.detail-time { font-size: 0.8rem; color: #888; margin-left: 0.4rem; }

/* Stavové info */
.status-info {
  padding: 0.75rem 1rem;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  display: flex;
  align-items: center;
}
.status-info--pending   { background: #fff8e1; color: #856404; }
.status-info--confirmed { background: #e8f5e9; color: #2d6a4f; }
.status-info--cancelled { background: #fce4ec; color: #b71c1c; }

/* Platobný blok (záloha / doplatek / jednorázová) */
.pay-block { text-align: center; }
.pay-block__title {
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: #888;
  margin-bottom: 0.25rem;
}
.pay-block__amount {
  font-size: 1.6rem;
  font-weight: 800;
  color: var(--bs-primary);
  line-height: 1.1;
  margin-bottom: 0.35rem;
}
.pay-block__deadline {
  font-size: 0.875rem;
  color: #c0392b;
  font-weight: 600;
}

.qr-img {
  display: block;
  margin: 0 auto;
  border-radius: 0.5rem;
  border: 1px solid #e5e5e5;
}

/* Ručné platobné údaje pod QR kódom */
.pay-manual {
  border-top: 1px solid #f0f0f0;
  padding-top: 0.75rem;
}
.pay-manual__row {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  gap: 0.5rem;
  padding: 0.3rem 0;
  font-size: 0.8rem;
  border-bottom: 1px solid #f7f7f7;
}
.pay-manual__row:last-child { border-bottom: none; }
.pay-manual__lbl { color: #999; flex-shrink: 0; }
.pay-manual__val { color: #1a1a1a; text-align: right; word-break: break-all; }
.pay-manual__iban { font-family: monospace; font-size: 0.75rem; }

/* Tab prepínač pre zálohu / doplatok */
.qr-tabs {
  display: flex;
  gap: 0.5rem;
}
.qr-tab {
  flex: 1;
  padding: 0.5rem 0.75rem;
  border: 1px solid #e5e5e5;
  border-radius: 0.5rem;
  background: #f7f7f7;
  color: #666;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.15s, color 0.15s, border-color 0.15s;
}
.qr-tab--active {
  background: var(--bs-primary);
  color: #fff;
  border-color: var(--bs-primary);
}
</style>
