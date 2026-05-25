export default defineNuxtRouteMiddleware(async () => {
  if (import.meta.server) return
  const localePath = useLocalePath()
  const { isSuperAdmin, fetchUser, isAuthenticated } = useAuth()

  if (!isAuthenticated.value) {
    await fetchUser()
  }

  if (!isAuthenticated.value) {
    return navigateTo(localePath({ name: 'admin-login' }))
  }

  if (!isSuperAdmin.value) {
    return navigateTo(localePath({ name: 'admin' }))
  }
})
