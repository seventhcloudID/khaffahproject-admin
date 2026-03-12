<template>
  <div
    class="flex h-screen bg-gradient-to-br from-teal-50 via-emerald-50 to-cyan-50 overflow-hidden"
  >
    <!-- Left Side - Branding with Islamic Theme -->
    <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-[#007b6f] via-[#005f56] to-[#004942] p-8 flex-col justify-between relative overflow-hidden">
      <!-- Islamic Geometric Pattern Background -->
      <div class="absolute inset-0 opacity-5">
        <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
          <defs>
            <pattern id="islamic-pattern" x="0" y="0" width="100" height="100" patternUnits="userSpaceOnUse">
              <path d="M50 0 L100 50 L50 100 L0 50 Z M25 25 L50 50 L25 75 M75 25 L50 50 L75 75" 
                    stroke="white" fill="none" stroke-width="1"/>
              <circle cx="50" cy="50" r="15" stroke="white" fill="none" stroke-width="1"/>
            </pattern>
          </defs>
          <rect width="100%" height="100%" fill="url(#islamic-pattern)"/>
        </svg>
      </div>

      <!-- Gradient Overlays -->
      <div class="absolute inset-0">
        <div class="absolute top-0 right-0 w-96 h-96 bg-gradient-to-br from-[#ffd700]/20 to-transparent rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 w-72 h-72 bg-white/10 rounded-full blur-3xl"></div>
      </div>

      <!-- Kaaba Silhouette -->
      <div class="absolute bottom-32 right-12 opacity-10">
        <svg width="200" height="200" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
          <!-- Kaaba -->
          <rect x="60" y="80" width="80" height="90" fill="white" stroke="white" stroke-width="2"/>
          <rect x="70" y="90" width="60" height="20" fill="white" opacity="0.6"/>
          <path d="M60 80 L100 50 L140 80" stroke="white" stroke-width="2" fill="white" opacity="0.8"/>
          <!-- Crescent Moon -->
          <path d="M30 40 Q30 20 50 20 Q35 20 35 40 Q35 60 50 60 Q30 60 30 40" fill="#ffd700" opacity="0.8"/>
          <circle cx="55" cy="25" r="4" fill="#ffd700" opacity="0.8"/>
        </svg>
      </div>

      <!-- Content -->
      <div class="relative z-10">
        <div class="flex items-center gap-3 mb-8">
          <div class="w-11 h-11 flex items-center justify-center ">
            <img :src="logo" alt="Logo" class="w-11 h-11" />
          </div>
          <div>
            <h1 class="text-xl font-bold text-white">Kaffah Admin</h1>
            <p class="text-teal-100 text-xs">Manajemen Haji & Umrah</p>
          </div>
        </div>

        <div class="space-y-4">
          <div class="inline-block">
            <div class="flex items-center gap-2 bg-[#ffd700]/20 backdrop-blur-sm px-3 py-1.5 rounded-full border border-[#ffd700]/30">
              <svg class="w-4 h-4 text-[#ffd700]" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
              </svg>
              <span class="text-[#ffd700] font-semibold text-xs">Sistem Terpercaya</span>
            </div>
          </div>
          
          <h2 class="text-3xl font-bold text-white leading-tight">
            Assalamu'alaikum,<br/>
            <span class="text-[#ffd700]">Selamat Datang</span>
          </h2>
          <p class="text-teal-100 text-base leading-relaxed">
            Kelola perjalanan ibadah haji dan umrah jamaah Anda dengan sistem manajemen yang mudah, aman, dan terpercaya.
          </p>
        </div>
      </div>

      <!-- Features with Islamic Icons -->
      <div class="relative z-10 grid grid-cols-2 gap-3">
        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-3 border border-white/20 hover:bg-white/15 transition-all duration-300">
          <div class="w-9 h-9 bg-gradient-to-br from-[#ffd700]/30 to-[#ffd700]/10 rounded-lg flex items-center justify-center mb-2">
            <svg class="w-5 h-5 text-[#ffd700]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
          </div>
          <p class="text-white font-semibold text-xs">Data Jamaah Aman</p>
          <p class="text-teal-200 text-[10px] mt-0.5">Terenkripsi & Terlindungi</p>
        </div>
        
        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-3 border border-white/20 hover:bg-white/15 transition-all duration-300">
          <div class="w-9 h-9 bg-gradient-to-br from-[#ffd700]/30 to-[#ffd700]/10 rounded-lg flex items-center justify-center mb-2">
            <svg class="w-5 h-5 text-[#ffd700]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
          </div>
          <p class="text-white font-semibold text-xs">Proses Cepat</p>
          <p class="text-teal-200 text-[10px] mt-0.5">Real-time & Efisien</p>
        </div>
        
        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-3 border border-white/20 hover:bg-white/15 transition-all duration-300">
          <div class="w-9 h-9 bg-gradient-to-br from-[#ffd700]/30 to-[#ffd700]/10 rounded-lg flex items-center justify-center mb-2">
            <svg class="w-5 h-5 text-[#ffd700]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
          </div>
          <p class="text-white font-semibold text-xs">Manajemen Jamaah</p>
          <p class="text-teal-200 text-[10px] mt-0.5">Lengkap & Terorganisir</p>
        </div>
        
        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-3 border border-white/20 hover:bg-white/15 transition-all duration-300">
          <div class="w-9 h-9 bg-gradient-to-br from-[#ffd700]/30 to-[#ffd700]/10 rounded-lg flex items-center justify-center mb-2">
            <svg class="w-5 h-5 text-[#ffd700]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
          </div>
          <p class="text-white font-semibold text-xs">Laporan Detail</p>
          <p class="text-teal-200 text-[10px] mt-0.5">Analitik Mendalam</p>
        </div>
      </div>

      <!-- Islamic Quote -->
      <div class="relative z-10 mt-4">
        <div class="bg-white/5 backdrop-blur-sm rounded-xl p-3 border border-white/10">
          <p class="text-[#ffd700] text-xs italic text-center font-arabic">"Dan sempurnakanlah ibadah haji dan umrah karena Allah"</p>
          <p class="text-teal-200 text-[10px] text-center mt-1">QS. Al-Baqarah: 196</p>
        </div>
      </div>
    </div>

    <!-- Right Side - Login Form -->
    <div class="flex-1 flex items-center justify-center p-8">
      <div class="w-full max-w-md">
        <!-- Mobile Logo -->
        <div class="lg:hidden flex items-center justify-center gap-3 mb-8">
          <div class="w-12 h-12 bg-gradient-to-br from-[#007b6f] to-[#005f56] rounded-xl flex items-center justify-center shadow-lg ring-2 ring-[#ffd700]/30">
            <svg class="w-7 h-7 text-[#ffd700]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
          </div>
          <div>
            <h1 class="text-xl font-bold text-[#007b6f]">Kaffah Admin</h1>
            <p class="text-gray-500 text-sm">Manajemen Haji & Umrah</p>
          </div>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 p-6">
          <div class="mb-6">
            <div class="inline-flex items-center gap-2 bg-[#007b6f]/5 px-3 py-1.5 rounded-full mb-4">
              <div class="w-2 h-2 bg-[#007b6f] rounded-full animate-pulse"></div>
              <span class="text-[#007b6f] text-xs font-semibold">SISTEM ADMIN</span>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 mb-1.5">Masuk</h2>
            <p class="text-gray-500">Masukkan kredensial Anda untuk mengakses dashboard</p>
          </div>

          <form @submit.prevent="handleLogin" class="space-y-4">
            <!-- Email Input -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Alamat Email
              </label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                  </svg>
                </div>
                <input
                  v-model="email"
                  type="email"
                  placeholder="admin@kaffah.com"
                  required
                  class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#007b6f] focus:border-transparent transition-all duration-200 outline-none"
                  :class="{ 'border-red-500 focus:ring-red-500': error }"
                />
              </div>
            </div>

            <!-- Password Input -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Password
              </label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                  </svg>
                </div>
                <input
                  v-model="password"
                  :type="showPassword ? 'text' : 'password'"
                  placeholder="••••••••"
                  required
                  class="w-full mb-2 pl-10 pr-12 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#007b6f] focus:border-transparent transition-all duration-200 outline-none"
                  :class="{ 'border-red-500 focus:ring-red-500': error }"
                />
                <button
                  type="button"
                  @click="showPassword = !showPassword"
                  class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors"
                >
                  <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                  <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                  </svg>
                </button>
              </div>
            </div>

            <!-- Error Message -->
            <transition
              enter-active-class="transition-all duration-300 ease-out"
              enter-from-class="opacity-0 -translate-y-2"
              enter-to-class="opacity-100 translate-y-0"
              leave-active-class="transition-all duration-200 ease-in"
              leave-from-class="opacity-100 translate-y-0"
              leave-to-class="opacity-0 -translate-y-2"
            >
              <div v-if="error" class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-start gap-3">
                <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                  <p class="text-sm font-medium text-red-800">Login Gagal</p>
                  <p class="text-sm text-red-600 mt-0.5">{{ error }}</p>
                </div>
              </div>
            </transition>

            <!-- Submit Button -->
            <button
              type="submit"
              :disabled="loading"
              class="w-full bg-gradient-to-r from-[#007b6f] to-[#005f56] hover:from-[#005f56] hover:to-[#004942] text-white font-semibold py-3 rounded-xl shadow-lg shadow-[#007b6f]/30 hover:shadow-xl hover:shadow-[#007b6f]/40 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 group"
            >
              <svg v-if="loading" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span v-if="loading">Sedang masuk...</span>
              <span v-else>Masuk ke Dashboard</span>
              <svg v-if="!loading" class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
              </svg>
            </button>
          </form>

          <!-- Footer -->
          <div class="mt-4 pt-4 border-t border-gray-100">
            <div class="flex items-center justify-center gap-2 text-sm text-gray-500">
              <svg class="w-4 h-4 text-[#007b6f]" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
              </svg>
              <span>Dilindungi dengan enkripsi SSL tingkat tinggi</span>
            </div>
          </div>
        </div>

        <!-- Additional Info -->
        <div class="mt-4 text-center">
          <p class="text-sm text-gray-500">
            Butuh bantuan? 
            <a href="#" class="text-[#007b6f] hover:text-[#005f56] font-medium transition-colors">
              Hubungi Support
            </a>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

import logo from '@/assets/Logo/LOGOMARK/Asset-82.png'

const email = ref('')
const password = ref('')
const error = ref('')
const loading = ref(false)
const showPassword = ref(false)
const auth = useAuthStore()
const router = useRouter()

async function handleLogin() {
  error.value = ''
  loading.value = true

  try {
    const res = await auth.login(email.value, password.value)
    const roles = res.role || res.roles || []
    const dashboardUrl = res.dashboard_url || '/dashboard' 

    if (roles.includes('superadmin')) {
      router.push(dashboardUrl)
    } else {
      router.push('/handling')
    }
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Email atau password salah. Silakan coba lagi.'
  } finally {
    loading.value = false
  }
}
</script>