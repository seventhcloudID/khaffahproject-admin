import axios from 'axios'
import { useUserSession } from '@/stores/userSession'
import { useStorage } from '@vueuse/core'

// Base URL API: dari env atau fallback. Di DEV tanpa env pakai proxy relatif agar tidak kena CORS/Network Error.
const getBaseURL = () => {
  const env = import.meta.env.VITE_API_BASE_URL
  if (env && typeof env === 'string' && env.trim()) return env.trim().replace(/\/+$/, '')
  if (import.meta.env.DEV) {
    // Relatif ke origin: request ke /api/... lalu Vite proxy meneruskan ke backend (vite.config.ts proxy)
    return '/api'
  }
  return ''
}

export function useApi() {
  const userSession = useUserSession()

  const api = axios.create({
    baseURL: getBaseURL(),
    headers: {
        Accept: 'application/json',
    },
    timeout: 30000,
  })

  api.interceptors.request.use((config) => {
    if (userSession.isLoggedIn && config.headers) {
      config.headers.Authorization = `Bearer ${userSession.token}`
    }
    // Penting: saat kirim FormData (upload file), jangan set Content-Type
    // agar browser/axios mengirim multipart/form-data dengan boundary
    if (config.data instanceof FormData && config.headers) {
      delete (config.headers as Record<string, unknown>)['Content-Type']
    }
    return config
  })

  api.interceptors.response.use(
    (response) => {
      // ✅ PERBAIKAN: Jangan transform blob response
      if (response.config.responseType === 'blob') {
        return response // Return full response untuk blob
      }
      return response.data // Return data langsung untuk JSON
    },
    (error) => {
      // kalau backend kasih 401 -> auto logout
      if (error.response?.status === 401) {
        useStorage('user_session', '').value = ''
        userSession.logoutUser()
        
        window.location.href = '/auth/login'
      }

      // lempar error ke calling
      return Promise.reject(error)
    }
  )

  return {
    get: (url: string, config = {}) => api.get(url, config),
    post: (url: string, payload?: any, config = {}) => api.post(url, payload, config),
    put: (url: string, payload?: any, config = {}) => api.put(url, payload, config),
    delete: (url: string, config = {}) => api.delete(url, config),
  }
}
