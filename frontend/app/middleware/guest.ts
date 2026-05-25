export default defineNuxtRouteMiddleware(async () => {
  const localePath = useLocalePath()
  const { isAuthenticated, fetchUser } = useAuth()

  if (!isAuthenticated.value) {
    await fetchUser()
  }

  if (isAuthenticated.value) {
    return navigateTo(localePath({ name: 'admin' }))
  }
})
