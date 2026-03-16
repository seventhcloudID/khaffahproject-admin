<template>
  <div class="p-4 sm:p-6 space-y-4 sm:space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-purple-600 to-purple-800 text-white p-4 sm:p-6 rounded-xl shadow-lg">
      <h1 class="text-2xl sm:text-3xl font-bold mb-1 sm:mb-2">📊 Dashboard Admin</h1>
      <p class="text-purple-100 text-sm sm:text-base">Selamat datang, {{ auth.user?.nama_lengkap || 'Admin' }}!</p>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center items-center py-12">
      <i class="fas fa-spinner fa-spin text-4xl text-gray-400"></i>
    </div>

    <!-- Error -->
    <div v-else-if="hasError" class="rounded-xl border border-red-200 bg-red-50 p-4 sm:p-6 text-center">
      <i class="fas fa-exclamation-triangle text-4xl sm:text-5xl text-red-500 mb-3 sm:mb-4"></i>
      <h2 class="text-lg sm:text-xl font-bold text-red-900 mb-2">Error Memuat Data</h2>
      <p class="text-red-700 mb-4 whitespace-pre-line text-xs sm:text-sm max-h-32 overflow-y-auto">{{ errorMessage }}</p>
      <button
        @click="fetchDashboardData"
        class="px-4 py-2.5 sm:py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 active:scale-[0.98] text-sm"
      >
        <i class="fas fa-redo mr-2"></i>Coba Lagi
      </button>
    </div>

    <!-- Content: hanya div + angka dari 1 ref -->
    <div v-else class="space-y-6">
      <!-- 3 kartu ringkasan -->
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 sm:gap-4">
        <div class="rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 text-white p-4 sm:p-6 shadow-lg flex items-center justify-between min-h-[88px] sm:min-h-0">
          <div class="min-w-0">
            <p class="text-blue-100 text-xs sm:text-sm truncate">Total Transaksi</p>
            <p class="text-2xl sm:text-4xl font-bold mt-0.5 sm:mt-1 tabular-nums">{{ display.total }}</p>
          </div>
          <i class="fas fa-chart-line text-3xl sm:text-5xl text-blue-200 opacity-50 flex-shrink-0 ml-2"></i>
        </div>
        <div class="rounded-xl bg-gradient-to-br from-green-500 to-green-600 text-white p-4 sm:p-6 shadow-lg flex items-center justify-between min-h-[88px] sm:min-h-0">
          <div class="min-w-0">
            <p class="text-green-100 text-xs sm:text-sm truncate">Transaksi Hari Ini</p>
            <p class="text-2xl sm:text-4xl font-bold mt-0.5 sm:mt-1 tabular-nums">{{ display.hariIni }}</p>
          </div>
          <i class="fas fa-calendar-day text-3xl sm:text-5xl text-green-200 opacity-50 flex-shrink-0 ml-2"></i>
        </div>
        <div class="rounded-xl bg-gradient-to-br from-purple-500 to-purple-600 text-white p-4 sm:p-6 shadow-lg flex items-center justify-between min-h-[88px] sm:min-h-0 sm:col-span-2 md:col-span-1">
          <div class="min-w-0">
            <p class="text-purple-100 text-xs sm:text-sm truncate">Transaksi Bulan Ini</p>
            <p class="text-2xl sm:text-4xl font-bold mt-0.5 sm:mt-1 tabular-nums">{{ display.bulanIni }}</p>
          </div>
          <i class="fas fa-calendar-alt text-3xl sm:text-5xl text-purple-200 opacity-50 flex-shrink-0 ml-2"></i>
        </div>
      </div>

      <!-- Transaksi per jenis -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
        <div class="rounded-xl border border-gray-200 bg-white shadow overflow-hidden">
          <div class="p-3 sm:p-4 bg-gradient-to-r from-blue-500 to-blue-600 text-white">
            <h3 class="text-base sm:text-lg font-semibold flex items-center gap-2">
              <i class="fas fa-kaaba"></i> Transaksi Umrah
            </h3>
          </div>
          <div class="p-3 sm:p-4">
            <p class="text-center text-2xl sm:text-3xl font-bold text-blue-600 mb-3 sm:mb-4 tabular-nums">{{ display.umrahTotal }}</p>
            <div class="space-y-1.5 sm:space-y-2 text-xs sm:text-sm">
              <div class="flex justify-between gap-2"><span class="text-gray-600 truncate">Belum Diproses</span><span class="font-semibold tabular-nums flex-shrink-0">{{ display.umrahBelum }}</span></div>
              <div class="flex justify-between gap-2"><span class="text-gray-600 truncate">Pembayaran</span><span class="font-semibold tabular-nums flex-shrink-0">{{ display.umrahPembayaran }}</span></div>
              <div class="flex justify-between gap-2"><span class="text-gray-600 truncate">Diproses</span><span class="font-semibold tabular-nums flex-shrink-0">{{ display.umrahDiproses }}</span></div>
              <div class="flex justify-between gap-2"><span class="text-gray-600 truncate">Berlangsung</span><span class="font-semibold tabular-nums flex-shrink-0">{{ display.umrahBerlangsung }}</span></div>
              <div class="flex justify-between gap-2"><span class="text-gray-600 truncate">Selesai</span><span class="font-semibold tabular-nums flex-shrink-0">{{ display.umrahSelesai }}</span></div>
              <div class="flex justify-between gap-2"><span class="text-gray-600 truncate">Batal</span><span class="font-semibold tabular-nums flex-shrink-0">{{ display.umrahBatal }}</span></div>
            </div>
            <button @click="router.push('/Daftar-Transaksi/Pemesanan-Paket-Umrah')" class="w-full mt-3 sm:mt-4 py-2.5 sm:py-2 border border-blue-500 text-blue-600 rounded-lg hover:bg-blue-50 text-sm font-medium active:scale-[0.98]">Lihat Detail</button>
          </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white shadow overflow-hidden">
          <div class="p-3 sm:p-4 bg-gradient-to-r from-green-500 to-green-600 text-white">
            <h3 class="text-base sm:text-lg font-semibold flex items-center gap-2">
              <i class="fas fa-mosque"></i> Pendaftaran Haji
            </h3>
          </div>
          <div class="p-3 sm:p-4">
            <p class="text-center text-2xl sm:text-3xl font-bold text-green-600 mb-3 sm:mb-4 tabular-nums">{{ display.hajiTotal }}</p>
            <div class="space-y-1.5 sm:space-y-2 text-xs sm:text-sm">
              <div class="flex justify-between gap-2"><span class="text-gray-600 truncate">Belum Diproses</span><span class="font-semibold tabular-nums flex-shrink-0">{{ display.hajiBelum }}</span></div>
              <div class="flex justify-between gap-2"><span class="text-gray-600 truncate">Diproses</span><span class="font-semibold tabular-nums flex-shrink-0">{{ display.hajiDiproses }}</span></div>
              <div class="flex justify-between gap-2"><span class="text-gray-600 truncate">Selesai</span><span class="font-semibold tabular-nums flex-shrink-0">{{ display.hajiSelesai }}</span></div>
              <div class="flex justify-between gap-2"><span class="text-gray-600 truncate">Batal</span><span class="font-semibold tabular-nums flex-shrink-0">{{ display.hajiBatal }}</span></div>
            </div>
            <button @click="router.push('/Daftar-Transaksi/Pendaftaran-Haji')" class="w-full mt-3 sm:mt-4 py-2.5 sm:py-2 border border-green-500 text-green-600 rounded-lg hover:bg-green-50 text-sm font-medium active:scale-[0.98]">Lihat Detail</button>
          </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white shadow overflow-hidden">
          <div class="p-3 sm:p-4 bg-gradient-to-r from-purple-500 to-purple-600 text-white">
            <h3 class="text-base sm:text-lg font-semibold flex items-center gap-2">
              <i class="fas fa-graduation-cap"></i> Peminat Edutrip
            </h3>
          </div>
          <div class="p-3 sm:p-4">
            <p class="text-center text-2xl sm:text-3xl font-bold text-purple-600 mb-3 sm:mb-4 tabular-nums">{{ display.edutripTotal }}</p>
            <div class="space-y-1.5 sm:space-y-2 text-xs sm:text-sm">
              <div class="flex justify-between gap-2"><span class="text-gray-600 truncate">Belum Diproses</span><span class="font-semibold tabular-nums flex-shrink-0">{{ display.edutripBelum }}</span></div>
              <div class="flex justify-between gap-2"><span class="text-gray-600 truncate">Diproses</span><span class="font-semibold tabular-nums flex-shrink-0">{{ display.edutripDiproses }}</span></div>
              <div class="flex justify-between gap-2"><span class="text-gray-600 truncate">Selesai</span><span class="font-semibold tabular-nums flex-shrink-0">{{ display.edutripSelesai }}</span></div>
              <div class="flex justify-between gap-2"><span class="text-gray-600 truncate">Batal</span><span class="font-semibold tabular-nums flex-shrink-0">{{ display.edutripBatal }}</span></div>
            </div>
            <button @click="router.push('/Daftar-Transaksi/Peminat-Edutrip')" class="w-full mt-3 sm:mt-4 py-2.5 sm:py-2 border border-purple-500 text-purple-600 rounded-lg hover:bg-purple-50 text-sm font-medium active:scale-[0.98]">Lihat Detail</button>
          </div>
        </div>
      </div>

      <!-- Paket & User -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
        <div class="rounded-xl border border-gray-200 bg-white shadow overflow-hidden">
          <div class="p-3 sm:p-4 bg-gradient-to-r from-indigo-500 to-indigo-600 text-white">
            <h3 class="text-base sm:text-lg font-semibold flex items-center gap-2"><i class="fas fa-box"></i> Statistik Paket</h3>
          </div>
          <div class="p-3 sm:p-4 space-y-2 sm:space-y-3">
            <div class="flex justify-between items-center p-2.5 sm:p-3 bg-blue-50 rounded-lg gap-2">
              <span class="text-gray-700 font-medium text-sm sm:text-base truncate">Paket Umrah</span>
              <span class="text-lg sm:text-xl font-bold text-blue-600 tabular-nums flex-shrink-0">{{ display.paketUmrah }}</span>
            </div>
            <div class="flex justify-between items-center p-2.5 sm:p-3 bg-green-50 rounded-lg gap-2">
              <span class="text-gray-700 font-medium text-sm sm:text-base truncate">Paket Haji</span>
              <span class="text-lg sm:text-xl font-bold text-green-600 tabular-nums flex-shrink-0">{{ display.paketHaji }}</span>
            </div>
            <div class="flex justify-between items-center p-2.5 sm:p-3 bg-purple-50 rounded-lg gap-2">
              <span class="text-gray-700 font-medium text-sm sm:text-base truncate">Paket Edutrip</span>
              <span class="text-lg sm:text-xl font-bold text-purple-600 tabular-nums flex-shrink-0">{{ display.paketEdutrip }}</span>
            </div>
          </div>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white shadow overflow-hidden">
          <div class="p-3 sm:p-4 bg-gradient-to-r from-teal-500 to-teal-600 text-white">
            <h3 class="text-base sm:text-lg font-semibold flex items-center gap-2"><i class="fas fa-users"></i> Statistik User & Mitra</h3>
          </div>
          <div class="p-3 sm:p-4 space-y-2 sm:space-y-3">
            <div class="flex justify-between items-center p-2.5 sm:p-3 bg-gray-50 rounded-lg gap-2">
              <span class="text-gray-700 font-medium text-sm sm:text-base truncate">Total User</span>
              <span class="text-lg sm:text-xl font-bold text-gray-700 tabular-nums flex-shrink-0">{{ display.totalUser }}</span>
            </div>
            <div class="flex justify-between items-center p-2.5 sm:p-3 bg-green-50 rounded-lg gap-2">
              <span class="text-gray-700 font-medium text-sm sm:text-base truncate">Total Mitra</span>
              <span class="text-lg sm:text-xl font-bold text-green-600 tabular-nums flex-shrink-0">{{ display.totalMitra }}</span>
            </div>
            <div class="flex justify-between items-center p-2.5 sm:p-3 bg-yellow-50 rounded-lg gap-2">
              <span class="text-gray-700 font-medium text-sm sm:text-base truncate">Mitra Pending</span>
              <span class="text-lg sm:text-xl font-bold text-yellow-600 tabular-nums flex-shrink-0">{{ display.mitraPending }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Links -->
      <div class="rounded-xl border border-gray-200 bg-white shadow overflow-hidden">
        <div class="p-3 sm:p-4 bg-gray-700 text-white">
          <h3 class="text-base sm:text-lg font-semibold flex items-center gap-2"><i class="fas fa-link"></i> Quick Links</h3>
        </div>
        <div class="p-3 sm:p-4 grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
          <button @click="router.push('/Paket/Master-Umrah')" class="p-3 sm:p-4 bg-blue-50 hover:bg-blue-100 active:scale-[0.98] rounded-lg text-left border border-blue-200 min-h-[80px] sm:min-h-0">
            <i class="fas fa-kaaba text-blue-600 text-xl sm:text-2xl mb-1 sm:mb-2 block"></i>
            <p class="font-semibold text-gray-900 text-sm sm:text-base">Paket Umrah</p>
          </button>
          <button @click="router.push('/Paket/Master-Haji')" class="p-3 sm:p-4 bg-green-50 hover:bg-green-100 active:scale-[0.98] rounded-lg text-left border border-green-200 min-h-[80px] sm:min-h-0">
            <i class="fas fa-mosque text-green-600 text-xl sm:text-2xl mb-1 sm:mb-2 block"></i>
            <p class="font-semibold text-gray-900 text-sm sm:text-base">Paket Haji</p>
          </button>
          <button @click="router.push('/Paket/Master-Edutrip')" class="p-3 sm:p-4 bg-purple-50 hover:bg-purple-100 active:scale-[0.98] rounded-lg text-left border border-purple-200 min-h-[80px] sm:min-h-0">
            <i class="fas fa-graduation-cap text-purple-600 text-xl sm:text-2xl mb-1 sm:mb-2 block"></i>
            <p class="font-semibold text-gray-900 text-sm sm:text-base">Paket Edutrip</p>
          </button>
          <button @click="router.push('/paket-request')" class="p-3 sm:p-4 bg-amber-50 hover:bg-amber-100 active:scale-[0.98] rounded-lg text-left border border-amber-200 min-h-[80px] sm:min-h-0">
            <i class="fas fa-puzzle-piece text-amber-600 text-xl sm:text-2xl mb-1 sm:mb-2 block"></i>
            <p class="font-semibold text-gray-900 text-sm sm:text-base">Paket Request</p>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'
import { useApi } from '@/api/useApi'

const auth = useAuthStore()
const router = useRouter()
const api = useApi()

const loading = ref(true)
const hasError = ref(false)
const errorMessage = ref('')

// Satu ref berisi angka saja – diisi dari API, dipakai di template
const display = ref({
  total: 0,
  hariIni: 0,
  bulanIni: 0,
  umrahTotal: 0,
  umrahBelum: 0,
  umrahPembayaran: 0,
  umrahDiproses: 0,
  umrahBerlangsung: 0,
  umrahSelesai: 0,
  umrahBatal: 0,
  hajiTotal: 0,
  hajiBelum: 0,
  hajiDiproses: 0,
  hajiSelesai: 0,
  hajiBatal: 0,
  edutripTotal: 0,
  edutripBelum: 0,
  edutripDiproses: 0,
  edutripSelesai: 0,
  edutripBatal: 0,
  paketUmrah: 0,
  paketHaji: 0,
  paketEdutrip: 0,
  totalUser: 0,
  totalMitra: 0,
  mitraPending: 0,
})

function setDisplay (data: any) {
  const t = data?.transaksi ?? {}
  const u = t?.umrah ?? {}
  const h = t?.haji ?? {}
  const e = t?.edutrip ?? {}
  const p = data?.paket ?? {}
  const user = data?.user ?? {}
  display.value = {
    total: Number(t?.total ?? 0),
    hariIni: Number(t?.hari_ini ?? 0),
    bulanIni: Number(t?.bulan_ini ?? 0),
    umrahTotal: Number(u?.total ?? 0),
    umrahBelum: Number(u?.belum_diproses ?? 0),
    umrahPembayaran: Number(u?.pembayaran ?? 0),
    umrahDiproses: Number(u?.diproses ?? 0),
    umrahBerlangsung: Number(u?.berlangsung ?? 0),
    umrahSelesai: Number(u?.selesai ?? 0),
    umrahBatal: Number(u?.batal ?? 0),
    hajiTotal: Number(h?.total ?? 0),
    hajiBelum: Number(h?.belum_diproses ?? 0),
    hajiDiproses: Number(h?.diproses ?? 0),
    hajiSelesai: Number(h?.selesai ?? 0),
    hajiBatal: Number(h?.batal ?? 0),
    edutripTotal: Number(e?.total ?? 0),
    edutripBelum: Number(e?.belum_diproses ?? 0),
    edutripDiproses: Number(e?.diproses ?? 0),
    edutripSelesai: Number(e?.selesai ?? 0),
    edutripBatal: Number(e?.batal ?? 0),
    paketUmrah: Number(p?.umrah ?? 0),
    paketHaji: Number(p?.haji ?? 0),
    paketEdutrip: Number(p?.edutrip ?? 0),
    totalUser: Number(user?.total_user ?? 0),
    totalMitra: Number(user?.total_mitra ?? 0),
    mitraPending: Number(user?.mitra_pending ?? 0),
  }
}

// API backend: GET /api/sistem-admin/dashboard/data (Laravel DashboardController::getDashboardData)
const DASHBOARD_API = '/sistem-admin/dashboard/data'

const fetchDashboardData = async () => {
  loading.value = true
  hasError.value = false
  errorMessage.value = ''
  try {
    const res = await api.get(DASHBOARD_API)
    // useApi mengembalikan response.data (body JSON). Backend: { status, message, data: { transaksi, paket, user } }
    const data = res?.data ?? res
    if (!data || (data.transaksi == null && data.paket == null)) {
      throw new Error((res as unknown as Record<string, unknown>)?.message as string || 'Data dashboard tidak ditemukan')
    }
    setDisplay(data)
  } catch (err: any) {
    hasError.value = true
    errorMessage.value = err?.response?.data?.message ?? err?.message ?? 'Gagal memuat data dashboard. Pastikan backend Laravel berjalan (mis. php artisan serve) dan VITE_API_BASE_URL benar.'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchDashboardData()
})
</script>
