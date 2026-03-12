import { fileURLToPath, URL } from 'node:url'
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'
import Components from 'unplugin-vue-components/vite'

// https://vite.dev/config/
export default defineConfig({
  plugins: [
    vue(),
    vueDevTools(),
    Components({
      // folder tempat komponen kamu disimpan
      dirs: ['src/components'],
      extensions: ['vue'],
      deep: true, // ⬅️ penting! biar bisa scan sampai folder dalam (seperti base/card/card.vue)
      dts: true,  // ⬅️ generate file deklarasi otomatis (components.d.ts)
    }),
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    },
  },
  server: {
    host: '127.0.0.1',
    port: 5173,
    strictPort: false,
    // Proxy /api ke backend Laravel (hindari CORS / Network Error di dev)
    proxy: {
      '/api': {
        target: 'http://127.0.0.1:8000',
        changeOrigin: true,
      },
    },
  },
})
