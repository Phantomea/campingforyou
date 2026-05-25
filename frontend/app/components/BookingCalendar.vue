<template>
  <div class="booking-calendar">
    <!-- Header: navigácia -->
    <div class="cal-header">
      <button type="button" class="cal-nav" @click="prevMonth" :disabled="isPrevDisabled">
        <i class="bi bi-chevron-left"></i>
      </button>
      <span class="cal-title">{{ monthLabel }}</span>
      <button type="button" class="cal-nav" @click="nextMonth">
        <i class="bi bi-chevron-right"></i>
      </button>
    </div>

    <!-- Dni v týždni -->
    <div class="cal-grid">
      <div v-for="d in dayNames" :key="d" class="cal-cell cal-day-name">{{ d }}</div>

      <!-- Prázdne bunky pred prvým dňom -->
      <div v-for="n in firstDayOffset" :key="`e-${n}`" class="cal-cell"></div>

      <!-- Dni mesiaca -->
      <button
        v-for="day in daysInMonth"
        :key="day"
        type="button"
        class="cal-cell cal-day"
        :class="{
          'is-today':    isToday(day),
          'is-selected': isSelected(day),
          'is-disabled': isDisabled(day),
          'is-weekend':  isWeekend(day),
          'is-blocked':  isBlocked(day),
        }"
        :disabled="isDisabled(day) || isBlocked(day)"
        :title="blockReason(day)"
        @click="selectDay(day)"
      >
        {{ day }}
        <span v-if="isBlocked(day) && !isWeekend(day) && !isPast(day)" class="cal-blocked-dot"></span>
      </button>
    </div>

    <!-- Legenda -->
    <div class="cal-legend mt-2">
      <span class="legend-item"><span class="legend-dot blocked"></span> Nedostupný deň</span>
      <span class="legend-item"><span class="legend-dot selected"></span> Vybraný deň</span>
    </div>
  </div>
</template>

<script setup lang="ts">
const props = defineProps<{
  modelValue: string
  blockedDays?: string[] // YYYY-MM-DD
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', val: string): void
  (e: 'monthChange', year: number, month: number): void
}>()

const today = new Date()
today.setHours(0, 0, 0, 0)

const viewYear  = ref(today.getFullYear())
const viewMonth = ref(today.getMonth())

const dayNames = ['Po', 'Ut', 'St', 'Št', 'Pi', 'So', 'Ne']

const monthLabel = computed(() =>
  new Date(viewYear.value, viewMonth.value, 1)
    .toLocaleDateString('sk-SK', { month: 'long', year: 'numeric' })
)

const daysInMonth = computed(() =>
  new Date(viewYear.value, viewMonth.value + 1, 0).getDate()
)

const firstDayOffset = computed(() => {
  const jsDay = new Date(viewYear.value, viewMonth.value, 1).getDay()
  return jsDay === 0 ? 6 : jsDay - 1
})

const isPrevDisabled = computed(() =>
  viewYear.value === today.getFullYear() && viewMonth.value === today.getMonth()
)

const prevMonth = () => {
  if (isPrevDisabled.value) return
  if (viewMonth.value === 0) { viewMonth.value = 11; viewYear.value-- }
  else viewMonth.value--
  emit('monthChange', viewYear.value, viewMonth.value + 1)
}

const nextMonth = () => {
  if (viewMonth.value === 11) { viewMonth.value = 0; viewYear.value++ }
  else viewMonth.value++
  emit('monthChange', viewYear.value, viewMonth.value + 1)
}

const dateStr = (day: number) => {
  const m = String(viewMonth.value + 1).padStart(2, '0')
  const d = String(day).padStart(2, '0')
  return `${viewYear.value}-${m}-${d}`
}

const isPast = (day: number) => {
  const d = new Date(viewYear.value, viewMonth.value, day)
  d.setHours(0, 0, 0, 0)
  return d < today
}

const isToday   = (day: number) => new Date(viewYear.value, viewMonth.value, day).getTime() === today.getTime()
const isSelected = (day: number) => dateStr(day) === props.modelValue
const isWeekend  = (day: number) => { const w = new Date(viewYear.value, viewMonth.value, day).getDay(); return w === 0 || w === 6 }
const isBlocked  = (day: number) => !!props.blockedDays?.includes(dateStr(day))
const isDisabled = (day: number) => isWeekend(day) || isPast(day)

const blockReason = (day: number) => isBlocked(day) ? 'Tento deň je nedostupný' : ''

const selectDay = (day: number) => {
  if (isDisabled(day) || isBlocked(day)) return
  emit('update:modelValue', dateStr(day))
}
</script>

<style scoped>
.booking-calendar { user-select: none; }

.cal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 0.75rem;
}

.cal-title {
  font-weight: 700;
  font-size: 0.9375rem;
  text-transform: capitalize;
  color: #1a1a1a;
}

.cal-nav {
  background: none;
  border: 1.5px solid #dee2e6;
  border-radius: 0.375rem;
  width: 2rem;
  height: 2rem;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: #555;
  transition: all 0.15s;
  padding: 0;
}

.cal-nav:hover:not(:disabled) { border-color: var(--jg-primary); color: var(--jg-primary); }
.cal-nav:disabled { opacity: 0.35; cursor: not-allowed; }

.cal-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 3px;
}

.cal-cell {
  aspect-ratio: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.8125rem;
  border-radius: 0.375rem;
  position: relative;
}

.cal-day-name {
  font-weight: 700;
  font-size: 0.75rem;
  color: #888;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  aspect-ratio: auto;
  padding: 0.25rem 0;
}

.cal-day {
  background: none;
  border: 1.5px solid transparent;
  cursor: pointer;
  color: #333;
  font-weight: 500;
  transition: all 0.15s;
}

.cal-day:hover:not(:disabled):not(.is-selected):not(.is-blocked) {
  border-color: var(--jg-primary);
  color: var(--jg-primary);
  background: rgba(139, 26, 26, 0.05);
}

.cal-day.is-today:not(.is-selected) {
  border-color: #dee2e6;
  font-weight: 700;
  color: var(--jg-primary);
}

.cal-day.is-selected {
  background-color: var(--jg-primary);
  border-color: var(--jg-primary);
  color: #fff;
  font-weight: 700;
}

.cal-day.is-disabled,
.cal-day.is-weekend {
  color: #ccc;
  cursor: not-allowed;
  background: none;
}

.cal-day.is-blocked:not(.is-disabled) {
  background-color: #fff0f0;
  color: #ccc;
  cursor: not-allowed;
  border-color: #ffd0d0;
}

.cal-blocked-dot {
  position: absolute;
  bottom: 3px;
  left: 50%;
  transform: translateX(-50%);
  width: 4px;
  height: 4px;
  border-radius: 50%;
  background-color: #dc3545;
}

.cal-legend {
  display: flex;
  gap: 1rem;
  font-size: 0.75rem;
  color: #888;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 0.3rem;
}

.legend-dot {
  width: 10px;
  height: 10px;
  border-radius: 3px;
  display: inline-block;
}

.legend-dot.blocked { background-color: #ffd0d0; border: 1px solid #dc3545; }
.legend-dot.selected { background-color: var(--jg-primary); }
</style>
