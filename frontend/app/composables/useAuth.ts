interface User {
  id: number
  name: string
  email: string
  role: 'owner' | 'super_admin'
}

export const useAuth = () => {
  const user = useState<User | null>('auth-user', () => null)
  const isAuthenticated = computed(() => !!user.value)
  const isOwner = computed(() => user.value?.role === 'owner')
  const isSuperAdmin = computed(() => user.value?.role === 'super_admin')

  const api = useApi()

  const fetchUser = async () => {
    try {
      user.value = await api.get<User>('/user')
    } catch {
      user.value = null
    }
  }

  const login = async (email: string, password: string) => {
    await api.getCsrfCookie()
    const response = await api.post<{ user: User }>('/login', { email, password })
    user.value = response.user
    return response
  }

  const logout = async () => {
    await api.post('/logout')
    user.value = null
  }

  const hasRole = (...roles: string[]) => {
    return user.value && roles.includes(user.value.role)
  }

  return {
    user,
    isAuthenticated,
    isOwner,
    isSuperAdmin,
    fetchUser,
    login,
    logout,
    hasRole,
  }
}
