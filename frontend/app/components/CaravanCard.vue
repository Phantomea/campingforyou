<template>
  <div
    class="bg-white position-relative h-100 d-flex flex-column rounded border"
    :class="{ 'caravan-card--selected': selected, 'caravan-card--selectable': selectable }"
    style="transition: border-color 0.2s, box-shadow 0.2s, transform 0.15s;"
    v-bind="selectable ? { role: 'button', tabindex: '0' } : {}"
    @click="selectable ? emit('select', service.id) : undefined"
    @keydown.enter="selectable ? emit('select', service.id) : undefined"
    @keydown.space.prevent="selectable ? emit('select', service.id) : undefined"
  >
    <div
      v-if="selectable"
      class="position-absolute top-0 end-0 mt-3 me-3 fs-5 text-primary"
      :class="selected ? 'opacity-100' : 'opacity-0'"
      style="transition: opacity 0.2s; z-index: 1;"
    >
      <i class="bi bi-check-circle-fill"></i>
    </div>
    <div class="overflow-hidden h-300px rounded">
      <img v-if="coverImage" :src="coverImage" :alt="service.title" loading="lazy" class="w-100 h-100 object-fit-cover" />
      <div v-else class="h-100 d-flex align-items-center justify-content-center" style="font-size: 3.5rem; color: #c8bfb0;">
        <i class="bi bi-house-door"></i>
      </div>
    </div>
    <div class="p-3 flex-grow-1 d-flex flex-column">
      <h3 class="fw-bold fs-md">{{ service.title }}</h3>
      <p v-if="service.description" class="small text-muted mb-3">{{ service.description }}</p>
      <div v-if="service.capacity || service.manufacturer || service.year" class="d-flex flex-wrap gap-2 small text-muted mb-3">
        <span v-if="service.capacity" class="d-flex align-items-center"><i class="bi bi-people-fill me-1"></i>{{ service.capacity }} os.</span>
        <span v-if="service.manufacturer" class="d-flex align-items-center"><i class="bi bi-truck me-1"></i>{{ service.manufacturer }}</span>
        <span v-if="service.year" class="d-flex align-items-center"><i class="bi bi-calendar3 me-1"></i>{{ service.year }}</span>
      </div>

      <ul v-if="equipment.length" class="list-unstyled p-0 mb-3 d-flex flex-column small text-muted gap-1">
        <li v-for="item in equipment.slice(0, 4)" :key="item.key" class="d-flex align-items-center" style="gap: 6px;">
          <svg v-if="item.value" xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16" class="text-success flex-shrink-0">
            <path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425z"/>
          </svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16" class="text-danger flex-shrink-0">
            <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/>
          </svg>
          {{ item.label }}
        </li>
        <template v-if="equipment.length > 4">
          <li v-for="item in equipment.slice(4)" v-show="expanded" :key="item.key">
            <span class="d-flex align-items-center" style="gap: 6px;">
              <svg v-if="item.value" xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16" class="text-success flex-shrink-0">
                <path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425z"/>
              </svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16" class="text-danger flex-shrink-0">
                <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/>
              </svg>
              {{ item.label }}
            </span>
          </li>
          <li>
            <button
              class="btn btn-link p-0 text-base fs-sm fw-medium d-inline-flex align-items-center gap-5px mt-5px text-decoration-none text-decoration-underline-hover"
              style="text-underline-offset: 2px;"
              type="button"
              role="button"
              @click.stop="expanded = !expanded"
            >
              {{ expanded ? 'Zobrazit méně' : 'Zobrazit více' }}
              <svg v-if="expanded" xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M7.646 4.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1-.708.708L8 5.707l-5.646 5.647a.5.5 0 0 1-.708-.708z"/>
              </svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708"/>
              </svg>
            </button>
          </li>
        </template>
      </ul>

      <div class="mt-auto pt-2">
        <div class="fw-bold text-secondary" style="font-size: 1.3rem;">
          <div v-if="service.price_formatted">
            <span class="fw-black fs-md">{{ service.price_formatted }} Kč</span>
            <span class="fs-sm fw-medium text-muted ms-1">/den</span>
          </div>
        </div>
        <div class="fs-sm mt-1">
          <template v-if="service.deposit">Záloha: <strong>{{ service.deposit_formatted }} Kč</strong></template>
          <template v-else>Bez zálohy</template>
        </div>
      </div>

      <div v-if="!selectable" class="d-flex flex-column align-items-center gap-2 pt-3">
        <NuxtLink to="/rezervacia" class="btn btn-secondary w-100 py-15px">Rezervovat</NuxtLink>
        <NuxtLink :to="localePath({ name: 'services-slug', params: { slug: service.slug } })" class="btn btn-link text-base w-100">Více o karavanu</NuxtLink>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
export interface CaravanService {
  id: number
  title: string
  slug: string
  description?: string | null
  manufacturer?: string | null
  capacity?: number | null
  year?: number | null
  price?: number | null
  price_formatted?: string | null
  deposit?: number | null
  deposit_formatted?: string | null
  image?: string | null
  images?: string[] | null
  has_aircon?: boolean | null
  has_roof_aircon?: boolean | null
  has_solar?: boolean | null
  has_shower?: boolean | null
  has_toilet?: boolean | null
  has_kitchen?: boolean | null
  has_heating?: boolean | null
  has_awning?: boolean | null
  has_bike_rack?: boolean | null
  has_gas_alarm?: boolean | null
  has_smoke_alarm?: boolean | null
  has_co_alarm?: boolean | null
  has_spare_wheel?: boolean | null
}

const EQUIPMENT_LABELS: Record<string, string> = {
  has_aircon:       'Klimatizace',
  has_roof_aircon:  'Střešní klimatizace',
  has_solar:        'Solární panel',
  has_shower:       'Sprcha',
  has_toilet:       'Toaleta',
  has_kitchen:      'Kuchyně',
  has_heating:      'Topení',
  has_awning:       'Markýza',
  has_bike_rack:    'Nosič kol',
  has_gas_alarm:    'Plynový alarm',
  has_smoke_alarm:  'Dymový alarm',
  has_co_alarm:     'CO alarm',
  has_spare_wheel:  'Rezervní kolo',
}

const props = defineProps<{
  service: CaravanService
  selected?: boolean
  selectable?: boolean
}>()

const emit = defineEmits<{
  select: [id: number]
}>()

const localePath = useLocalePath()
const backendUrl = useRuntimeConfig().public.backendUrl as string
const expanded = ref(false)

const coverImage = computed((): string | null => {
  if (props.service.images?.length) return `${backendUrl}/storage/${props.service.images[0]}`
  if (props.service.image) return props.service.image
  return null
})

const equipment = computed(() =>
  Object.entries(EQUIPMENT_LABELS)
    .filter(([key]) => (props.service as any)[key] !== null && (props.service as any)[key] !== undefined)
    .map(([key, label]) => ({ key, label, value: !!(props.service as any)[key] }))
    .sort((a, b) => {
      if (a.value !== b.value) return a.value ? -1 : 1
      return a.label.localeCompare(b.label, 'sk')
    })
)
</script>
