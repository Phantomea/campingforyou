<template>
  <div class="d-flex flex-column min-vh-100">
    <LayoutHeader />
    <main class="flex-grow-1 main-content">
      <slot />
    </main>
    <LayoutFooter />
  </div>
</template>
