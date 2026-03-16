import { defineStore } from 'pinia'
import { useApi } from '@/api/useApi'
import { useUserSession } from '@/stores/userSession'
import { resetDynamicRoutes } from '@/router'
import type { LoginResponse, User, UserRole } from '@/types/user'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('token') || null,
  }),

  getters: {
    user(): User | null {
      const userSession = useUserSession()
      return userSession.user
    },
    roles(): string[] {
      const userSession = useUserSession()
      return (userSession.user?.roles as string[] | undefined) || []
    }
  },

  actions: {
    async login(email: string, password: string) {
      const api = useApi()
      const userSession = useUserSession()
      const res = (await api.post('/login', { email, password })) as unknown as LoginResponse

      userSession.setToken(res.token)
      const rolesNorm = (res.roles ?? []).map((r: UserRole) => (typeof r === 'string' ? r : (r && typeof r === 'object' && 'name' in r ? (r as { name?: string }).name : undefined) ?? ''))
      const userData: User = {
        id: res.id,
        nama_lengkap: res.nama_lengkap,
        jenis_kelamin: res.jenis_kelamin,
        tgl_lahir: res.tgl_lahir,
        email: res.email,
        no_handphone: res.no_handphone,
        foto_profile: res.foto_profile,
        roles: rolesNorm.filter(Boolean),
        subroles: res.subroles ?? [],
      }
      userSession.setUser(userData)

      this.token = res.token

      return res
    },

    async getProfile() {
        const api = useApi()
        const userSession = useUserSession()
        const res = (await api.get('/me')) as unknown as User
        userSession.setUser(res)
        return res
    },

    async logout() {
      const api = useApi()
      const userSession = useUserSession()

      try {
        await api.post('/logout')
      } catch (err) {
        // kalau token sudah expired, tetap lanjut logout
        console.warn('Logout API failed, clearing session anyway')
      } finally {
        resetDynamicRoutes() 
        userSession.logoutUser()
        this.token = null
      }
    }
  },
})
