<template>
  <footer class="site-footer mt-auto">
    <div class="container">
      <div class="row g-4 g-lg-5">

        <!-- Logo + popis -->
        <div class="col-lg-3 col-md-6">
          <div class="footer-logo">
            <img src="/logo.png" alt="CampingForYou" />
          </div>
          <p class="footer-desc">
            Pronájem karavanů pro nezapomenutelné prázdniny. Moderní a dobře vybavené karavany pro vaše výlety.
          </p>
        </div>

        <!-- Kontakt -->
        <div class="col-lg-3 col-md-6">
          <h6>Kontakt</h6>

          <div class="footer-contact-item">
            <i class="bi bi-telephone-fill"></i>
            <a :href="`tel:${settings.phone || '+421900123456'}`">
              {{ settings.phone || '+421 900 123 456' }}
            </a>
          </div>
          <div class="footer-contact-item">
            <i class="bi bi-envelope-fill"></i>
            <a :href="`mailto:${settings.email || 'info@campingforyou.cz'}`">
              {{ settings.email || 'info@campingforyou.cz' }}
            </a>
          </div>
          <div class="footer-contact-item">
            <i class="bi bi-geo-alt-fill"></i>
            <a :href="`https://maps.google.com/?q=${encodeURIComponent(settings.address || 'Hlavní 1, Praha')}`" target="_blank" rel="noopener">
              {{ settings.address || 'Hlavní 1, Praha' }}
            </a>
          </div>
        </div>

        <!-- Otváracie hodiny -->
        <div class="col-lg-3 col-md-6">
          <h6>Otevírací hodiny</h6>
          <dl v-if="openingHours" class="footer-hours mb-0">
            <template v-for="(hours, day) in openingHours" :key="day">
              <dt>{{ day }}</dt>
              <dd>{{ hours }}</dd>
            </template>
          </dl>
          <dl v-else class="footer-hours mb-0">
            <dt>Pondělí – Pátek</dt>
            <dd>08:00 – 17:00</dd>
            <dt>Sobota</dt>
            <dd>08:00 – 12:00</dd>
            <dt>Neděle</dt>
            <dd>Zavřeno</dd>
          </dl>
        </div>

        <!-- Navigácia -->
        <div class="col-lg-3 col-md-6">
          <h6>Navigace</h6>
          <nav class="footer-nav">
            <NuxtLink :to="localePath({ name: 'index' })">Úvod</NuxtLink>
            <NuxtLink :to="localePath({ name: 'services' })">Karavany</NuxtLink>
            <NuxtLink :to="localePath({ name: 'pricing' })">Ceník</NuxtLink>
            <NuxtLink :to="localePath({ name: 'contact' })">Kontakt</NuxtLink>
          </nav>
        </div>

      </div>
    </div>

    <div class="footer-bottom">
      <div class="container">
        <p class="text-center">
          &copy; {{ new Date().getFullYear() }} CampingForYou. Všechna práva vyhrazena.
        </p>
      </div>
    </div>
  </footer>
</template>

<script setup lang="ts">
const localePath = useLocalePath()
const api = useApi()

const settings = ref<Record<string, string>>({})
const openingHours = ref<Record<string, string> | null>(null)

onMounted(async () => {
  try {
    const data = await api.get<Record<string, any>>('/settings/public')
    settings.value = data
    openingHours.value = data.opening_hours
  } catch {
    // Use defaults
  }
})
</script>
