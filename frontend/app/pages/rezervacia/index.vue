<template>
  <div>
    <WizardSteps :current="1" />

    <section class="pt-5 pb-5" style="min-height:60vh">
      <div class="container">
        <h2 class="fs-4 fw-bold text-dark mb-1">Vyberte karavan</h2>
        <ClientOnly>
          <div class="row g-4 mt-1">
            <div v-for="service in services" :key="service.id" class="col-md-6 col-lg-4">
              <CaravanCard
                :service="service"
                :selected="selectedId === service.id"
                :selectable="true"
                @select="select"
              />
            </div>
          </div>
          <template #fallback>
            <div class="row g-4 mt-1">
              <div v-for="n in 3" :key="n" class="col-md-6 col-lg-4">
                <div class="caravan-card minh-280px bg-light"></div>
              </div>
            </div>
          </template>
        </ClientOnly>

        <div class="d-flex align-items-center justify-content-between gap-3 mt-5">
          <div></div>
          <button class="btn btn-primary btn-lg px-5" :disabled="!selectedId" @click="next">
            Pokračovat <i class="bi bi-arrow-right ms-2"></i>
          </button>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'rezervacia' })

interface Service {
  id: number; title: string; slug: string; description: string
  manufacturer?: string; capacity?: number; year?: number
  price_formatted?: string | null; image?: string | null; images?: string[] | null
}

const api = useApi()

const services = ref<Service[]>([])
onMounted(async () => {
  services.value = await api.get<Service[]>('/services')
})

const selectedId = ref<number | null>(null)

const select = (id: number) => { selectedId.value = id }

const next = () => {
  if (!selectedId.value) return
  navigateTo(`/rezervacia/termin?karavan=${selectedId.value}`)
}

useSeoMeta({ title: 'Rezervace — výběr karavanu | CampingForYou' })
</script>
