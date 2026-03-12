<template>
  <div class="w-full max-w-full overflow-x-hidden px-4 py-4 sm:p-6">
    <!-- Header -->
    <div class="mb-4 sm:mb-6">
      <h1 class="text-xl sm:text-3xl font-bold text-gray-900 mb-1 sm:mb-2">Jurnal Transaksi</h1>
      <p class="text-sm sm:text-base text-gray-600">Daftar transaksi dalam format jurnal akuntansi</p>
    </div>

    <!-- Filter Periode -->
    <div class="mb-4 sm:mb-6 border border-gray-200 rounded-lg bg-white shadow-sm">
      <div class="p-4">
        <div class="grid grid-cols-1 sm:flex sm:flex-wrap gap-3 sm:gap-4 sm:items-end">
          <div class="w-full sm:flex-1 sm:min-w-[200px]">
            <label class="block text-sm font-medium text-gray-700 mb-2">Periode</label>
            <div class="grid grid-cols-2 gap-2">
              <input
                v-model="filter.tglAwal"
                type="date"
                class="w-full px-3 py-2.5 sm:py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
              />
              <input
                v-model="filter.tglAkhir"
                type="date"
                class="w-full px-3 py-2.5 sm:py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
              />
            </div>
          </div>
          <div class="flex gap-2">
            <button
              type="button"
              @click="fetchData"
              :disabled="loading"
              class="flex-1 sm:flex-none px-4 py-2.5 sm:py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 flex items-center justify-center gap-2 min-h-[44px] sm:min-h-0"
            >
              <i class="fas fa-filter"></i>
              <span>{{ loading ? 'Memuat...' : 'Filter' }}</span>
            </button>
            <button
              type="button"
              @click="resetFilter"
              class="flex-1 sm:flex-none px-4 py-2.5 sm:py-2 border border-gray-300 rounded-lg hover:bg-gray-50 flex items-center justify-center gap-2 min-h-[44px] sm:min-h-0"
            >
              <i class="fas fa-redo"></i>
              <span>Reset</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex flex-col justify-center items-center py-8 sm:py-12">
      <i class="fas fa-spinner fa-spin text-3xl sm:text-4xl text-gray-400 mb-3 sm:mb-4"></i>
      <p class="text-sm sm:text-base text-gray-600">Memuat jurnal transaksi...</p>
    </div>

    <!-- Error -->
    <div v-if="!loading && error" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
      <div class="flex items-center gap-2 text-red-800">
        <i class="fas fa-exclamation-circle"></i>
        <p class="font-semibold">Error: {{ error }}</p>
      </div>
    </div>

    <!-- Content -->
    <div v-if="!loading && !error">
      <div
        v-if="entries.length === 0"
        class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-lg flex items-start gap-3"
      >
        <i class="fas fa-info-circle text-amber-600 mt-0.5"></i>
        <div class="text-sm text-amber-800">
          <p class="font-medium">Belum ada data jurnal transaksi.</p>
          <p class="mt-1">
            Data jurnal akan terisi dari transaksi Umrah, Haji, Edutrip, dan Permintaan Custom
            sesuai nominal transaksi yang tercatat, baik yang sudah lunas maupun yang masih berjalan.
          </p>
        </div>
      </div>

      <!-- Daftar Jurnal -->
      <div class="border border-gray-200 rounded-lg bg-white shadow-sm overflow-hidden">
        <div class="p-3 sm:p-4 bg-gray-50 border-b border-gray-200">
          <h3 class="text-base sm:text-lg font-semibold text-gray-900 flex items-center gap-2">
            <i class="fas fa-book text-gray-600"></i>
            Daftar Jurnal Transaksi
          </h3>
        </div>
        <div class="p-3 sm:p-4">
          <div v-if="entries.length === 0 && !loading" class="text-center py-8 text-gray-500">
            <i class="fas fa-book-open text-3xl sm:text-4xl mb-2"></i>
            <p class="font-medium text-sm sm:text-base">Tidak ada data jurnal</p>
            <p class="text-xs sm:text-sm mt-2">Gunakan filter periode lalu klik Filter untuk memuat data.</p>
          </div>

          <!-- Mobile: kartu per entri -->
          <div v-else-if="isMobile" class="space-y-3">
            <div
              v-for="(row, idx) in paginatedEntries"
              :key="idx"
              class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm"
            >
              <p class="text-xs text-gray-500 mb-0.5">{{ formatDate(row.tanggal) }}</p>
              <p class="font-mono text-sm font-semibold text-gray-900 truncate">{{ row.referensi || '-' }}</p>
              <p class="text-sm text-gray-700 line-clamp-2 mt-0.5">{{ row.keterangan }}</p>
              <div class="mt-2">
                <span
                  :class="{
                    'bg-blue-100 text-blue-800': row.jenis === 'Umrah',
                    'bg-green-100 text-green-800': row.jenis === 'Haji',
                    'bg-purple-100 text-purple-800': row.jenis === 'Edutrip',
                  }"
                  class="px-2 py-0.5 rounded text-xs font-semibold"
                >
                  {{ row.jenis }}
                </span>
              </div>
              <div class="flex justify-between items-center mt-3 pt-3 border-t border-gray-100 text-sm">
                <span class="text-gray-600">Debit</span>
                <span v-if="row.debit > 0" class="font-semibold text-gray-900">{{ formatCurrency(row.debit) }}</span>
                <span v-else class="text-gray-400">—</span>
              </div>
              <div class="flex justify-between items-center mt-1 text-sm">
                <span class="text-gray-600">Kredit</span>
                <span v-if="row.kredit > 0" class="font-semibold text-green-600">{{ formatCurrency(row.kredit) }}</span>
                <span v-else class="text-gray-400">—</span>
              </div>
            </div>
            <!-- Pagination mobile -->
            <div v-if="entries.length > 0 && totalPages > 1" class="flex items-center justify-center gap-2 pt-4 pb-2">
              <button
                type="button"
                :disabled="page <= 1"
                @click="page = Math.max(1, page - 1)"
                class="px-3 py-2 rounded-lg border border-gray-300 text-sm font-medium disabled:opacity-50 min-h-[44px]"
              >
                Prev
              </button>
              <span class="text-sm text-gray-600 px-2">{{ page }} / {{ totalPages }}</span>
              <button
                type="button"
                :disabled="page >= totalPages"
                @click="page = Math.min(totalPages, page + 1)"
                class="px-3 py-2 rounded-lg border border-gray-300 text-sm font-medium disabled:opacity-50 min-h-[44px]"
              >
                Next
              </button>
            </div>
          </div>

          <!-- Desktop: tabel -->
          <div v-else>
            <div class="overflow-x-auto -mx-2 sm:mx-0 rounded-lg">
              <table class="w-full text-sm text-left border-collapse">
                <thead>
                  <tr class="border-b border-gray-200 bg-gray-50">
                    <th class="px-3 sm:px-4 py-3 font-semibold text-gray-700">Tanggal</th>
                    <th class="px-3 sm:px-4 py-3 font-semibold text-gray-700">No. Referensi</th>
                    <th class="px-3 sm:px-4 py-3 font-semibold text-gray-700">Keterangan</th>
                    <th class="px-3 sm:px-4 py-3 font-semibold text-gray-700">Jenis</th>
                    <th class="px-3 sm:px-4 py-3 font-semibold text-gray-700 text-right">Debit</th>
                    <th class="px-3 sm:px-4 py-3 font-semibold text-gray-700 text-right">Kredit</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="(row, idx) in paginatedEntries"
                    :key="idx"
                    class="border-b border-gray-100 hover:bg-gray-50"
                  >
                    <td class="px-3 sm:px-4 py-3 text-gray-900">{{ formatDate(row.tanggal) }}</td>
                    <td class="px-3 sm:px-4 py-3 font-mono text-sm">{{ row.referensi || '-' }}</td>
                    <td class="px-3 sm:px-4 py-3 text-gray-700">{{ row.keterangan }}</td>
                    <td class="px-3 sm:px-4 py-3">
                      <span
                        :class="{
                          'bg-blue-100 text-blue-800': row.jenis === 'Umrah',
                          'bg-green-100 text-green-800': row.jenis === 'Haji',
                          'bg-purple-100 text-purple-800': row.jenis === 'Edutrip',
                        }"
                        class="px-2 py-1 rounded text-xs font-semibold"
                      >
                        {{ row.jenis }}
                      </span>
                    </td>
                    <td class="px-3 sm:px-4 py-3 text-right">
                      <span v-if="row.debit > 0" class="font-semibold text-gray-900">{{ formatCurrency(row.debit) }}</span>
                      <span v-else class="text-gray-400">—</span>
                    </td>
                    <td class="px-3 sm:px-4 py-3 text-right">
                      <span v-if="row.kredit > 0" class="font-semibold text-green-600">{{ formatCurrency(row.kredit) }}</span>
                      <span v-else class="text-gray-400">—</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <!-- Pagination desktop -->
            <div
              v-if="entries.length > 0"
              class="flex flex-col sm:flex-row items-center justify-between gap-2 py-3 mt-4 pt-4 border-t border-gray-200 bg-gray-50 rounded-b-lg"
            >
              <span class="text-xs sm:text-sm text-gray-600 order-2 sm:order-1">
                Menampilkan {{ (page - 1) * rowsPerPage + 1 }}–{{ Math.min(page * rowsPerPage, entries.length) }} dari {{ entries.length }}
              </span>
              <div class="flex items-center gap-2 order-1 sm:order-2">
                <button
                  type="button"
                  :disabled="page <= 1"
                  @click="page = Math.max(1, page - 1)"
                  class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-100 disabled:opacity-50 text-sm min-h-[44px] sm:min-h-0"
                >
                  Sebelumnya
                </button>
                <span class="text-sm text-gray-600">Halaman {{ page }} / {{ totalPages }}</span>
                <button
                  type="button"
                  :disabled="page >= totalPages"
                  @click="page = Math.min(totalPages, page + 1)"
                  class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-100 disabled:opacity-50 text-sm min-h-[44px] sm:min-h-0"
                >
                  Berikutnya
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useApi } from '@/api/useApi'

const api = useApi()
const loading = ref(false)
const error = ref('')
const isMobile = ref(false)
function updateIsMobile() {
  isMobile.value = typeof window !== 'undefined' && window.innerWidth < 768
}

const filter = ref({
  tglAwal: '',
  tglAkhir: '',
})

interface JurnalEntry {
  tanggal: string
  referensi: string
  keterangan: string
  jenis: string
  debit: number
  kredit: number
}

const entries = ref<JurnalEntry[]>([])

const page = ref(1)
const rowsPerPage = ref(10)
const totalPages = computed(() =>
  Math.max(1, Math.ceil(entries.value.length / rowsPerPage.value))
)
const paginatedEntries = computed(() => {
  const start = (page.value - 1) * rowsPerPage.value
  return entries.value.slice(start, start + rowsPerPage.value)
})

function getListFromResponse(body: any): any[] {
  if (body == null || typeof body !== 'object') return []
  if (Array.isArray(body.data)) return body.data
  if (Array.isArray(body)) return body
  if (typeof body === 'object' && body.data != null && typeof body.data === 'object' && !Array.isArray(body.data))
    return Object.values(body.data)
  return []
}

const fetchData = async () => {
  try {
    loading.value = true
    error.value = ''

    const { tglAwal, tglAkhir } = filter.value
    const qs = tglAwal && tglAkhir ? `?tglAwal=${tglAwal}&tglAkhir=${tglAkhir}` : ''

    const [umrahRes, hajiRes, edutripRes, customRes] = await Promise.all([
      api.get(`/sistem-admin/transaksi/get-list-pemesanan-umrah${qs}`),
      api.get(`/sistem-admin/transaksi/get-list-pendaftaran-haji${qs}`),
      api.get(`/sistem-admin/transaksi/get-list-peminat-edutrip${qs}`),
      api.get(`/sistem-admin/transaksi/get-list-permintaan-custom${qs}`),
    ])

    const umrahList = getListFromResponse(umrahRes)
    const hajiList = getListFromResponse(hajiRes)
    const edutripList = getListFromResponse(edutripRes)
    const customList = getListFromResponse(customRes)

    const toAmount = (t: any): number => {
      // Utamakan nominal yang sudah diverifikasi admin jika tersedia
      if (t.total_pembayaran_verified != null) {
        const verified = Number(t.total_pembayaran_verified) || 0
        if (verified > 0) return verified
      }

      let biaya = Number(t.total_biaya) || 0
      if (biaya === 0 && t.snapshot_produk) {
        try {
          const snapshot =
            typeof t.snapshot_produk === 'string' ? JSON.parse(t.snapshot_produk) : t.snapshot_produk
          const jamaahCount = Array.isArray(t.jamaah_data) ? t.jamaah_data.length : 1
          biaya = Number(snapshot?.harga_per_pax || 0) * jamaahCount
        } catch {
          // ignore
        }
      }
      return biaya
    }

    const mapToJurnal = (
      list: any[],
      jenis: string
    ): JurnalEntry[] =>
      list.map((t: any) => {
        const amount = toAmount(t)
        const tanggal = t.tanggal_transaksi || t.created_at || t.tgl_pemesanan || ''
        const referensi = t.kode_transaksi || t.nomor_pembayaran || '-'
        const nama = t.nama_lengkap || t.nama || '-'
        return {
          tanggal,
          referensi,
          keterangan: `Pendapatan ${jenis} - ${nama}`,
          jenis,
          debit: 0,
          kredit: amount,
        }
      })

    let all: JurnalEntry[] = [
      ...mapToJurnal(umrahList, 'Umrah'),
      ...mapToJurnal(hajiList, 'Haji'),
      ...mapToJurnal(edutripList, 'Edutrip'),
      ...mapToJurnal(customList, 'Custom'),
    ]

    if (filter.value.tglAwal && filter.value.tglAkhir) {
      const tglAwal = new Date(filter.value.tglAwal)
      const tglAkhir = new Date(filter.value.tglAkhir)
      tglAkhir.setHours(23, 59, 59, 999)
      all = all.filter((e) => {
        const tgl = new Date(e.tanggal || 0)
        return tgl >= tglAwal && tgl <= tglAkhir
      })
    }

    entries.value = all.sort((a, b) => {
      const dateA = new Date(a.tanggal || 0)
      const dateB = new Date(b.tanggal || 0)
      return dateB.getTime() - dateA.getTime()
    })
    page.value = 1
  } catch (err: any) {
    console.error('Error fetching jurnal:', err)
    error.value =
      err?.response?.data?.message || err?.message || 'Gagal memuat jurnal transaksi'
    entries.value = []
  } finally {
    loading.value = false
  }
}

const formatCurrency = (amount: number) =>
  new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(amount)

const formatDate = (date: string | null) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

const resetFilter = () => {
  filter.value = { tglAwal: '', tglAkhir: '' }
  fetchData()
}

onMounted(() => {
  updateIsMobile()
  if (typeof window !== 'undefined') window.addEventListener('resize', updateIsMobile)
  filter.value = { tglAwal: '', tglAkhir: '' }
  fetchData()
})
onUnmounted(() => {
  if (typeof window !== 'undefined') window.removeEventListener('resize', updateIsMobile)
})
</script>
