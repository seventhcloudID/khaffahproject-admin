<template>
  <div class="w-full max-w-full overflow-x-hidden px-4 py-4 sm:p-6">
    <!-- Header -->
    <div class="mb-4 sm:mb-6">
      <h1 class="text-xl sm:text-3xl font-bold text-gray-900 mb-1 sm:mb-2">Laporan Keuangan</h1>
      <p class="text-sm sm:text-base text-gray-600">Ringkasan keuangan dan laporan transaksi</p>
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
          <div class="flex gap-2 sm:gap-2">
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

    <!-- Loading State -->
    <div v-if="loading" class="flex flex-col justify-center items-center py-8 sm:py-12">
      <i class="fas fa-spinner fa-spin text-3xl sm:text-4xl text-gray-400 mb-3 sm:mb-4"></i>
      <p class="text-sm sm:text-base text-gray-600">Memuat data keuangan...</p>
    </div>

    <!-- Error State -->
    <div v-if="!loading && error" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
      <div class="flex items-center gap-2 text-red-800">
        <i class="fas fa-exclamation-circle"></i>
        <p class="font-semibold">Error: {{ error }}</p>
      </div>
    </div>

    <!-- Content -->
    <div v-if="!loading && !error">
      <!-- Info ketika tidak ada data -->
      <div
        v-if="summary.totalTransaksi === 0"
        class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-lg flex items-start gap-3"
      >
        <i class="fas fa-info-circle text-amber-600 mt-0.5"></i>
        <div class="text-sm text-amber-800">
          <p class="font-medium">Belum ada data transaksi di database.</p>
          <p class="mt-1">Transaksi Umrah, Haji, dan Edutrip akan muncul setelah ada data di tabel transaksi.</p>
        </div>
      </div>

      <!-- Ringkasan Keuangan -->
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 mb-4 sm:mb-6">
        <div class="border border-gray-200 rounded-lg overflow-hidden bg-white shadow-sm">
          <div class="p-3 sm:p-4 bg-gradient-to-br from-green-500 to-green-600 text-white rounded-lg">
            <div class="flex items-center justify-between">
              <div class="min-w-0 flex-1">
                <p class="text-green-100 text-xs sm:text-sm mb-0.5 sm:mb-1">Total Pendapatan</p>
                <p class="text-lg sm:text-2xl font-bold text-white truncate">{{ formatCurrency(summary.totalPendapatan || 0) }}</p>
              </div>
              <i class="fas fa-arrow-up text-2xl sm:text-3xl text-green-200 opacity-50 flex-shrink-0 ml-2"></i>
            </div>
          </div>
        </div>

        <div class="border border-gray-200 rounded-lg overflow-hidden bg-white shadow-sm">
          <div class="p-3 sm:p-4 text-white rounded-lg" style="background: linear-gradient(to bottom right, #ec4899, #db2777);">
            <div class="flex items-center justify-between">
              <div class="min-w-0 flex-1">
                <p class="text-pink-100 text-xs sm:text-sm mb-0.5 sm:mb-1">Total Pengeluaran</p>
                <p class="text-lg sm:text-2xl font-bold text-white truncate">{{ formatCurrency(summary.totalPengeluaran || 0) }}</p>
              </div>
              <i class="fas fa-arrow-down text-2xl sm:text-3xl text-pink-200 opacity-50 flex-shrink-0 ml-2"></i>
            </div>
          </div>
        </div>

        <div class="border border-gray-200 rounded-lg overflow-hidden bg-white shadow-sm">
          <div
            :class="
              summary.labaRugi >= 0
                ? 'bg-gradient-to-br from-blue-500 to-blue-600 text-white'
                : 'bg-gradient-to-br from-orange-500 to-orange-600 text-white'
            "
            class="p-3 sm:p-4 rounded-lg"
          >
            <div class="flex items-center justify-between">
              <div class="min-w-0 flex-1">
                <p :class="summary.labaRugi >= 0 ? 'text-blue-100' : 'text-orange-100'" class="text-xs sm:text-sm mb-0.5 sm:mb-1">Laba / Rugi</p>
                <p class="text-lg sm:text-2xl font-bold text-white truncate">{{ formatCurrency(summary.labaRugi || 0) }}</p>
              </div>
              <i
                :class="
                  summary.labaRugi >= 0
                    ? 'fas fa-chart-line text-2xl sm:text-3xl text-blue-200 opacity-50'
                    : 'fas fa-exclamation-triangle text-2xl sm:text-3xl text-orange-200 opacity-50'
                "
                class="flex-shrink-0 ml-2"
              ></i>
            </div>
          </div>
        </div>

        <div class="border border-gray-200 rounded-lg overflow-hidden bg-white shadow-sm">
          <div class="p-3 sm:p-4 bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-lg">
            <div class="flex items-center justify-between">
              <div class="min-w-0 flex-1">
                <p class="text-purple-100 text-xs sm:text-sm mb-0.5 sm:mb-1">Total Transaksi</p>
                <p class="text-lg sm:text-2xl font-bold text-white">{{ summary.totalTransaksi || 0 }}</p>
              </div>
              <i class="fas fa-receipt text-2xl sm:text-3xl text-purple-200 opacity-50 flex-shrink-0 ml-2"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Breakdown per Jenis Transaksi -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 mb-4 sm:mb-6">
        <div class="border border-gray-200 rounded-lg bg-white shadow-sm overflow-hidden">
          <div class="p-3 sm:p-4 bg-blue-50 border-b border-gray-200">
            <h3 class="text-base sm:text-lg font-semibold text-blue-900 flex items-center gap-2">
              <i class="fas fa-kaaba text-blue-600"></i>
              Transaksi Umrah
            </h3>
          </div>
          <div class="p-3 sm:p-4">
            <div class="space-y-2 sm:space-y-3">
              <div class="flex justify-between items-center text-sm sm:text-base">
                <span class="text-gray-600">Total Transaksi</span>
                <span class="font-bold text-gray-900">{{ summary.umrah.total || 0 }}</span>
              </div>
              <div class="flex justify-between items-center text-sm sm:text-base">
                <span class="text-gray-600">Total Pendapatan</span>
                <span class="font-bold text-green-600">{{ formatCurrency(summary.umrah.pendapatan || 0) }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="border border-gray-200 rounded-lg bg-white shadow-sm overflow-hidden">
          <div class="p-3 sm:p-4 bg-green-50 border-b border-gray-200">
            <h3 class="text-base sm:text-lg font-semibold text-green-900 flex items-center gap-2">
              <i class="fas fa-mosque text-green-600"></i>
              Transaksi Haji
            </h3>
          </div>
          <div class="p-3 sm:p-4">
            <div class="space-y-2 sm:space-y-3">
              <div class="flex justify-between items-center text-sm sm:text-base">
                <span class="text-gray-600">Total Transaksi</span>
                <span class="font-bold text-gray-900">{{ summary.haji.total || 0 }}</span>
              </div>
              <div class="flex justify-between items-center text-sm sm:text-base">
                <span class="text-gray-600">Total Pendapatan</span>
                <span class="font-bold text-green-600">{{ formatCurrency(summary.haji.pendapatan || 0) }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="border border-gray-200 rounded-lg bg-white shadow-sm overflow-hidden">
          <div class="p-3 sm:p-4 bg-purple-50 border-b border-gray-200">
            <h3 class="text-base sm:text-lg font-semibold text-purple-900 flex items-center gap-2">
              <i class="fas fa-graduation-cap text-purple-600"></i>
              Transaksi Edutrip
            </h3>
          </div>
          <div class="p-3 sm:p-4">
            <div class="space-y-2 sm:space-y-3">
              <div class="flex justify-between items-center text-sm sm:text-base">
                <span class="text-gray-600">Total Transaksi</span>
                <span class="font-bold text-gray-900">{{ summary.edutrip.total || 0 }}</span>
              </div>
              <div class="flex justify-between items-center text-sm sm:text-base">
                <span class="text-gray-600">Total Pendapatan</span>
                <span class="font-bold text-green-600">{{ formatCurrency(summary.edutrip.pendapatan || 0) }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="border border-gray-200 rounded-lg bg-white shadow-sm overflow-hidden">
          <div class="p-3 sm:p-4 bg-amber-50 border-b border-gray-200">
            <h3 class="text-base sm:text-lg font-semibold text-amber-900 flex items-center gap-2">
              <i class="fas fa-pencil-alt text-amber-600"></i>
              Permintaan Custom
            </h3>
          </div>
          <div class="p-3 sm:p-4">
            <div class="space-y-2 sm:space-y-3">
              <div class="flex justify-between items-center text-sm sm:text-base">
                <span class="text-gray-600">Total Transaksi</span>
                <span class="font-bold text-gray-900">{{ summary.custom.total || 0 }}</span>
              </div>
              <div class="flex justify-between items-center text-sm sm:text-base">
                <span class="text-gray-600">Total Pendapatan</span>
                <span class="font-bold text-green-600">{{ formatCurrency(summary.custom.pendapatan || 0) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Daftar Transaksi -->
      <div class="border border-gray-200 rounded-lg bg-white shadow-sm overflow-hidden">
        <div class="p-3 sm:p-4 bg-gray-50 border-b border-gray-200">
          <h3 class="text-base sm:text-lg font-semibold text-gray-900 flex items-center gap-2">
            <i class="fas fa-list text-gray-600"></i>
            Daftar Transaksi
          </h3>
        </div>
        <div class="p-3 sm:p-4">
          <div v-if="paginatedTransactions.length === 0 && !loading" class="text-center py-8 text-gray-500">
            <i class="fas fa-inbox text-3xl sm:text-4xl mb-2"></i>
            <p class="font-medium text-sm sm:text-base">Tidak ada data transaksi</p>
          </div>

          <!-- Mobile: kartu per transaksi -->
          <div v-else-if="isMobile" class="space-y-3">
            <div
              v-for="(row, idx) in paginatedTransactions"
              :key="row.id || idx"
              class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm"
            >
              <p class="text-xs text-gray-500 mb-0.5">{{ formatDate(row.tanggal_transaksi) }}</p>
              <p class="font-mono text-sm font-semibold text-gray-900 truncate">{{ row.kode_transaksi || '-' }}</p>
              <p class="text-sm text-gray-700 truncate mt-0.5">{{ row.nama_lengkap || '-' }}</p>
              <div class="flex flex-wrap gap-2 mt-2">
                <span
                  :class="{
                    'bg-blue-100 text-blue-800': row.jenis_transaksi === 'Umrah',
                    'bg-green-100 text-green-800': row.jenis_transaksi === 'Haji',
                    'bg-purple-100 text-purple-800': row.jenis_transaksi === 'Edutrip',
                  }"
                  class="px-2 py-0.5 rounded text-xs font-semibold"
                >
                  {{ row.jenis_transaksi }}
                </span>
                <span
                  :class="{
                    'bg-yellow-100 text-yellow-800': row.status_pembayaran === 'Menunggu Verifikasi',
                    'bg-green-100 text-green-800': row.status_pembayaran === 'Lunas',
                    'bg-red-100 text-red-800': row.status_pembayaran === 'Ditolak',
                    'bg-gray-100 text-gray-800': row.status_pembayaran === 'Belum Bayar',
                  }"
                  class="px-2 py-0.5 rounded text-xs font-semibold"
                >
                  {{ row.status_pembayaran || '-' }}
                </span>
              </div>
                <p class="text-sm font-semibold text-green-600 mt-2">
                  {{ formatCurrency(row.total_biaya || 0) }}
                </p>
            </div>
            <!-- Pagination mobile -->
            <div v-if="totalPages > 1" class="flex items-center justify-center gap-2 pt-4 pb-2">
              <button
                type="button"
                :disabled="currentPage <= 1"
                @click="currentPage = Math.max(1, currentPage - 1)"
                class="px-3 py-2 rounded-lg border border-gray-300 text-sm font-medium disabled:opacity-50 min-h-[44px]"
              >
                Prev
              </button>
              <span class="text-sm text-gray-600 px-2">{{ currentPage }} / {{ totalPages }}</span>
              <button
                type="button"
                :disabled="currentPage >= totalPages"
                @click="currentPage = Math.min(totalPages, currentPage + 1)"
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
                    <th class="px-3 sm:px-4 py-3 font-semibold text-gray-700">Kode Transaksi</th>
                    <th class="px-3 sm:px-4 py-3 font-semibold text-gray-700">Nama Pemesan</th>
                    <th class="px-3 sm:px-4 py-3 font-semibold text-gray-700">Jenis</th>
                    <th class="px-3 sm:px-4 py-3 font-semibold text-gray-700">Total Biaya</th>
                    <th class="px-3 sm:px-4 py-3 font-semibold text-gray-700">Status Pembayaran</th>
                    <th class="px-3 sm:px-4 py-3 font-semibold text-gray-700">Tanggal</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="(row, idx) in paginatedTransactions"
                    :key="row.id || idx"
                    class="border-b border-gray-100 hover:bg-gray-50"
                  >
                    <td class="px-3 sm:px-4 py-3 font-mono text-gray-900">{{ row.kode_transaksi || '-' }}</td>
                    <td class="px-3 sm:px-4 py-3 text-gray-900">{{ row.nama_lengkap || '-' }}</td>
                    <td class="px-3 sm:px-4 py-3">
                      <span
                        :class="{
                          'bg-blue-100 text-blue-800': row.jenis_transaksi === 'Umrah',
                          'bg-green-100 text-green-800': row.jenis_transaksi === 'Haji',
                          'bg-purple-100 text-purple-800': row.jenis_transaksi === 'Edutrip',
                        }"
                        class="px-2 py-1 rounded text-xs font-semibold"
                      >
                        {{ row.jenis_transaksi }}
                      </span>
                    </td>
                    <td class="px-3 sm:px-4 py-3 font-semibold text-green-600">
                      {{ formatCurrency(row.total_biaya || 0) }}
                    </td>
                    <td class="px-3 sm:px-4 py-3">
                      <span
                        :class="{
                          'bg-yellow-100 text-yellow-800': row.status_pembayaran === 'Menunggu Verifikasi',
                          'bg-green-100 text-green-800': row.status_pembayaran === 'Lunas',
                          'bg-red-100 text-red-800': row.status_pembayaran === 'Ditolak',
                          'bg-gray-100 text-gray-800': row.status_pembayaran === 'Belum Bayar',
                        }"
                        class="px-2 py-1 rounded text-xs font-semibold"
                      >
                        {{ row.status_pembayaran || '-' }}
                      </span>
                    </td>
                    <td class="px-3 sm:px-4 py-3 text-gray-600">{{ formatDate(row.tanggal_transaksi) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Pagination desktop -->
            <div v-if="totalPages > 1" class="flex flex-col sm:flex-row items-center justify-between gap-3 mt-4 pt-4 border-t border-gray-200">
              <div class="text-xs sm:text-sm text-gray-600 order-2 sm:order-1">
                Menampilkan {{ (currentPage - 1) * rowsPerPage + 1 }} - {{ Math.min(currentPage * rowsPerPage, transactions.length) }} dari {{ transactions.length }}
              </div>
              <div class="flex items-center gap-2 order-1 sm:order-2">
                <button
                  type="button"
                  :disabled="currentPage <= 1"
                  @click="currentPage--"
                  class="px-3 py-2 border rounded-lg disabled:opacity-50 hover:bg-gray-50 text-sm"
                >
                  Sebelumnya
                </button>
                <span class="px-2 text-sm text-gray-600">Halaman {{ currentPage }} / {{ totalPages }}</span>
                <button
                  type="button"
                  :disabled="currentPage >= totalPages"
                  @click="currentPage++"
                  class="px-3 py-2 border rounded-lg disabled:opacity-50 hover:bg-gray-50 text-sm"
                >
                  Selanjutnya
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
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
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

const summary = ref({
  totalPendapatan: 0,
  totalPengeluaran: 0,
  labaRugi: 0,
  totalTransaksi: 0,
  umrah: { total: 0, pendapatan: 0 },
  haji: { total: 0, pendapatan: 0 },
  edutrip: { total: 0, pendapatan: 0 },
  custom: { total: 0, pendapatan: 0 },
})

const transactions = ref<any[]>([])
const rowsPerPage = 10
const currentPage = ref(1)

const paginatedTransactions = computed(() => {
  const start = (currentPage.value - 1) * rowsPerPage
  return transactions.value.slice(start, start + rowsPerPage)
})

const totalPages = computed(() => Math.max(1, Math.ceil(transactions.value.length / rowsPerPage)))

watch(currentPage, () => {
  if (currentPage.value > totalPages.value) currentPage.value = totalPages.value
})

function getListFromResponse(body: any, key: string): any[] {
  if (body == null || typeof body !== 'object') return []
  const data = body.data
  if (data == null || typeof data !== 'object') return []
  const arr = data[key]
  return Array.isArray(arr) ? arr : []
}

function normalizeRow(t: any, jenis: string): any {
  let biaya = Number(t.total_biaya) || 0
  if (biaya === 0 && t.snapshot_produk) {
    try {
      const snapshot = typeof t.snapshot_produk === 'string' ? JSON.parse(t.snapshot_produk) : t.snapshot_produk
      if (snapshot && typeof snapshot === 'object') {
        const jamaahCount = Math.max(1, Array.isArray(t.jamaah_data) ? t.jamaah_data.length : 1)
        const hargaPerPax = Number(snapshot.harga_per_pax ?? snapshot.harga ?? 0)
        if (hargaPerPax > 0) {
          biaya = hargaPerPax * jamaahCount
        } else if (Number(snapshot.total_biaya ?? snapshot.total_harga ?? 0) > 0) {
          biaya = Number(snapshot.total_biaya ?? snapshot.total_harga)
        }
      }
    } catch {
      // ignore
    }
  }
  return {
    ...t,
    jenis_transaksi: jenis,
    status_pembayaran: t.status_pembayaran_nama || getStatusPembayaran(t.status_pembayaran_id),
    tanggal_transaksi: t.tanggal_transaksi || t.created_at || t.tgl_pemesanan,
    total_biaya: biaya,
    kode_transaksi: t.kode_transaksi || t.nomor_pembayaran || '-',
    nama_lengkap: t.nama_lengkap || t.nama || '-',
  }
}

// Sesuaikan dengan StatusPembayaranSeeder: 1=BELUM_BAYAR, 2=DP, 3=CICIL, 4=LUNAS, 5=REFUND
function getStatusPembayaran(statusId: number | null): string {
  const statusMap: Record<number, string> = {
    1: 'Belum Bayar',
    2: 'Sudah Bayar DP',
    3: 'Cicilan Berjalan',
    4: 'Lunas',
    5: 'Refund',
  }
  return statusMap[statusId || 0] || 'Belum Bayar'
}

function isLunas(t: any): boolean {
  return (
    t.status_pembayaran_id === 4 ||
    t.status_pembayaran_kode === 'LUNAS' ||
    String(t.status_pembayaran_nama || '').toLowerCase() === 'lunas'
  )
}

function sumPendapatan(list: any[]): number {
  return list.filter(isLunas).reduce((sum, t) => sum + (Number(normalizeRow(t, '').total_biaya) || 0), 0)
}

const fetchData = async () => {
  try {
    loading.value = true
    error.value = ''
    currentPage.value = 1

    const { tglAwal, tglAkhir } = filter.value
    const qs = tglAwal && tglAkhir ? `?tglAwal=${tglAwal}&tglAkhir=${tglAkhir}` : ''

    const res = await api.get(`/sistem-admin/transaksi/get-laporan-keuangan${qs}`)

    const umrahList = getListFromResponse(res, 'umrah')
    const hajiList = getListFromResponse(res, 'haji')
    const edutripList = getListFromResponse(res, 'edutrip')
    const customList = getListFromResponse(res, 'custom')

    const umrahRows = umrahList.map((t: any) => normalizeRow(t, 'Umrah'))
    const hajiRows = hajiList.map((t: any) => normalizeRow(t, 'Haji'))
    const edutripRows = edutripList.map((t: any) => normalizeRow(t, 'Edutrip'))
    const customRows = customList.map((t: any) => normalizeRow(t, 'Custom'))

    const totalPendapatan =
      sumPendapatan(umrahList) + sumPendapatan(hajiList) + sumPendapatan(edutripList) + sumPendapatan(customList)
    const totalTransaksi = umrahRows.length + hajiRows.length + edutripRows.length + customRows.length

    summary.value = {
      totalPendapatan,
      totalPengeluaran: 0,
      labaRugi: totalPendapatan,
      totalTransaksi,
      umrah: { total: umrahRows.length, pendapatan: sumPendapatan(umrahList) },
      haji: { total: hajiRows.length, pendapatan: sumPendapatan(hajiList) },
      edutrip: { total: edutripRows.length, pendapatan: sumPendapatan(edutripList) },
      custom: { total: customRows.length, pendapatan: sumPendapatan(customList) },
    }

    const all = [...umrahRows, ...hajiRows, ...edutripRows, ...customRows].sort((a, b) => {
      const dateA = new Date(a.tanggal_transaksi || a.created_at || 0).getTime()
      const dateB = new Date(b.tanggal_transaksi || b.created_at || 0).getTime()
      return dateB - dateA
    })
    transactions.value = all
  } catch (err: any) {
    console.error('Error fetching financial data:', err)
    const msg = err?.response?.data?.message || err?.message || 'Gagal memuat data keuangan'
    const status = err?.response?.status
    error.value = status ? `[${status}] ${msg}` : msg
    summary.value = {
      totalPendapatan: 0,
      totalPengeluaran: 0,
      labaRugi: 0,
      totalTransaksi: 0,
      umrah: { total: 0, pendapatan: 0 },
      haji: { total: 0, pendapatan: 0 },
      edutrip: { total: 0, pendapatan: 0 },
      custom: { total: 0, pendapatan: 0 },
    }
    transactions.value = []
  } finally {
    loading.value = false
  }
}

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(amount)
}

const formatDate = (date: string | null) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long',
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
