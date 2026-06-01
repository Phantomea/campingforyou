<template>
  <div class="d-flex flex-column min-vh-100">
    <header class="rezervacia-header">
      <div class="container">
        <NuxtLink to="/">
          <img :src="'/logo-black.png'" alt="CampingForYou" class="rezervacia-logo" />
        </NuxtLink>
      </div>
    </header>
    <main class="flex-grow-1" style="padding-top: var(--navbar-height)">
      <slot />
    </main>
    <LayoutFooter />
  </div>
</template>

<style scoped>
.rezervacia-header {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  z-index: 1030;
  padding: 1rem 0;
  background: #fff;
  border-bottom: 1px solid #e5e5e5;
}

.rezervacia-logo {
  height: 50px;
  width: auto;
}
</style>
