<template>
  <div>
    <WizardSteps :current="3" />

    <section class="wiz-body">
      <div class="container">
        <h2 class="wiz-step-heading">Kontaktní a fakturační údaje</h2>
        <div class="row g-4 mt-1">
          <div class="col-lg-7">
            <form @submit.prevent="submit" novalidate>
              <div class="wiz-card mb-3">
                <div class="wiz-card__head"><i class="bi bi-person-fill me-2"></i>Kontaktní údaje</div>
                <div class="wiz-card__body">

                  <!-- Faktura na firmu — prvá položka -->
                  <div class="form-check mb-4">
                    <input type="checkbox" class="form-check-input" id="wantInvoice" v-model="wantInvoice" />
                    <label class="form-check-label fw-semibold" for="wantInvoice">Chci fakturu na firmu</label>
                  </div>

                  <!-- IČO / firma / DIČ — nad menom, len ak wantInvoice -->
                  <template v-if="wantInvoice">
                    <div class="mb-3">
                      <label class="form-label">IČO</label>
                      <div class="input-group">
                        <input v-model="form.billing_ico" type="text" class="form-control" placeholder="12345678" maxlength="8" @keydown.enter.prevent="loadFromAres" />
                        <button type="button" class="btn btn-outline-secondary" @click="loadFromAres" :disabled="aresLoading">
                          <span v-if="aresLoading" class="spinner-border spinner-border-sm me-1"></span>
                          <i v-else class="bi bi-search me-1"></i>Načíst z ARES
                        </button>
                      </div>
                      <div v-if="aresError" class="form-text text-danger"><i class="bi bi-exclamation-circle me-1"></i>{{ aresError }}</div>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Název firmy</label>
                      <input v-model="form.billing_company" type="text" class="form-control" placeholder="Firma s.r.o." />
                    </div>
                    <div class="mb-3">
                      <label class="form-label">DIČ <span class="text-muted small">(nepovinné)</span></label>
                      <input v-model="form.billing_dic" type="text" class="form-control" placeholder="CZ12345678" />
                    </div>
                    <hr class="mb-3" />
                  </template>

                  <div class="mb-3">
                    <label class="form-label">Jméno a příjmení</label>
                    <input v-model="form.customer_name" type="text" class="form-control" required placeholder="Jan Novák" />
                  </div>
                  <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                      <label class="form-label">Telefon</label>
                      <input v-model="form.customer_phone" type="tel" class="form-control" required placeholder="+420 900 000 000" />
                    </div>
                    <div class="col-sm-6">
                      <label class="form-label">E-mail</label>
                      <input v-model="form.customer_email" type="email" class="form-control" required placeholder="jan@example.cz" />
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
                  <div>
                    <label class="form-label">Poznámka <span class="text-muted small">(nepovinné)</span></label>
                    <textarea v-model="form.note" class="form-control" rows="2" placeholder="Např. speciální požadavky..."></textarea>
                  </div>

                </div>
              </div>

              <div v-if="submitError" class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ submitError }}
              </div>

              <div class="wiz-nav mt-2">
                <NuxtLink :to="`/rezervacia/termin?karavan=${karavanId}&od=${dateFrom}&do=${dateTo}`" class="btn btn-outline-secondary" :class="{ disabled: submitting }">
                  <i class="bi bi-arrow-left me-2"></i>Zpět
                </NuxtLink>
                <button type="submit" class="btn btn-primary btn-lg px-5" :disabled="submitting">
                  <span v-if="submitting" class="spinner-border spinner-border-sm me-2"></span>
                  <i v-else class="bi bi-calendar-check me-2"></i>
                  {{ submitting ? 'Odesílám...' : 'Rezervovat' }}
                </button>
              </div>
            </form>
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
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
interface Service {
  id: number; title: string; capacity?: number
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

const route = useRoute()
const api   = useApi()

const karavanId = Number(route.query.karavan)
const dateFrom  = (route.query.od  as string) || ''
const dateTo    = (route.query.do  as string) || ''

if (!karavanId || !dateFrom || !dateTo) { await navigateTo('/rezervacia') }

const [{ data: serviceData }, { data: previewData }, { data: settingsData }] = await Promise.all([
  useAsyncData(`wiz-svc-${karavanId}`, () =>
    api.get<Service[]>('/services').then(list => list.find(s => s.id === karavanId) ?? null)
  ),
  useAsyncData(`wiz-prev-${karavanId}-${dateFrom}-${dateTo}`, () =>
    api.get<Preview>(`/bookings/preview?service_id=${karavanId}&date_from=${dateFrom}&date_to=${dateTo}`)
  ),
  useAsyncData('wiz-settings', () => api.get<Record<string, string>>('/settings/public'), { default: () => ({}) }),
])

if (!serviceData.value) { await navigateTo('/rezervacia') }

const service  = computed(() => serviceData.value!)
const preview  = computed(() => previewData.value)
const settings = computed(() => (settingsData.value || {}) as Record<string, string>)

const wantInvoice  = ref(false)
const submitting   = ref(false)
const submitError  = ref('')
const aresLoading  = ref(false)
const aresError    = ref('')

const form = ref({
  customer_name: '', customer_phone: '', customer_email: '', note: '',
  billing_company: '', billing_ico: '', billing_dic: '',
  billing_street: '', billing_city: '', billing_zip: '',
})

const loadFromAres = async () => {
  const ico = form.value.billing_ico.trim()
  if (!ico || ico.length < 6) {
    aresError.value = 'Zadejte platné IČO (min. 6 číslic)'
    return
  }
  aresError.value = ''
  aresLoading.value = true
  try {
    const res = await fetch(
      `https://ares.gov.cz/ekonomicke-subjekty-v-be/rest/ekonomicke-subjekty/${ico}`
    )
    if (!res.ok) throw new Error('Firma s tímto IČO nebyla nalezena v ARES')
    const data = await res.json()

    form.value.billing_company = data.obchodniJmeno || ''
    form.value.billing_dic     = data.dic || ''

    const s = data.sidlo || {}
    const streetNum = [s.cisloDomovni, s.cisloOrientacni ? `/${s.cisloOrientacni}` : '']
      .filter(Boolean).join('')
    form.value.billing_street = [s.nazevUlice, streetNum].filter(Boolean).join(' ')
    form.value.billing_city   = s.nazevObce || ''
    form.value.billing_zip    = s.psc ? String(s.psc) : ''
  } catch (e: any) {
    aresError.value = e.message || 'Chyba při načítání z ARES'
  } finally {
    aresLoading.value = false
  }
}

const submit = async () => {
  submitError.value = ''
  submitting.value  = true
  try {
    await api.getCsrfCookie()

    const payload: Record<string, any> = {
      service_id:      karavanId,
      date_from:       dateFrom,
      date_to:         dateTo,
      customer_name:   form.value.customer_name,
      customer_phone:  form.value.customer_phone,
      customer_email:  form.value.customer_email,
      note:            form.value.note || null,
      billing_street:  form.value.billing_street  || null,
      billing_city:    form.value.billing_city    || null,
      billing_zip:     form.value.billing_zip     || null,
    }
    if (wantInvoice.value) {
      payload.billing_company = form.value.billing_company || null
      payload.billing_ico     = form.value.billing_ico     || null
      payload.billing_dic     = form.value.billing_dic     || null
    }

    const booking = await api.post<any>('/bookings', payload)
    await navigateTo(`/stav-rezervacie/${booking.token}`)
  } catch (e: any) {
    submitError.value = e.message || 'Chyba při odesílání. Zkuste znovu.'
    window.scrollTo({ top: 0, behavior: 'smooth' })
  } finally {
    submitting.value = false
  }
}

useSeoMeta({ title: 'Rezervace — kontaktní údaje | CampingForYou' })
</script>

<style scoped>
.wiz-body { padding: 3rem 0 4rem; background: var(--jg-light, #f8f4ec); min-height: 60vh; }
.wiz-step-heading { font-size: 1.5rem; font-weight: 700; color: #1a1a1a; margin-bottom: 0.25rem; }
.wiz-nav { display: flex; align-items: center; justify-content: space-between; gap: 1rem; }

.wiz-card { background: #fff; border-radius: 0.75rem; border: 1px solid #e5e5e5; overflow: hidden; }
.wiz-card__head { padding: 0.85rem 1.25rem; background: var(--jg-primary); color: #fff; font-weight: 600; font-size: 0.925rem; }
.wiz-card__body { padding: 1.5rem; }

</style>
