<template>
  <section class="wiz-hero">
    <div class="container">
      <h1 class="wiz-hero-title">Rezervace karavanu</h1>
      <p class="wiz-hero-sub">Vyberte karavan, termín a zadejte kontaktní údaje</p>
      <div class="wiz-steps-bar">
        <template v-for="(label, i) in labels" :key="i">
          <div class="wiz-step" :class="{ 'wiz-step--active': current === i + 1, 'wiz-step--done': current > i + 1 }">
            <div class="wiz-bubble">
              <i v-if="current > i + 1" class="bi bi-check-lg"></i>
              <span v-else>{{ i + 1 }}</span>
            </div>
            <span class="wiz-step-label">{{ label }}</span>
          </div>
          <div v-if="i < labels.length - 1" class="wiz-connector" :class="{ 'wiz-connector--done': current > i + 1 }"></div>
        </template>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
defineProps<{ current: number }>()
const labels = ['Karavan', 'Termín', 'Údaje']
</script>

<style scoped>
.wiz-hero {
  background: linear-gradient(135deg, var(--jg-primary) 0%, #6b1414 100%);
  color: #fff;
  padding: 3.5rem 0 2.5rem;
}
.wiz-hero-title {
  font-size: clamp(1.6rem, 4vw, 2.4rem);
  font-weight: 800;
  margin-bottom: 0.4rem;
}
.wiz-hero-sub { opacity: 0.85; margin-bottom: 2rem; font-size: 1.05rem; }

.wiz-steps-bar { display: flex; align-items: center; }
.wiz-step { display: flex; align-items: center; gap: 0.5rem; flex-shrink: 0; }
.wiz-bubble {
  width: 2.25rem; height: 2.25rem; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-weight: 700; font-size: 0.875rem;
  background: rgba(255,255,255,0.2);
  border: 2px solid rgba(255,255,255,0.4);
  color: rgba(255,255,255,0.7);
  transition: all 0.2s; flex-shrink: 0;
}
.wiz-step--active .wiz-bubble {
  background: #fff; border-color: #fff; color: var(--jg-primary);
  box-shadow: 0 0 0 4px rgba(255,255,255,0.3);
}
.wiz-step--done .wiz-bubble { background: rgba(255,255,255,0.9); border-color: rgba(255,255,255,0.9); color: var(--jg-primary); }
.wiz-step-label { font-size: 0.8rem; font-weight: 600; opacity: 0.7; white-space: nowrap; }
.wiz-step--active .wiz-step-label { opacity: 1; }
.wiz-step--done .wiz-step-label { opacity: 0.9; }
.wiz-connector {
  flex: 1; height: 2px; background: rgba(255,255,255,0.25);
  margin: 0 0.5rem; min-width: 1.5rem; transition: background 0.2s;
}
.wiz-connector--done { background: rgba(255,255,255,0.7); }
</style>
