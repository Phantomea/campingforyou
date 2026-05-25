export default defineNuxtRouteMiddleware(async (to) => {
  const localePath = useLocalePath()
  const { isAuthenticated, fetchUser } = useAuth()

  // Try to fetch user if not authenticated
  if (!isAuthenticated.value) {
    await fetchUser()
  }

  // Redirect to login if still not authenticated
  if (!isAuthenticated.value) {
    return navigateTo(localePath({ name: 'admin-login' }))
  }
})
