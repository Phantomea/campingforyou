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
        :class="dayClasses(day)"
        :disabled="isCellDisabled(day)"
        :title="dayTitle(day)"
        @click="selectDay(day)"
        @mouseenter="hoverDay = dateStr(day)"
        @mouseleave="hoverDay = null"
      >
        {{ day }}
        <span v-if="isBlocked(day) && !isPast(day)" class="cal-blocked-dot"></span>
      </button>
    </div>

    <!-- Legenda -->
    <div class="cal-legend mt-2">
      <span class="legend-item"><span class="legend-dot blocked"></span> Nedostupné</span>
      <span class="legend-item"><span class="legend-dot range-start"></span> Převzetí</span>
      <span class="legend-item"><span class="legend-dot range-end"></span> Vrácení</span>
      <span class="legend-item"><span class="legend-dot in-range"></span> Pronájem</span>
    </div>
  </div>
</template>

<script setup lang="ts">
const props = defineProps<{
  dateFrom: string       // YYYY-MM-DD alebo ''
  dateTo: string         // YYYY-MM-DD alebo ''
  blockedDays?: string[] // YYYY-MM-DD[]
  selectingEnd?: boolean // true = kliknutie nastaví dateTo, false = dateFrom
  minDate?: string       // YYYY-MM-DD — najskorší voliteľný dátum (default: dnes)
}>()

const emit = defineEmits<{
  (e: 'update:dateFrom', val: string): void
  (e: 'update:dateTo', val: string): void
  (e: 'monthChange', year: number, month: number): void
}>()

const today = new Date()
today.setHours(0, 0, 0, 0)

// Najskorší voliteľný deň — buď dnes alebo zajtrajšok (ak prešiel cutoff na backende)
const minDateObj = computed(() => {
  if (props.minDate) return new Date(props.minDate + 'T00:00:00')
  return today
})

const viewYear  = ref(today.getFullYear())
const viewMonth = ref(today.getMonth())
const hoverDay  = ref<string | null>(null)

const dayNames = ['Po', 'Ut', 'St', 'Št', 'Pi', 'So', 'Ne']

const monthLabel = computed(() =>
  new Date(viewYear.value, viewMonth.value, 1)
    .toLocaleDateString('cs-CZ', { month: 'long', year: 'numeric' })
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

const dateStr = (day: number): string => {
  const m = String(viewMonth.value + 1).padStart(2, '0')
  const d = String(day).padStart(2, '0')
  return `${viewYear.value}-${m}-${d}`
}

const isPast    = (day: number) => new Date(viewYear.value, viewMonth.value, day) < minDateObj.value
const isToday   = (day: number) => new Date(viewYear.value, viewMonth.value, day).getTime() === today.getTime()
const isBlocked = (day: number) => !!props.blockedDays?.includes(dateStr(day))

// First blocked day after dateFrom — limits how far the end date can go
const firstBlockedAfterFrom = computed((): string | null => {
  if (!props.selectingEnd || !props.dateFrom || !props.blockedDays?.length) return null
  return props.blockedDays.filter(d => d > props.dateFrom).sort()[0] ?? null
})

// A day that cannot be selected as end date because it's at/after a blocked day in the range
const isAfterRangeBlock = (day: number): boolean => {
  if (!props.selectingEnd || !firstBlockedAfterFrom.value) return false
  return dateStr(day) >= firstBlockedAfterFrom.value
}

const isCellDisabled = (day: number): boolean =>
  isPast(day) || isBlocked(day) || isAfterRangeBlock(day)

const isRangeStart = (day: number) => !!props.dateFrom && dateStr(day) === props.dateFrom
const isRangeEnd   = (day: number) => !!props.dateTo   && dateStr(day) === props.dateTo

const isInRange = (day: number): boolean => {
  const d = dateStr(day)
  const from = props.dateFrom
  // Clip the preview range at the first blocked day
  const limit = firstBlockedAfterFrom.value
  const rawTo = props.dateTo || (props.selectingEnd && hoverDay.value ? hoverDay.value : '')
  const to = (limit && rawTo >= limit) ? limit : rawTo
  if (!from || !to) return false
  return d > from && d < to
}

const isHoverEnd = (day: number): boolean => {
  if (!props.selectingEnd || !hoverDay.value || props.dateTo) return false
  // Don't show hover end past first blocked day
  if (firstBlockedAfterFrom.value && hoverDay.value >= firstBlockedAfterFrom.value) return false
  return dateStr(day) === hoverDay.value && hoverDay.value > (props.dateFrom || '')
}

const dayClasses = (day: number) => ({
  'is-today':       isToday(day) && !isPast(day),
  'is-disabled':    isPast(day) || isAfterRangeBlock(day),
  'is-blocked':     isBlocked(day),
  'is-range-start': isRangeStart(day),
  'is-range-end':   isRangeEnd(day),
  'is-in-range':    isInRange(day),
  'is-hover-end':   isHoverEnd(day),
})

const dayTitle = (day: number): string => {
  if (isBlocked(day)) return 'Tento termín není dostupný'
  if (isPast(day))    return 'Minulé datum'
  if (isAfterRangeBlock(day)) return 'Termín je za obsazeným dnem'
  if (isRangeStart(day)) return 'Datum převzetí'
  if (isRangeEnd(day))   return 'Datum vrácení'
  return ''
}

const selectDay = (day: number) => {
  if (isCellDisabled(day)) return
  const d = dateStr(day)

  if (!props.selectingEnd) {
    emit('update:dateFrom', d)
    emit('update:dateTo', '')
  } else {
    if (props.dateFrom && d > props.dateFrom) {
      emit('update:dateTo', d)
    } else {
      emit('update:dateFrom', d)
      emit('update:dateTo', '')
    }
  }
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
  gap: 2px;
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
  transition: background 0.1s, border-color 0.1s, color 0.1s;
}

.cal-day:hover:not(:disabled):not(.is-range-start):not(.is-range-end):not(.is-blocked) {
  border-color: var(--jg-primary);
  color: var(--jg-primary);
  background: rgba(139, 26, 26, 0.05);
}

.cal-day.is-today:not(.is-range-start):not(.is-range-end) {
  border-color: #dee2e6;
  font-weight: 700;
  color: var(--jg-primary);
}

.cal-day.is-range-start {
  background-color: var(--jg-primary);
  border-color: var(--jg-primary);
  border-radius: 0.375rem 0 0 0.375rem;
  color: #fff;
  font-weight: 700;
}

.cal-day.is-range-end {
  background-color: #2d6a4f;
  border-color: #2d6a4f;
  border-radius: 0 0.375rem 0.375rem 0;
  color: #fff;
  font-weight: 700;
}

.cal-day.is-in-range {
  background-color: rgba(139, 26, 26, 0.1);
  border-color: transparent;
  border-radius: 0;
  color: #333;
}

.cal-day.is-hover-end {
  background-color: rgba(45, 106, 79, 0.2);
  border-color: #2d6a4f;
  color: #2d6a4f;
}

.cal-day.is-disabled {
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
  flex-wrap: wrap;
  gap: 0.6rem 1rem;
  font-size: 0.7rem;
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

.legend-dot.blocked    { background-color: #ffd0d0; border: 1px solid #dc3545; }
.legend-dot.range-start { background-color: var(--jg-primary); }
.legend-dot.range-end   { background-color: #2d6a4f; }
.legend-dot.in-range    { background-color: rgba(139, 26, 26, 0.15); border: 1px solid rgba(139,26,26,0.3); }
</style>
