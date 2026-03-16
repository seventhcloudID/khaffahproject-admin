import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useApi } from '@/api/useApi'
import { useUserSession } from '@/stores/userSession'

export const useModulesStore = defineStore('modules', () => {
  const modules = ref<any[]>([])
  const isLoading = ref(false)

  const setModules = (m: any[]) => {
    modules.value = Array.isArray(m) ? m : []
  }

  const fetchModules = async (subroles?: any[]) => {
    isLoading.value = true
    try {
      const api = useApi()
      const userSession = useUserSession()
      const res = await api.post('/sistem-admin/get-modul-dinamis', {
        subroles: subroles ?? userSession.user?.subroles,
      })

      setModules(res.data)
      return res.data
    } catch (err) {
      console.error('Failed to fetch modules', err)
      setModules([])
      return []
    } finally {
      isLoading.value = false
    }
  }

  return {
    modules,
    isLoading,
    setModules,
    fetchModules,
  }
})
