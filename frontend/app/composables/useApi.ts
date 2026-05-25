const getXsrfToken = (): string | null => {
  if (typeof document === 'undefined') return null
  const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/)
  return match ? decodeURIComponent(match[1]) : null
}

export const useApi = () => {
  const config = useRuntimeConfig()

  const apiFetch = async <T>(
    endpoint: string,
    options: RequestInit = {}
  ): Promise<T> => {
    const base = import.meta.server ? config.apiBaseServer : config.public.apiBase
    const url = `${base}${endpoint}`

    const xsrf = getXsrfToken()
    const extraHeaders: Record<string, string> = xsrf
      ? { 'X-XSRF-TOKEN': xsrf }
      : {}

    const response = await fetch(url, {
      ...options,
      credentials: 'include',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...extraHeaders,
        ...options.headers,
      },
    })

    if (!response.ok) {
      const error = await response.json().catch(() => ({}))
      throw new Error(error.message || `HTTP error! status: ${response.status}`)
    }

    return response.json()
  }

  const get = <T>(endpoint: string) => apiFetch<T>(endpoint, { method: 'GET' })

  const post = <T>(endpoint: string, data?: unknown) =>
    apiFetch<T>(endpoint, {
      method: 'POST',
      body: data ? JSON.stringify(data) : undefined,
    })

  const put = <T>(endpoint: string, data?: unknown) =>
    apiFetch<T>(endpoint, {
      method: 'PUT',
      body: data ? JSON.stringify(data) : undefined,
    })

  const patch = <T>(endpoint: string, data?: unknown) =>
    apiFetch<T>(endpoint, {
      method: 'PATCH',
      body: data ? JSON.stringify(data) : undefined,
    })

  const del = <T>(endpoint: string) =>
    apiFetch<T>(endpoint, { method: 'DELETE' })

  const getCsrfCookie = async () => {
    await fetch(`${config.public.backendUrl}/sanctum/csrf-cookie`, {
      credentials: 'include',
    })
  }

  return {
    get,
    post,
    put,
    patch,
    del,
    getCsrfCookie,
  }
}
