<template>
  <div>
    <div class="d-flex justify-content-end mb-4">
      <button @click="openModal()" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Přidat karavan
      </button>
    </div>

    <!-- Zoznam -->
    <div class="card">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th>Název</th>
              <th>Výrobce</th>
              <th>Kapacita</th>
              <th>Rok</th>
              <th>Cena/den</th>
              <th>Stav</th>
              <th class="text-end">Akce</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="service in services" :key="service.id">
              <td class="fw-medium">{{ service.title }}</td>
              <td>{{ service.manufacturer || '–' }}</td>
              <td>{{ service.capacity ? `${service.capacity} os.` : '–' }}</td>
              <td>{{ service.year || '–' }}</td>
              <td>{{ service.price_per_day ? `${service.price_per_day} Kč` : '–' }}</td>
              <td>
                <span :class="['badge', service.is_active ? 'bg-success' : 'bg-secondary']">
                  {{ service.is_active ? 'Aktivní' : 'Neaktivní' }}
                </span>
              </td>
              <td class="text-end">
                <button @click="openModal(service)" class="btn btn-dark btn-sm me-1">
                  <i class="bi bi-pencil"></i>
                </button>
                <button @click="deleteService(service.id)" class="btn btn-outline-danger btn-sm">
                  <i class="bi bi-trash"></i>
                </button>
              </td>
            </tr>
            <tr v-if="services.length === 0">
              <td colspan="7" class="text-center text-muted py-4">
                <i class="bi bi-inbox display-6 d-block mb-2"></i>Žádné karavany
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="serviceModal" tabindex="-1" ref="modalRef">
      <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              {{ editingService ? 'Upravit karavan' : 'Přidat karavan' }}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>

          <div class="modal-body p-0">
            <!-- Taby -->
            <ul class="nav nav-tabs px-3 pt-3 border-bottom" id="svcTabs">
              <li class="nav-item" v-for="(tab, i) in tabs" :key="i">
                <button class="nav-link" :class="{ active: activeTab === i }" @click="activeTab = i" type="button">
                  <i :class="`bi ${tab.icon} me-1`"></i>{{ tab.label }}
                </button>
              </li>
            </ul>

            <form @submit.prevent="saveService" id="svcForm">
              <div class="p-4">

                <!-- TAB 0: Základní info -->
                <div v-show="activeTab === 0">
                  <div class="row g-3">
                    <div class="col-md-8">
                      <label class="form-label">Název *</label>
                      <input v-model="form.title" type="text" class="form-control" required />
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Slug</label>
                      <input v-model="form.slug" type="text" class="form-control" placeholder="automaticky" />
                    </div>
                    <div class="col-12">
                      <label class="form-label">Popis</label>
                      <ClientOnly>
                        <QuillEditor
                          v-model:content="form.description"
                          content-type="html"
                          theme="snow"
                          :toolbar="quillToolbar"
                          style="min-height: 180px"
                        />
                      </ClientOnly>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Výrobce</label>
                      <input v-model="form.manufacturer" type="text" class="form-control" placeholder="Hobby, Fendt, Adria…" />
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Rok výroby</label>
                      <input v-model.number="form.year" type="number" min="1990" max="2100" class="form-control" />
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">SPZ</label>
                      <input v-model="form.license_plate" type="text" class="form-control" placeholder="1AB1234" />
                    </div>
                    <div class="col-md-3">
                      <label class="form-label">Kapacita (os.)</label>
                      <input v-model.number="form.capacity" type="number" min="1" max="12" class="form-control" />
                    </div>
                    <div class="col-md-3">
                      <label class="form-label">Lůžka</label>
                      <input v-model.number="form.sleeping_capacity" type="number" min="1" max="12" class="form-control" />
                    </div>
                    <div class="col-md-3">
                      <label class="form-label">Pořadí</label>
                      <input v-model.number="form.sort_order" type="number" min="0" class="form-control" />
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                      <div class="form-check mb-2">
                        <input v-model="form.is_active" type="checkbox" class="form-check-input" id="isActive" />
                        <label class="form-check-label" for="isActive">Aktivní</label>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- TAB 1: Ceny -->
                <div v-show="activeTab === 1">
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="form-label">Cena nízka sezóna / deň (Kč)</label>
                      <div class="input-group">
                        <input v-model.number="form.price_low_season" type="number" step="0.01" min="0" class="form-control" />
                        <span class="input-group-text">Kč</span>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Cena vysoká sezóna / den (Kč)</label>
                      <div class="input-group">
                        <input v-model.number="form.price_high_season" type="number" step="0.01" min="0" class="form-control" />
                        <span class="input-group-text">Kč</span>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Záloha (Kč)</label>
                      <div class="input-group">
                        <input v-model.number="form.deposit" type="number" step="0.01" min="0" class="form-control" />
                        <span class="input-group-text">Kč</span>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Minimální počet dní pronájmu</label>
                      <input v-model.number="form.min_rental_days" type="number" min="1" max="365" class="form-control" />
                    </div>
                  </div>
                </div>

                <!-- TAB 2: Technické parametry -->
                <div v-show="activeTab === 2">
                  <div class="row g-3">
                    <div class="col-12">
                      <p class="text-muted small mb-3">Rozměry v centimetrech, hmotnosti v kilogramech.</p>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Délka (cm)</label>
                      <div class="input-group">
                        <input v-model.number="form.length_cm" type="number" min="1" class="form-control" placeholder="např. 750" />
                        <span class="input-group-text">cm</span>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Šířka (cm)</label>
                      <div class="input-group">
                        <input v-model.number="form.width_cm" type="number" min="1" class="form-control" placeholder="např. 230" />
                        <span class="input-group-text">cm</span>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Výška (cm)</label>
                      <div class="input-group">
                        <input v-model.number="form.height_cm" type="number" min="1" class="form-control" placeholder="např. 270" />
                        <span class="input-group-text">cm</span>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Hmotnost prázdný (kg)</label>
                      <div class="input-group">
                        <input v-model.number="form.weight_kg" type="number" min="1" class="form-control" placeholder="např. 1350" />
                        <span class="input-group-text">kg</span>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">MTOM – max. hmotnost (kg)</label>
                      <div class="input-group">
                        <input v-model.number="form.max_weight_kg" type="number" min="1" class="form-control" placeholder="např. 1800" />
                        <span class="input-group-text">kg</span>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Tažná koule</label>
                      <input v-model="form.tow_ball_size" type="text" class="form-control" placeholder="např. 50 mm" />
                    </div>
                    <div class="col-12 mt-3">
                      <p class="text-muted small fw-medium mb-2 border-top pt-3">Kapacity</p>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Čerstvá voda (L)</label>
                      <div class="input-group">
                        <input v-model.number="form.fresh_water_l" type="number" min="1" class="form-control" placeholder="napr. 122" />
                        <span class="input-group-text">L</span>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Odpadová voda (L)</label>
                      <div class="input-group">
                        <input v-model.number="form.waste_water_l" type="number" min="1" class="form-control" placeholder="napr. 92" />
                        <span class="input-group-text">L</span>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Chladnička (L)</label>
                      <div class="input-group">
                        <input v-model.number="form.fridge_l" type="number" min="1" class="form-control" placeholder="napr. 167" />
                        <span class="input-group-text">L</span>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Batéria (Ah)</label>
                      <div class="input-group">
                        <input v-model.number="form.battery_ah" type="number" min="1" class="form-control" placeholder="napr. 150" />
                        <span class="input-group-text">Ah</span>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Typ batérie</label>
                      <input v-model="form.battery_type" type="text" class="form-control" placeholder="napr. LiFePo4, AGM" />
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Dĺžka markízy (m)</label>
                      <div class="input-group">
                        <input v-model.number="form.awning_m" type="number" min="0" step="0.5" class="form-control" placeholder="napr. 4" />
                        <span class="input-group-text">m</span>
                      </div>
                    </div>
                    <div class="col-12 mt-3">
                      <p class="text-muted small fw-medium mb-2 border-top pt-3">Motor / pohon</p>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Motor</label>
                      <input v-model="form.engine" type="text" class="form-control" placeholder="napr. 2.2 JTD diesel, 140 k" />
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Prevodovka</label>
                      <input v-model="form.transmission" type="text" class="form-control" placeholder="napr. 9-st. automat" />
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Spotreba paliva</label>
                      <input v-model="form.fuel_consumption" type="text" class="form-control" placeholder="napr. 10–12 L/100km" />
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Nádrž (L)</label>
                      <div class="input-group">
                        <input v-model.number="form.fuel_tank_l" type="number" min="1" class="form-control" placeholder="napr. 70" />
                        <span class="input-group-text">L</span>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Max. ťažná záťaž (kg)</label>
                      <div class="input-group">
                        <input v-model.number="form.max_towing_kg" type="number" min="1" class="form-control" placeholder="napr. 2000" />
                        <span class="input-group-text">kg</span>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Počet miest na sedenie</label>
                      <input v-model.number="form.driving_seats" type="number" min="1" max="20" class="form-control" placeholder="napr. 4" />
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Kategória vodičského preukazu</label>
                      <input v-model="form.license_category" type="text" class="form-control" placeholder="napr. B" />
                    </div>
                    <div class="col-12 mt-2">
                      <label class="form-label">Interní poznámka</label>
                      <textarea v-model="form.internal_note" class="form-control" rows="3"
                        placeholder="Např. stav vozidla, servisní historie, speciální požadavky…"></textarea>
                      <div class="form-text">Zákazník ji nevidí.</div>
                    </div>
                  </div>
                </div>

                <!-- TAB 3: Vybavenie -->
                <div v-show="activeTab === 3">
                  <div class="row g-3">
                    <div v-for="eq in equipment" :key="eq.key" class="col-md-4 col-sm-6">
                      <div class="form-check form-switch equipment-switch">
                        <input
                          type="checkbox"
                          class="form-check-input"
                          :id="`eq-${eq.key}`"
                          v-model="(form as any)[eq.key]"
                        />
                        <label class="form-check-label" :for="`eq-${eq.key}`">
                          <i :class="`bi ${eq.icon} me-2 text-muted`"></i>{{ eq.label }}
                        </label>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- TAB 4: Galéria -->
                <div v-show="activeTab === 4">
                  <div v-if="!editingService" class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    Nejprve uložte karavan, poté můžete přidávat fotografie.
                  </div>
                  <template v-else>
                    <!-- Existující fotky -->
                    <div class="gallery-grid mb-4" v-if="galleryImages.length">
                      <div v-for="img in galleryImages" :key="img" class="gallery-item">
                        <img :src="imgUrl(img)" :alt="editingService.title" />
                        <button type="button" class="gallery-delete" @click="removeImage(img)" :disabled="deletingImage === img">
                          <i class="bi bi-x-lg"></i>
                        </button>
                      </div>
                    </div>
                    <p v-else class="text-muted small mb-3">Žádné fotografie. Přidejte první fotografii níže.</p>

                    <!-- Upload -->
                    <div class="upload-area" @click="fileInput?.click()" @dragover.prevent @drop.prevent="handleDrop">
                      <input ref="fileInput" type="file" accept="image/*" multiple class="d-none" @change="handleFiles" />
                      <i class="bi bi-cloud-upload fs-2 text-muted mb-2 d-block"></i>
                      <p class="mb-1 fw-medium">Klikněte nebo přetáhněte fotografie sem</p>
                      <p class="small text-muted mb-0">JPG, PNG, WebP — max. 8 MB / foto</p>
                    </div>

                    <!-- Upload progress -->
                    <div v-if="uploading" class="mt-3 d-flex align-items-center gap-2 text-muted small">
                      <div class="spinner-border spinner-border-sm"></div>
                      Nahrávám {{ uploadQueue.length }} {{ uploadQueue.length === 1 ? 'fotografii' : 'fotografie' }}…
                    </div>
                    <div v-if="uploadError" class="alert alert-danger mt-2 py-2 small">
                      <i class="bi bi-exclamation-triangle me-1"></i>{{ uploadError }}
                    </div>
                  </template>
                </div>

                <!-- TAB 5: Nedostupné termíny -->
                <div v-show="activeTab === 5">
                  <div v-if="!editingService" class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    Nejprve uložte karavan, poté můžete nastavit nedostupné termíny.
                  </div>
                  <template v-else>
                    <div class="card mb-4">
                      <div class="card-header bg-white fw-semibold py-2 small">
                        <i class="bi bi-plus-circle me-2 text-danger"></i>Přidat blokovaný termín
                      </div>
                      <div class="card-body py-3">
                        <div class="row g-2 align-items-end">
                          <div class="col-md-3">
                            <label class="form-label small mb-1">Datum od *</label>
                            <input v-model="blockForm.block_date_from" type="date" class="form-control form-control-sm" />
                          </div>
                          <div class="col-md-3">
                            <label class="form-label small mb-1">Datum do <span class="text-muted fw-normal">(nepovinné)</span></label>
                            <input
                              v-model="blockForm.block_date_to"
                              type="date"
                              class="form-control form-control-sm"
                              :min="blockForm.block_date_from || undefined"
                            />
                          </div>
                          <div class="col-md-4">
                            <label class="form-label small mb-1">Důvod <span class="text-muted fw-normal">(nepovinné)</span></label>
                            <input
                              v-model="blockForm.reason"
                              type="text"
                              class="form-control form-control-sm"
                              placeholder="Např. Údržba, Servis, Rezervováno…"
                            />
                          </div>
                          <div class="col-md-2">
                            <button
                              type="button"
                              class="btn btn-danger btn-sm w-100"
                              @click="addServiceBlock"
                              :disabled="savingBlock || !blockForm.block_date_from"
                            >
                              <span v-if="savingBlock" class="spinner-border spinner-border-sm me-1"></span>
                              <i v-else class="bi bi-plus-lg me-1"></i>Přidat
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="table-responsive">
                      <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                          <tr>
                            <th>Datum od</th>
                            <th>Datum do</th>
                            <th>Důvod</th>
                            <th class="text-end">Akce</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr v-for="block in serviceBlocks" :key="block.id">
                            <td class="fw-semibold">{{ formatBlockDate(block.block_date_from) }}</td>
                            <td>{{ block.block_date_to ? formatBlockDate(block.block_date_to) : '—' }}</td>
                            <td class="small text-muted fst-italic">{{ block.reason || '—' }}</td>
                            <td class="text-end">
                              <button @click="deleteServiceBlock(block.id)" class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-trash"></i>
                              </button>
                            </td>
                          </tr>
                          <tr v-if="serviceBlocks.length === 0">
                            <td colspan="4" class="text-center text-muted py-4">
                              <i class="bi bi-calendar-check display-6 d-block mb-2"></i>
                              Žádné blokované termíny pro tento karavan
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </template>
                </div>

              </div><!-- /p-4 -->
            </form>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zrušit</button>
            <button type="submit" form="svcForm" class="btn btn-primary" :disabled="saving">
              <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
              {{ saving ? 'Ukládám…' : 'Uložit' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'admin', middleware: 'auth' })

const api = useApi()
const { $bootstrap } = useNuxtApp()
const config = useRuntimeConfig()

const quillToolbar = [
  ['bold', 'italic', 'underline'],
  [{ list: 'ordered' }, { list: 'bullet' }],
  ['link'],
  ['clean'],
]

// ─── Typy ─────────────────────────────────────────────────────────────────────
interface Service {
  id: number
  title: string
  slug: string
  description: string | null
  manufacturer: string | null
  capacity: number | null
  sleeping_capacity: number | null
  year: number | null
  license_plate: string | null
  price_low_season: number | null
  price_high_season: number | null
  deposit: number | null
  min_rental_days: number
  length_cm: number | null
  width_cm: number | null
  height_cm: number | null
  weight_kg: number | null
  max_weight_kg: number | null
  tow_ball_size: string | null
  engine: string | null
  transmission: string | null
  fuel_consumption: string | null
  fuel_tank_l: number | null
  max_towing_kg: number | null
  driving_seats: number | null
  license_category: string | null
  fresh_water_l: number | null
  waste_water_l: number | null
  battery_ah: number | null
  battery_type: string | null
  fridge_l: number | null
  awning_m: number | null
  has_aircon: boolean
  has_roof_aircon: boolean
  has_solar: boolean
  has_shower: boolean
  has_toilet: boolean
  has_kitchen: boolean
  has_heating: boolean
  has_awning: boolean
  has_bike_rack: boolean
  has_gas_alarm: boolean
  has_smoke_alarm: boolean
  has_co_alarm: boolean
  has_spare_wheel: boolean
  has_outdoor_shower: boolean
  has_reversing_camera: boolean
  images: string[] | null
  image: string | null
  widget_code: string | null
  internal_note: string | null
  is_active: boolean
  sort_order: number
}

// ─── Taby ─────────────────────────────────────────────────────────────────────
const tabs = [
  { label: 'Základní info',   icon: 'bi-house-door' },
  { label: 'Ceny',            icon: 'bi-tag' },
  { label: 'Technické',       icon: 'bi-rulers' },
  { label: 'Vybavení',        icon: 'bi-check2-square' },
  { label: 'Galerie',         icon: 'bi-images' },
  { label: 'Nedostupné termíny', icon: 'bi-calendar-x' },
]
const activeTab = ref(0)

// ─── Vybavenie ────────────────────────────────────────────────────────────────
const equipment = [
  { key: 'has_kitchen',    icon: 'bi-cup-hot',          label: 'Kuchyně' },
  { key: 'has_shower',     icon: 'bi-droplet',           label: 'Sprcha' },
  { key: 'has_toilet',     icon: 'bi-house',             label: 'WC' },
  { key: 'has_heating',    icon: 'bi-thermometer-sun',   label: 'Topení' },
  { key: 'has_aircon',     icon: 'bi-wind',              label: 'Klimatizace' },
  { key: 'has_roof_aircon',icon: 'bi-snow2',             label: 'Střešní klima' },
  { key: 'has_solar',      icon: 'bi-sun',               label: 'Solární panel' },
  { key: 'has_awning',     icon: 'bi-umbrella',          label: 'Markýza' },
  { key: 'has_bike_rack',  icon: 'bi-bicycle',           label: 'Stojan na kola' },
  { key: 'has_spare_wheel',icon: 'bi-circle',            label: 'Náhradní kolo' },
  { key: 'has_gas_alarm',  icon: 'bi-exclamation-triangle', label: 'Alarm plynu' },
  { key: 'has_smoke_alarm',icon: 'bi-fire',              label: 'Alarm kouře' },
  { key: 'has_co_alarm',         icon: 'bi-shield-exclamation', label: 'Alarm CO' },
  { key: 'has_outdoor_shower',   icon: 'bi-droplet-half',       label: 'Vonkajšia sprcha' },
  { key: 'has_reversing_camera', icon: 'bi-camera-video',       label: 'Couvacia kamera' },
]

// ─── Stav ─────────────────────────────────────────────────────────────────────
const services      = ref<Service[]>([])
const modalRef      = ref<HTMLElement | null>(null)
const modalInstance = ref<any>(null)
const editingService= ref<Service | null>(null)
const saving        = ref(false)

const fileInput    = ref<HTMLInputElement | null>(null)
const uploading    = ref(false)
const uploadQueue  = ref<File[]>([])
const uploadError  = ref('')
const deletingImage= ref<string | null>(null)

// ─── Blokované termíny ────────────────────────────────────────────────────────
interface BlockedDate {
  id: number
  block_date_from: string
  block_date_to: string | null
  reason: string | null
}

const serviceBlocks = ref<BlockedDate[]>([])
const savingBlock   = ref(false)
const blockForm     = ref({ block_date_from: '', block_date_to: '', reason: '' })

const galleryImages = computed(() => editingService.value?.images ?? [])

const emptyForm = () => ({
  title: '', slug: '', description: '',
  manufacturer: '', capacity: 4 as number | null,
  sleeping_capacity: null as number | null,
  year: null as number | null, license_plate: '',
  price_low_season: null as number | null,
  price_high_season: null as number | null,
  deposit: null as number | null,
  min_rental_days: 1,
  length_cm: null as number | null,
  width_cm: null as number | null,
  height_cm: null as number | null,
  weight_kg: null as number | null,
  max_weight_kg: null as number | null,
  tow_ball_size: '',
  engine: '', transmission: '', fuel_consumption: '',
  fuel_tank_l: null as number | null,
  max_towing_kg: null as number | null,
  driving_seats: null as number | null,
  license_category: '',
  fresh_water_l: null as number | null,
  waste_water_l: null as number | null,
  battery_ah: null as number | null,
  battery_type: '',
  fridge_l: null as number | null,
  awning_m: null as number | null,
  has_aircon: false, has_roof_aircon: false, has_solar: false, has_shower: false, has_toilet: false,
  has_kitchen: true, has_heating: false, has_awning: false, has_bike_rack: false,
  has_gas_alarm: false, has_smoke_alarm: false, has_co_alarm: false, has_spare_wheel: false,
  has_outdoor_shower: false, has_reversing_camera: false,
  image: '', widget_code: '', internal_note: '',
  is_active: true, sort_order: 0,
})

const form = ref(emptyForm())

// ─── CRUD ─────────────────────────────────────────────────────────────────────
const loadServices = async () => {
  services.value = await api.get<Service[]>('/admin/services')
}

const openModal = (service?: Service) => {
  activeTab.value = 0
  uploadError.value = ''
  serviceBlocks.value = []
  blockForm.value = { block_date_from: '', block_date_to: '', reason: '' }
  if (service) {
    editingService.value = service
    loadServiceBlocks()
    form.value = {
      title: service.title,
      slug: service.slug,
      description: service.description ?? '',
      manufacturer: service.manufacturer ?? '',
      capacity: service.capacity,
      sleeping_capacity: service.sleeping_capacity,
      year: service.year,
      license_plate: service.license_plate ?? '',
      price_low_season: service.price_low_season,
      price_high_season: service.price_high_season,
      deposit: service.deposit,
      min_rental_days: service.min_rental_days ?? 1,
      length_cm: service.length_cm,
      width_cm: service.width_cm,
      height_cm: service.height_cm,
      weight_kg: service.weight_kg,
      max_weight_kg: service.max_weight_kg,
      tow_ball_size: service.tow_ball_size ?? '',
      engine: service.engine ?? '',
      transmission: service.transmission ?? '',
      fuel_consumption: service.fuel_consumption ?? '',
      fuel_tank_l: service.fuel_tank_l,
      max_towing_kg: service.max_towing_kg,
      driving_seats: service.driving_seats,
      license_category: service.license_category ?? '',
      fresh_water_l: service.fresh_water_l,
      waste_water_l: service.waste_water_l,
      battery_ah: service.battery_ah,
      battery_type: service.battery_type ?? '',
      fridge_l: service.fridge_l,
      awning_m: service.awning_m,
      has_aircon: service.has_aircon,
      has_roof_aircon: service.has_roof_aircon,
      has_solar: service.has_solar,
      has_shower: service.has_shower,
      has_outdoor_shower: service.has_outdoor_shower,
      has_toilet: service.has_toilet,
      has_kitchen: service.has_kitchen,
      has_heating: service.has_heating,
      has_awning: service.has_awning,
      has_bike_rack: service.has_bike_rack,
      has_gas_alarm: service.has_gas_alarm,
      has_smoke_alarm: service.has_smoke_alarm,
      has_co_alarm: service.has_co_alarm,
      has_spare_wheel: service.has_spare_wheel,
      has_reversing_camera: service.has_reversing_camera,
      image: service.image ?? '',
      widget_code: service.widget_code ?? '',
      internal_note: service.internal_note ?? '',
      is_active: service.is_active,
      sort_order: service.sort_order,
    }
  } else {
    editingService.value = null
    form.value = emptyForm()
  }
  if (!modalInstance.value && modalRef.value) {
    modalInstance.value = new $bootstrap.Modal(modalRef.value)
  }
  modalInstance.value?.show()
}

const closeModal = () => {
  modalInstance.value?.hide()
  editingService.value = null
}

const saveService = async () => {
  saving.value = true
  try {
    if (editingService.value) {
      const updated = await api.put<Service>(`/admin/services/${editingService.value.id}`, form.value)
      // Sync editingService so gallery reacts without reload
      editingService.value = { ...editingService.value, ...updated }
    } else {
      await api.post('/admin/services', form.value)
    }
    await loadServices()
    if (!editingService.value) closeModal()
  } catch (e: any) {
    alert(e.message || 'Chyba při ukládání')
  } finally {
    saving.value = false
  }
}

const deleteService = async (id: number) => {
  if (!confirm('Opravdu chcete vymazat tento karavan?')) return
  try {
    await api.del(`/admin/services/${id}`)
    await loadServices()
  } catch (e: any) {
    alert(e.message || 'Chyba při mazání')
  }
}

// ─── Galéria ──────────────────────────────────────────────────────────────────
const imgUrl = (path: string) => {
  const backend = config.public.backendUrl || ''
  return `${backend}/storage/${path}`
}

const handleFiles = async (e: Event) => {
  const input = e.target as HTMLInputElement
  if (!input.files?.length) return
  await uploadFiles(Array.from(input.files))
  input.value = ''
}

const handleDrop = async (e: DragEvent) => {
  const files = Array.from(e.dataTransfer?.files ?? []).filter(f => f.type.startsWith('image/'))
  if (files.length) await uploadFiles(files)
}

const uploadFiles = async (files: File[]) => {
  if (!editingService.value) return
  uploadError.value = ''
  uploadQueue.value = files
  uploading.value = true
  try {
    await api.getCsrfCookie()
    const fd = new FormData()
    files.forEach(f => fd.append('files[]', f))
    const result = await api.upload<{ images: string[] }>(
      `/admin/services/${editingService.value.id}/images`, fd
    )
    editingService.value = { ...editingService.value, images: result.images }
    await loadServices()
  } catch (e: any) {
    uploadError.value = e.message || 'Chyba při nahrávání'
  } finally {
    uploading.value = false
    uploadQueue.value = []
  }
}

const removeImage = async (path: string) => {
  if (!editingService.value || !confirm('Odstranit fotografii?')) return
  deletingImage.value = path
  try {
    await api.getCsrfCookie()
    const result = await api.del<{ images: string[] }>(
      `/admin/services/${editingService.value.id}/images`,
      { image: path }
    )
    editingService.value = { ...editingService.value, images: result.images }
    await loadServices()
  } catch (e: any) {
    alert(e.message || 'Chyba při mazání')
  } finally {
    deletingImage.value = null
  }
}

const loadServiceBlocks = async () => {
  if (!editingService.value) return
  serviceBlocks.value = await api.get<BlockedDate[]>(
    `/admin/booking-blocks?service_id=${editingService.value.id}&strict=1`
  )
}

const addServiceBlock = async () => {
  if (!editingService.value || !blockForm.value.block_date_from) return
  savingBlock.value = true
  try {
    const newBlock = await api.post<BlockedDate>('/admin/booking-blocks', {
      service_id:      editingService.value.id,
      block_date_from: blockForm.value.block_date_from,
      block_date_to:   blockForm.value.block_date_to || null,
      reason:          blockForm.value.reason || null,
    })
    serviceBlocks.value.push(newBlock)
    serviceBlocks.value.sort((a, b) => a.block_date_from.localeCompare(b.block_date_from))
    blockForm.value = { block_date_from: '', block_date_to: '', reason: '' }
  } catch (e: any) {
    alert(e.message || 'Chyba při ukládání')
  } finally {
    savingBlock.value = false
  }
}

const deleteServiceBlock = async (id: number) => {
  if (!confirm('Odstranit tento blok?')) return
  try {
    await api.del(`/admin/booking-blocks/${id}`)
    serviceBlocks.value = serviceBlocks.value.filter(b => b.id !== id)
  } catch (e: any) {
    alert(e.message || 'Chyba při mazání')
  }
}

const formatBlockDate = (d: string) =>
  new Date(d).toLocaleDateString('cs-CZ', { day: 'numeric', month: 'numeric', year: 'numeric' })

onMounted(loadServices)
</script>

<style scoped>
.equipment-switch {
  background: #f8f9fa;
  border: 1px solid #e9ecef;
  border-radius: 0.5rem;
  padding: 0.75rem 1rem;
}
.equipment-switch .form-check-input { cursor: pointer; }
.equipment-switch .form-check-label { cursor: pointer; font-size: 0.9rem; }

.gallery-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
  gap: 0.75rem;
}
.gallery-item {
  position: relative;
  aspect-ratio: 4/3;
  border-radius: 0.5rem;
  overflow: hidden;
  background: #f0f0f0;
}
.gallery-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.gallery-delete {
  position: absolute;
  top: 0.3rem;
  right: 0.3rem;
  background: rgba(0,0,0,0.6);
  color: #fff;
  border: none;
  border-radius: 50%;
  width: 1.75rem;
  height: 1.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.7rem;
  cursor: pointer;
  transition: background 0.15s;
}
.gallery-delete:hover { background: #dc3545; }

.upload-area {
  border: 2px dashed #dee2e6;
  border-radius: 0.75rem;
  padding: 2rem;
  text-align: center;
  cursor: pointer;
  transition: border-color 0.15s, background 0.15s;
}
.upload-area:hover {
  border-color: var(--bs-primary, #0d6efd);
  background: rgba(13,110,253,0.03);
}
</style>
