import { defineStore } from 'pinia'
import { computed } from 'vue'
import { useStorage } from '@vueuse/core'
import type { User } from '@/types/user'

export const useUserSession = defineStore('userSession', () => {
  const token = useStorage<string>('token', '')
  const user = useStorage<User | null>('user', null)
  const subroles = computed(() => (user.value?.subroles as unknown[] | undefined) ?? [])

  const isLoggedIn = computed(() => !!token.value)

  function setToken(newToken: string) {
    token.value = newToken
  }

  function setUser(newUser: User | Record<string, unknown>) {
    const u = newUser as User
    user.value = {
      ...u,
      roles: u.roles ?? [],
      subroles: (Array.isArray(u.subroles) ? u.subroles : []) as string[] | Record<string, unknown>[]
    }
  }

  function logoutUser() {
    token.value = ''
    user.value = null
  }

  return {
    token,
    user,
    isLoggedIn,
    setToken,
    setUser,
    logoutUser,
    subroles,
  }
})
