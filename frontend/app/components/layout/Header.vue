<template>
  <!-- Main Navigation -->
  <nav class="main-navbar navbar navbar-expand-lg fixed-top" :class="{ scrolled: scrolled || !isHome }">
    <div class="container">
      <NuxtLink :to="localePath({ name: 'index' })" class="navbar-brand p-0 me-4">
        <img src="/logo.png" alt="CampingForYou" />
      </NuxtLink>

      <button
        class="navbar-toggler border-0 p-1"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#mainNav"
        aria-controls="mainNav"
        aria-expanded="false"
        aria-label="Toggle navigation"
      >
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="mainNav">
        <ul class="navbar-nav me-auto align-items-lg-center">
          <li class="nav-item">
            <NuxtLink :to="localePath({ name: 'index' })" class="nav-link" exact>Úvod</NuxtLink>
          </li>
          <li class="nav-item">
            <NuxtLink :to="localePath({ name: 'services' })" class="nav-link">Karavany</NuxtLink>
          </li>
          <li class="nav-item">
            <NuxtLink :to="localePath({ name: 'pricing' })" class="nav-link">Ceník</NuxtLink>
          </li>
          <li class="nav-item">
            <NuxtLink :to="localePath({ name: 'gallery' })" class="nav-link">Galerie</NuxtLink>
          </li>
          <li class="nav-item">
            <NuxtLink :to="localePath({ name: 'contact' })" class="nav-link">Kontakt</NuxtLink>
          </li>
        </ul>
        <ul class="navbar-nav align-items-lg-center ms-xl-auto mt-30px ms-15px mt-xl-0">
          <li class="nav-item">
            <NuxtLink :to="localePath({ name: 'rezervacia' })" class="btn btn-primary px-4">
              <i class="bi bi-calendar-check me-2"></i>Rezervovat
            </NuxtLink>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</template>

<script setup lang="ts">
const localePath = useLocalePath()
const route = useRoute()

const isHome = computed(() => {
  const name = route.name?.toString() ?? ''
  return name === 'index' || name.startsWith('index___')
})

const scrolled = ref(false)

onMounted(() => {
  const onScroll = () => { scrolled.value = window.scrollY > 40 }
  window.addEventListener('scroll', onScroll, { passive: true })
  onUnmounted(() => window.removeEventListener('scroll', onScroll))
})
</script>
