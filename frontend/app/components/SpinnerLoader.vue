<template>
  <div
    class="d-flex align-items-center justify-content-center py-5"
    :class="textPosition === 'right' ? 'flex-row gap-10px' : 'flex-column gap-10px'"
  >
    <div class="spinner-border text-primary flex-shrink-0" role="status">
      <span class="visually-hidden">{{ text || 'Načítavam...' }}</span>
    </div>
    <span v-if="text" class="text-muted small">{{ text }}</span>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  text?: string
  textPosition?: 'below' | 'right'
}>()
</script>
