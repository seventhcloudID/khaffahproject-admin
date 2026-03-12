import './assets/main.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import PrimeVue from 'primevue/config'
import Aura from '@primevue/themes/aura';
import { useAuthStore } from '@/stores/auth'
import '@fortawesome/fontawesome-free/css/all.min.css'

import GlobalComponents from "@/plugins/global-components";

async function bootstrap() {
  const app = createApp(App)

  const pinia = createPinia()
  app.use(pinia)
  app.use(PrimeVue, {
    theme: {
      preset: Aura,
      options: {
        darkModeSelector: 'light',
        cssLayer: false,
        unstyled: true
      }
    }
  })
  app.use(GlobalComponents)

  const auth = useAuthStore()

  if (auth.token) {
    try {
      await auth.getProfile()
    } catch (e) {
      auth.logout()
    }
  }


  app.use(router)

  await router.isReady()

  app.mount('#app')
}

bootstrap()
