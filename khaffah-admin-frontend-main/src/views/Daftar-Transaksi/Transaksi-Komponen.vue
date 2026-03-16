<template>
  <div class="w-full max-w-full overflow-x-hidden">
    <Card class="w-full max-w-full px-4 sm:px-6 py-4 sm:py-6 box-border" elevated radius="smooth" padding="md">
      <h1 class="text-lg sm:text-xl font-medium mb-3 sm:mb-4">{{ pageTitle }}</h1>
      <p class="text-sm text-gray-500 mb-4">{{ pageDescription }}</p>

      <!-- Tabs status -->
      <div class="flex gap-2 sm:gap-3 overflow-x-auto pb-2 mb-4 border-b border-gray-200 -mx-1 px-1 scrollbar-hide">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          @click="changeTab(tab.id)"
          class="flex-shrink-0 px-2.5 sm:px-3 py-2 sm:py-1 flex items-center gap-1.5 sm:gap-2 relative whitespace-nowrap text-xs sm:text-sm"
          :class="activeTab === tab.id ? tab.activeClass : 'text-gray-500'"
        >
          {{ tab.label }}
        </button>
      </div>

      <!-- Filter -->
      <div class="grid grid-cols-1 md:grid-cols-12 gap-3 mb-4 md:items-end">
        <div class="hidden md:block md:col-span-7"></div>
        <div class="md:col-span-3">
          <Field class="w-full" label="Tanggal Pemesanan (Opsional)" id="tgl-komponen" :horizontal="!isMobile">
            <DatePicker
              v-model="item.tanggalPemesanan"
              showIcon
              fluid
              iconDisplay="input"
              selectionMode="range"
              dateFormat="dd/mm/yy"
              :manualInput="false"
              placeholder="Pilih tanggal (kosongkan = semua)"
            />
          </Field>
        </div>
        <div class="md:col-span-2">
          <Button class="w-full md:w-auto md:mb-1" color="success" @click="fetchData()" darkOutlined icon="fas fa-search" :hide-text-on-mobile="true">
            Cari Data
          </Button>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loadSearch" class="flex justify-center py-12">
        <i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i>
      </div>

      <!-- Satu tabel dengan kolom Komponen -->
      <template v-else>
        <div v-if="flatItems.length === 0" class="text-center text-gray-500 py-12 text-sm">
          Tidak ada data transaksi komponen untuk filter ini.
        </div>

        <div v-else class="rounded-xl border border-gray-200 bg-white overflow-hidden shadow-sm">
          <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
              <thead>
                <tr class="border-b border-gray-200 bg-gray-50/80">
                  <th class="px-4 py-3 font-semibold text-gray-700 w-12">No</th>
                  <th class="px-4 py-3 font-semibold text-gray-700">Komponen</th>
                  <th class="px-4 py-3 font-semibold text-gray-700">Kode Transaksi</th>
                  <th class="px-4 py-3 font-semibold text-gray-700 whitespace-nowrap">Tanggal Pemesanan</th>
                  <th class="px-4 py-3 font-semibold text-gray-700">Nama Lengkap</th>
                  <th class="px-4 py-3 font-semibold text-gray-700">No WhatsApp</th>
                  <th class="px-4 py-3 font-semibold text-gray-700">Status</th>
                  <th class="px-4 py-3 font-semibold text-gray-700 text-center w-24">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(row, rIdx) in flatItems"
                  :key="row.id"
                  class="border-b border-gray-100 hover:bg-gray-50/50 transition-colors"
                >
                  <td class="px-4 py-3 text-gray-600">{{ rIdx + 1 }}</td>
                  <td class="px-4 py-3 text-gray-800 font-medium">{{ row.komponen }}</td>
                  <td class="px-4 py-3 font-medium text-gray-900">{{ row.kode_transaksi }}</td>
                  <td class="px-4 py-3 text-gray-700 whitespace-nowrap">
                    {{ row.tgl_pemesanan ? H.formatDate(row.tgl_pemesanan, 'DD/MM/YYYY') : '–' }}
                  </td>
                  <td class="px-4 py-3 text-gray-800">{{ (row.gelar || '') + ' ' + (row.nama_lengkap || '') }}</td>
                  <td class="px-4 py-3">
                    <div class="flex items-center gap-2">
                      <span>{{ row.no_whatsapp || '–' }}</span>
                      <a
                        v-if="row.no_whatsapp"
                        :href="`https://wa.me/${String(row.no_whatsapp).replace(/^0/, '62').replace(/\D/g, '')}`"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center justify-center w-7 h-7 bg-green-500 hover:bg-green-600 rounded-full transition-colors"
                        title="Chat via WhatsApp"
                      >
                        <i class="fab fa-whatsapp text-white text-sm"></i>
                      </a>
                    </div>
                  </td>
                  <td class="px-4 py-3">
                    <span class="inline-flex items-center text-xs px-2.5 py-1 rounded-full bg-gray-100 text-gray-700">
                      {{ row.nama_status || row.status_nama || '–' }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-center">
                    <button
                      type="button"
                      class="px-3 py-1.5 text-xs font-medium text-[#007b6f] border border-[#007b6f] rounded-lg hover:bg-[#007b6f]/10 transition-colors"
                      @click="lihatDetail(row.id)"
                    >
                      Detail
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </template>
    </Card>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import DatePicker from 'primevue/datepicker'
import { useApi } from '@/api/useApi'
import H from '@/utils/appHelper'
import Card from '@/components/base/card/Card.vue'
import Button from '@/components/base/button/Button.vue'
import Field from '@/components/base/form/Field.vue'

const router = useRouter()
const route = useRoute()
const api = useApi()

const loadSearch = ref(false)
const activeTab = ref('belum')
const item = ref({ tanggalPemesanan: null as any })
const groupsRaw = ref<{ name: string; items: any[] }[]>([])

/** Filter dari URL (sidebar: Pemesanan Hotel → "Komponen Hotel", Badal Haji → "Badal Haji") */
const filterTipe = computed(() => (route.query.filter as string) || '')

/** Groups yang ditampilkan: satu grup jika ada filter, semua jika tidak */
const groups = computed(() => {
  const list = groupsRaw.value
  if (!filterTipe.value) return list
  return list.filter((g) => g.name === filterTipe.value)
})

/** Satu list datar untuk satu tabel, tiap baris punya field komponen */
const flatItems = computed(() => {
  const out: { komponen: string; [k: string]: any }[] = []
  for (const g of groups.value) {
    for (const row of g.items) {
      out.push({ ...row, komponen: g.name })
    }
  }
  return out
})

/** Judul halaman sesuai filter */
const pageTitle = computed(() => (filterTipe.value ? filterTipe.value : 'Transaksi Komponen'))
const pageDescription = computed(() =>
  filterTipe.value
    ? `Data transaksi untuk ${filterTipe.value}.`
    : 'Data transaksi komponen (Hotel, Visa, Badal Haji, dll) dalam satu daftar.'
)

const isMobile = ref(false)
function updateIsMobile() {
  isMobile.value = typeof window !== 'undefined' && window.innerWidth < 768
}

const tabs = [
  { id: 'belum', label: 'Belum di Proses', activeClass: 'font-bold text-amber-600 border-b-2 border-amber-600 -mb-0.5 pb-2' },
  { id: 'diproses', label: 'Diproses', activeClass: 'font-bold text-blue-600 border-b-2 border-blue-600 -mb-0.5 pb-2' },
  { id: 'selesai', label: 'Selesai', activeClass: 'font-bold text-green-600 border-b-2 border-green-600 -mb-0.5 pb-2' },
  { id: 'batal', label: 'Batal', activeClass: 'font-bold text-red-600 border-b-2 border-red-600 -mb-0.5 pb-2' },
]

const changeTab = (tab: string) => {
  activeTab.value = tab
  fetchData()
}

const fetchData = async () => {
  try {
    loadSearch.value = true
    const { dateStart, dateEnd } = H.parseDateRange(item.value.tanggalPemesanan)
    const params = new URLSearchParams()
    params.set('status', activeTab.value)
    if (dateStart && dateEnd) {
      params.set('tglAwal', H.formatDate(dateStart, 'YYYY-MM-DD'))
      params.set('tglAkhir', H.formatDate(dateEnd, 'YYYY-MM-DD'))
    }
    const response = await api.get(`/sistem-admin/transaksi/get-list-transaksi-komponen-grouped?${params.toString()}`) as any
    const data = response?.data
    groupsRaw.value = Array.isArray(data?.groups) ? data.groups : []
  } catch (error: any) {
    console.error('Error fetching Transaksi Komponen:', error)
    const isNetwork = !error?.response && (error?.code === 'ERR_NETWORK' || /network|failed to fetch|unable to connect/i.test(error?.message || ''))
    if (isNetwork) {
      alert('Tidak dapat terhubung ke server. Pastikan backend API berjalan dan VITE_API_BASE_URL mengarah ke URL backend (mis. http://localhost:8000/api).')
    } else {
      alert(error?.response?.data?.message ?? 'Gagal memuat data Transaksi Komponen.')
    }
    groupsRaw.value = []
  } finally {
    loadSearch.value = false
  }
}

const lihatDetail = (orderId: number | string) => {
  router.push(`/Daftar-Transaksi/Transaksi-Komponen/${orderId}`)
}

onMounted(() => {
  updateIsMobile()
  if (typeof window !== 'undefined') {
    window.addEventListener('resize', updateIsMobile)
  }
  fetchData()
})

onUnmounted(() => {
  if (typeof window !== 'undefined') {
    window.removeEventListener('resize', updateIsMobile)
  }
})
</script>
