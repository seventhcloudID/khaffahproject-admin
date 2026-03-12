<template>
  <div class="w-full max-w-full overflow-x-hidden">
    <Card class="w-full max-w-full px-4 sm:px-6 py-4 sm:py-6 box-border" elevated radius="smooth" padding="md">
      <h1 class="text-lg sm:text-xl font-medium mb-3 sm:mb-4">Peminat Edutrip</h1>

      <!-- Tabs: scroll horizontal di mobile -->
      <div class="flex gap-2 sm:gap-3 overflow-x-auto pb-2 mb-4 border-b border-gray-200 -mx-1 px-1 scrollbar-hide" style="-webkit-overflow-scrolling: touch;">
        <button
          @click="changeTab('belum')"
          class="flex-shrink-0 px-2.5 sm:px-3 py-2 sm:py-1 flex items-center gap-1.5 sm:gap-2 relative whitespace-nowrap text-xs sm:text-sm"
          :class="activeTab === 'belum' ? 'font-bold text-yellow-500 border-b-2 border-yellow-500 -mb-0.5 pb-2' : 'text-gray-500'"
        >
          <span class="sm:hidden">Belum</span>
          <span class="hidden sm:inline">Belum di Proses</span>
          <span class="w-5 h-5 sm:w-6 sm:h-6 flex items-center justify-center rounded-full text-white text-xs flex-shrink-0" :class="activeTab === 'belum' ? 'bg-yellow-500' : 'bg-gray-400'">{{ countBelum }}</span>
        </button>
        <button
          @click="changeTab('diproses')"
          class="flex-shrink-0 px-2.5 sm:px-3 py-2 sm:py-1 flex items-center gap-1.5 sm:gap-2 relative whitespace-nowrap text-xs sm:text-sm"
          :class="activeTab === 'diproses' ? 'font-bold text-blue-600 border-b-2 border-blue-600 -mb-0.5 pb-2' : 'text-gray-500'"
        >
          Diproses
          <span class="w-5 h-5 sm:w-6 sm:h-6 flex items-center justify-center rounded-full text-white text-xs flex-shrink-0" :class="activeTab === 'diproses' ? 'bg-blue-600' : 'bg-gray-400'">{{ countProses }}</span>
        </button>
        <button
          @click="changeTab('selesai')"
          class="flex-shrink-0 px-2.5 sm:px-3 py-2 sm:py-1 flex items-center gap-1.5 sm:gap-2 relative whitespace-nowrap text-xs sm:text-sm"
          :class="activeTab === 'selesai' ? 'font-bold text-green-600 border-b-2 border-green-600 -mb-0.5 pb-2' : 'text-gray-500'"
        >
          Selesai
          <span class="w-5 h-5 sm:w-6 sm:h-6 flex items-center justify-center rounded-full text-white text-xs flex-shrink-0" :class="activeTab === 'selesai' ? 'bg-green-600' : 'bg-gray-400'">{{ countSelesai }}</span>
        </button>
        <button
          @click="changeTab('batal')"
          class="flex-shrink-0 px-2.5 sm:px-3 py-2 sm:py-1 flex items-center gap-1.5 sm:gap-2 relative whitespace-nowrap text-xs sm:text-sm"
          :class="activeTab === 'batal' ? 'font-bold text-red-600 border-b-2 border-red-600 -mb-0.5 pb-2' : 'text-gray-500'"
        >
          Batal
          <span class="w-5 h-5 sm:w-6 sm:h-6 flex items-center justify-center rounded-full text-white text-xs flex-shrink-0" :class="activeTab === 'batal' ? 'bg-red-600' : 'bg-gray-400'">{{ countBatal }}</span>
        </button>
      </div>

      <!-- Filter: stack di mobile -->
      <div class="grid grid-cols-1 md:grid-cols-12 gap-3 mb-4 md:items-end">
        <div class="hidden md:block md:col-span-7"></div>
        <div class="md:col-span-3">
          <Field class="w-full" label="Tanggal Kunjungan (Opsional)" id="tanggal-kunjungan-edutrip" :horizontal="!isMobile">
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

      <!-- Mobile: daftar kartu -->
      <div v-if="isMobile" class="space-y-3">
        <div class="flex items-center justify-between gap-2">
          <Button color="success" @click="exportExcel()" outlined icon="fas fa-file-excel" class="flex-1 min-w-0">Export Excel</Button>
        </div>
        <div v-if="loadSearch" class="flex justify-center py-8">
          <i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i>
        </div>
        <template v-else>
          <div class="space-y-3">
            <div v-for="row in mobileCardsSlice" :key="row.id" class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
              <div class="flex flex-col gap-1.5 sm:flex-row sm:justify-between sm:items-start mb-2">
                <span class="font-semibold text-gray-900 text-sm truncate min-w-0">{{ row.kode_transaksi }}</span>
                <span class="inline-flex items-center self-start text-[11px] sm:text-xs px-2.5 py-1 rounded-full bg-gray-100 text-gray-700 min-h-[28px] max-w-full min-w-0" :title="row.nama_status">
                  <span class="truncate block min-w-0">{{ row.nama_status }}</span>
                </span>
              </div>
              <p class="text-sm text-gray-700 font-medium truncate">{{ row.gelar }} {{ row.nama_lengkap }}</p>
              <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">{{ row.nama_paket }}</p>
              <p class="text-xs text-gray-500 mt-1">
                {{ row.snapshot_produk?.tanggal_kunjungan ? H.formatDate(row.snapshot_produk.tanggal_kunjungan, 'DD/MM/YYYY') : '-' }}
                <span v-if="row.snapshot_produk?.jam_kunjungan">{{ row.snapshot_produk.jam_kunjungan }}</span>
              </p>
              <p v-if="row.deskripsi" class="text-xs text-gray-500 mt-0.5 line-clamp-2">{{ row.deskripsi }}</p>
              <div class="flex items-center justify-between gap-2 mt-3 pt-3 border-t border-gray-100">
                <a
                  :href="`https://wa.me/${(row.no_whatsapp || '').replace(/^0/, '62').replace(/\D/g, '')}`"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="inline-flex items-center justify-center w-10 h-10 bg-green-500 hover:bg-green-600 rounded-full transition-colors flex-shrink-0"
                  title="WhatsApp"
                >
                  <i class="fab fa-whatsapp text-white"></i>
                </a>
                <div class="flex items-center gap-1">
                  <button type="button" class="px-3 py-1.5 text-xs font-medium text-[#007b6f] border border-[#007b6f] rounded-lg active:scale-[0.98]" @click="goToDetail(row.id)">Detail</button>
                  <button type="button" class="inline-flex items-center justify-center w-10 h-10 min-h-[44px] text-gray-600 hover:bg-gray-100 rounded-full transition-colors flex-shrink-0" @click="toggleDropdown(row.id, $event, row)">
                    <i class="fas fa-ellipsis-v"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
          <div v-if="mobileTotalPages > 1" class="flex items-center justify-center gap-2 pt-4 pb-2">
            <button type="button" class="px-3 py-2 rounded-lg border border-gray-300 text-sm font-medium disabled:opacity-50 disabled:pointer-events-none" :disabled="mobilePage <= 1" @click="mobilePage = Math.max(1, mobilePage - 1)">Prev</button>
            <span class="text-sm text-gray-600 px-2">{{ mobilePage }} / {{ mobileTotalPages }}</span>
            <button type="button" class="px-3 py-2 rounded-lg border border-gray-300 text-sm font-medium disabled:opacity-50 disabled:pointer-events-none" :disabled="mobilePage >= mobileTotalPages" @click="mobilePage = Math.min(mobileTotalPages, mobilePage + 1)">Next</button>
          </div>
          <p v-if="dataSource.length === 0" class="text-center text-gray-500 py-6 text-sm">Tidak ada data.</p>
        </template>
      </div>

      <!-- Desktop: Tabel -->
      <div v-show="!isMobile" class="overflow-x-auto -mx-4 sm:mx-0 rounded-lg border border-gray-200">
        <DataTable
          :value="dataSource"
          class="datatable datatable-fixed-height datatable-mobile"
          :paginator="true"
          :rows="10"
          :rowsPerPageOptions="[5, 10, 20, 50, 100]"
          paginatorTemplate="CurrentPageReport FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
          currentPageReportTemplate="Menampilkan {first} - {last} dari {totalRecords} data"
        >
          <template #header>
            <div class="flex flex-wrap gap-2 items-center pt-2">
              <Button color="success" @click="exportExcel()" outlined icon="fas fa-file-excel" :hide-text-on-mobile="true" class="min-w-0">Export To Excel</Button>
            </div>
          </template>
          <Column field="no" header="No" />
          <Column field="kode_transaksi" header="Kode Transaksi" />
          <Column header="Tanggal Kunjungan" style="min-width: 160px">
            <template #body="slotProps">
              {{ slotProps.data.snapshot_produk?.tanggal_kunjungan ? H.formatDate(slotProps.data.snapshot_produk.tanggal_kunjungan, 'DD/MM/YYYY') : '-' }}
              {{ slotProps.data.snapshot_produk?.jam_kunjungan ?? '' }}
            </template>
          </Column>
          <Column field="nama_paket" header="Nama Paket" />
          <Column header="Nama Lengkap">
            <template #body="slotProps">{{ slotProps.data.gelar }} {{ slotProps.data.nama_lengkap }}</template>
          </Column>
          <Column header="No Whatsapp">
            <template #body="slotProps">
              <div class="flex items-center gap-2">
                <span>{{ slotProps.data.no_whatsapp }}</span>
                <a :href="`https://wa.me/${slotProps.data.no_whatsapp?.replace(/^0/, '62').replace(/\D/g, '')}`" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center w-7 h-7 bg-green-500 hover:bg-green-600 rounded-full transition-colors" title="Chat via WhatsApp">
                  <i class="fab fa-whatsapp text-white text-sm"></i>
                </a>
              </div>
            </template>
          </Column>
          <Column field="deskripsi" header="Catatan" />
          <Column field="nama_status" header="Status Transaksi" />
          <Column header="#" headerClass="text-center" bodyClass="text-center" class="action-column" :style="{ width: '80px' }">
            <template #body="slotProps">
              <div class="relative inline-block text-left">
                <button type="button" class="inline-flex items-center justify-center w-8 h-8 min-w-[32px] min-h-[32px] text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-full transition-colors active:bg-gray-200" @click.stop="toggleDropdown(slotProps.data.id, $event, slotProps.data)">
                  <i class="fas fa-ellipsis-v"></i>
                </button>
              </div>
            </template>
          </Column>
        </DataTable>
      </div>
    </Card>
  </div>

  <!-- Dropdown aksi di-render ke body agar tidak terpotong overflow tabel -->
  <Teleport to="body">
    <div
      v-if="activeDropdown !== null"
      ref="dropdownMenuRef"
      class="action-dropdown-menu"
      :style="dropdownStyle"
      @click.stop
    >
      <div class="py-1">
        <button @click="goToDetail(activeDropdown)" class="w-full text-left px-4 py-2.5 sm:py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2 min-h-[44px] sm:min-h-0">
          <i class="fas fa-eye w-4 text-yellow-400 mr-2 flex-shrink-0"></i>
          <span>Lihat Detail</span>
        </button>
        <button @click="updateStatus(activeDropdown)" class="w-full text-left px-4 py-2.5 sm:py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2 min-h-[44px] sm:min-h-0">
          <i class="fas fa-pencil w-4 text-blue-600 mr-2 flex-shrink-0"></i>
          <span>Update Status Transaksi</span>
        </button>
        <button @click="updateStatusPembayaran(activeDropdown)" class="w-full text-left px-4 py-2.5 sm:py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2 min-h-[44px] sm:min-h-0">
          <i class="fas fa-money-bill-wave w-4 text-green-600 mr-2 flex-shrink-0"></i>
          <span>Update Status Pembayaran</span>
        </button>
        <button @click="hapusData(activeDropdownRow)" class="w-full text-left px-4 py-2.5 sm:py-2 text-sm text-red-600 hover:bg-red-50 flex items-center gap-2 min-h-[44px] sm:min-h-0">
          <i class="fas fa-trash w-4 mr-2 flex-shrink-0"></i>
          <span>Hapus</span>
        </button>
      </div>
    </div>
  </Teleport>

  <Modal
    :open="ModalUpdateStatus"
    size="small"
    rounded
    noscroll
    :closeOnEsc="true"
    :closeOnOutside="false"
    actions="right"
    cancelLabel="Tutup"
    actionLabel="Simpan"
    :showAction="true"
    @close="ModalUpdateStatus = false"
    @action="handleUpdate()"
  >
    <!-- Override Header -->
    <template #header>
      <div class="w-full flex items-center">
        <h3 class="font-semibold">Update Status</h3>
        <button class="ml-auto text-gray-500 hover:text-red-500" @click="ModalUpdateStatus = false">
          ✕
        </button>
      </div>
    </template>

    <!-- Content -->
    <template #content>
      <div class="column is-12 text-center">
        <label for="status-transaksi">Status Transaksi</label>
        <Dropdown
          id="status-transaksi"
          v-model="selectedStatusTransaksi"
          :options="d_StatusTransaksi"
          optionLabel="label"
          optionValue="value"
          placeholder="Pilih Status Transaksi..."
          class="w-full"
          appendTo="body"
        />
      </div>
    </template>

    <!-- Action (Footer) -->
    <template #footer-left>
      <!-- <button
        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
        @click="handleDelete"
      >
        Hapus Data
      </button> -->
    </template>
  </Modal>

  <!-- Modal Update Status Pembayaran (Lunas, Belum Bayar, dll) -->
  <Modal
    :open="ModalUpdateStatusPembayaran"
    size="small"
    rounded
    noscroll
    :closeOnEsc="true"
    :closeOnOutside="false"
    actions="right"
    cancelLabel="Tutup"
    actionLabel="Simpan"
    :showAction="true"
    @close="ModalUpdateStatusPembayaran = false"
    @action="handleUpdatePembayaran()"
  >
    <template #header>
      <div class="w-full flex items-center">
        <h3 class="font-semibold">Update Status Pembayaran</h3>
        <button class="ml-auto text-gray-500 hover:text-red-500" @click="ModalUpdateStatusPembayaran = false">✕</button>
      </div>
    </template>
    <template #content>
      <div class="column is-12 text-center">
        <label for="status-pembayaran">Status Pembayaran</label>
        <Dropdown
          id="status-pembayaran"
          v-model="selectedStatusPembayaran"
          :options="d_StatusPembayaran"
          optionLabel="label"
          optionValue="value"
          placeholder="Pilih (Belum Bayar, Lunas, dll)..."
          class="w-full"
          appendTo="body"
        />
      </div>
    </template>
  </Modal>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, onUnmounted, computed, watch, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import DataTable from 'primevue/datatable'
import Dropdown from 'primevue/dropdown'
import DatePicker from 'primevue/datepicker'
import Column from 'primevue/column'
import { useApi } from '@/api/useApi'
import * as XLSX from 'xlsx'
import * as XLSXStyle from 'xlsx-js-style'
import H from '@/utils/appHelper'

const api = useApi()
const router = useRouter()

const dataSource = ref<any[]>([])
const d_StatusTransaksi = ref<any[]>([])
const d_StatusPembayaran = ref<any[]>([])
const ModalUpdateStatusPembayaran = ref(false)
const selectedStatusPembayaran = ref(null)
const idTransaksiUpdatePembayaran = ref<number | null>(null)
const item = ref({
  tanggalPemesanan: null, // Default: tampilkan semua data tanpa filter tanggal
})
const loadSearch = ref(false)
const activeTab = ref('belum')

const isMobile = ref(false)
function updateIsMobile() {
  isMobile.value = typeof window !== 'undefined' && window.innerWidth < 768
}
const mobilePage = ref(1)
const mobileRows = 10
const mobileTotalPages = computed(() => Math.max(1, Math.ceil((dataSource.value?.length || 0) / mobileRows)))
const mobileCardsSlice = computed(() => {
  const list = dataSource.value || []
  const start = (mobilePage.value - 1) * mobileRows
  return list.slice(start, start + mobileRows)
})
watch(mobileTotalPages, (total) => {
  if (total > 0 && mobilePage.value > total) mobilePage.value = total
})

const activeDropdown = ref<number | string | null>(null)
const activeDropdownRow = ref<any>(null)
const dropdownStyle = ref<Record<string, string>>({})
const dropdownMenuRef = ref<HTMLElement | null>(null)
const selectedStatusTransaksi = ref<number | null>(null)
const idTransaksiUpdate = ref<number | null>(null)

const ModalUpdateStatus = ref(false)

const countBelum = ref(0)
const countProses = ref(0)
const countSelesai = ref(0)
const countBatal = ref(0)

const changeTab = (tab: string) => {
  // Hanya ubah tab aktif (untuk tampilan), data tetap semua status
  activeTab.value = tab
}

const goToDetail = (orderId: number | string) => {
  closeDropdown()
  router.push(`/Daftar-Transaksi/Peminat-Edutrip/${orderId}`)
}

// === DROPDOWN FUNCTIONS ===
const DROPDOWN_WIDTH = 192

const closeDropdown = () => {
  activeDropdown.value = null
  activeDropdownRow.value = null
  document.removeEventListener('click', handleClickOutside)
}

const handleClickOutside = (e: MouseEvent) => {
  const menu = dropdownMenuRef.value
  const target = e.target as Node
  if (menu && !menu.contains(target)) closeDropdown()
}

const toggleDropdown = (id: number | string, event: MouseEvent, row?: any) => {
  const isOpen = activeDropdown.value === id
  if (isOpen) {
    closeDropdown()
    return
  }
  const btn = event?.currentTarget as HTMLElement
  if (!btn) return
  const rect = btn.getBoundingClientRect()
  const viewportW = typeof window !== 'undefined' ? window.innerWidth : 1024
  let left = rect.right - DROPDOWN_WIDTH
  if (left < 8) left = 8
  if (left + DROPDOWN_WIDTH > viewportW - 8) left = viewportW - DROPDOWN_WIDTH - 8
  dropdownStyle.value = {
    position: 'fixed',
    top: `${rect.bottom + 8}px`,
    left: `${left}px`,
    zIndex: '9999',
    maxWidth: 'min(12rem, calc(100vw - 16px))',
  }
  activeDropdown.value = id
  activeDropdownRow.value = row ?? dataSource.value.find((r: any) => r.id === id) ?? null
  nextTick(() => document.addEventListener('click', handleClickOutside))
}

const updateStatus = (id: number | string) => {
  closeDropdown()
  idTransaksiUpdate.value = Number(id)
  selectedStatusTransaksi.value = null
  ModalUpdateStatus.value = true
  fetchStatusTransaksi()
}

const updateStatusPembayaran = (id: number | string) => {
  closeDropdown()
  idTransaksiUpdatePembayaran.value = Number(id)
  selectedStatusPembayaran.value = null
  ModalUpdateStatusPembayaran.value = true
  fetchStatusPembayaran()
}

const fetchStatusPembayaran = async (_opts?: { query?: string }) => {
  try {
    const response = await api.get(
      `/sistem-admin/utility/dropdown/status_pembayaran_m?select=id,nama_status&limit=50`,
    ) as { data?: unknown[] }
    const raw = Array.isArray(response) ? response : (response as any)?.data ?? response
    const arr = Array.isArray(raw) ? raw : []
    d_StatusPembayaran.value = arr.map((x: any) => ({
      id: x.value ?? x.id,
      label: x.label ?? x.nama_status ?? `#${x.value ?? x.id}`,
      value: x.value ?? x.id,
    }))
  } catch (e) {
    console.error('Error fetching status pembayaran:', e)
    d_StatusPembayaran.value = []
  }
}

const handleUpdatePembayaran = async () => {
  if (selectedStatusPembayaran.value == null) {
    alert('Silakan pilih status pembayaran (mis. Lunas) terlebih dahulu.')
    return
  }
  if (!idTransaksiUpdatePembayaran.value) {
    alert('ID transaksi tidak ditemukan.')
    return
  }
  try {
    const res = await api.post('/sistem-admin/transaksi/update-status-pembayaran', {
      id: idTransaksiUpdatePembayaran.value,
      status_pembayaran_id: selectedStatusPembayaran.value,
    })
    if (res && (res as any).status === true) {
      ModalUpdateStatusPembayaran.value = false
      selectedStatusPembayaran.value = null
      idTransaksiUpdatePembayaran.value = null
      fetchDataAll()
      updateCount()
    } else {
      alert((res as any)?.message ?? 'Update status pembayaran gagal.')
    }
  } catch (error: any) {
    const msg =
      error?.response?.data?.message ?? error?.message ?? 'Terjadi kesalahan saat update status pembayaran.'
    alert(msg)
  }
}

const hapusData = async (data: any) => {
  if (!confirm(`Apakah Anda yakin ingin menghapus ${data.nama_lengkap}?`)) return
  closeDropdown()
  try {
    await api.delete(`/sistem-admin/transaksi/delete-peminat-edutrip/${data.id}`)
    fetchData()
    updateCount()
    alert('Peminat edutrip berhasil dihapus')
  } catch (err: any) {
    alert(err?.response?.data?.message ?? 'Gagal menghapus data')
  }
}

// === END DROPDOWN FUNCTIONS ===

const fetchData = () => {
  fetchDataAll()
}

const fetchDataAll = async () => {
  try {
    loadSearch.value = true

    let { dateStart, dateEnd } = H.parseDateRange(item.value.tanggalPemesanan)

    // Build URL dengan atau tanpa filter tanggal (tanpa filter status supaya semua status tampil)
    let url = `/sistem-admin/transaksi/get-list-peminat-edutrip`

    if (dateStart && dateEnd) {
      let tglAwal = H.formatDate(dateStart, 'YYYY-MM-DD')
      let tglAkhir = H.formatDate(dateEnd, 'YYYY-MM-DD')
      url += `?tglAwal=${tglAwal}&tglAkhir=${tglAkhir}`
    }

    const response = await api.get(url) as { data?: unknown[] }
    const raw = Array.isArray(response) ? response : (response as any)?.data ?? response
    const list = Array.isArray(raw) ? raw : []

    list.forEach((element: any, i: number) => {
      element.no = i + 1
    })

    dataSource.value = list
    mobilePage.value = 1

    // Update counter tab (backend tetap hitung per status)
    updateCount()
  } catch (error) {
    console.error('Error fetching data:', error)
  } finally {
    loadSearch.value = false
  }
}

const updateCount = async () => {
  try {
    let { dateStart, dateEnd } = H.parseDateRange(item.value.tanggalPemesanan)

    let url = `/sistem-admin/transaksi/get-count-peminat-edutrip`

    if (dateStart && dateEnd) {
      let tglAwal = H.formatDate(dateStart, 'YYYY-MM-DD')
      let tglAkhir = H.formatDate(dateEnd, 'YYYY-MM-DD')
      url += `?tglAwal=${tglAwal}&tglAkhir=${tglAkhir}`
    }

    const res = await api.get(url)

    countBelum.value = res.data.belum_diproses || 0
    countProses.value = res.data.diproses || 0
    countSelesai.value = res.data.selesai || 0
    countBatal.value = res.data.batal || 0
  } catch (error) {
    console.error('Error fetching count:', error)
  }
}

const exportExcel = () => {
  const workbook = XLSX.utils.book_new()

  // Parse periode dari filter tanggal saat ini
  let { dateStart, dateEnd } = H.parseDateRange(item.value.tanggalPemesanan)
  const tglAwal = dateStart ? H.formatDate(dateStart, 'DD-MM-YYYY') : ''
  const tglAkhir = dateEnd ? H.formatDate(dateEnd, 'DD-MM-YYYY') : ''
  const periodeLabel = tglAwal && tglAkhir ? `${tglAwal} - ${tglAkhir}` : 'Semua data'

  const header = [
    'No',
    'Kode Transaksi',
    'Tanggal Kunjungan',
    'Nama Paket',
    'Nama Lengkap',
    'No Whatsapp',
    'Catatan',
    'Status Transaksi',
  ]

  const sheetData: any[] = []
  sheetData.push(['LAPORAN PEMINAT EDUTRIP'])
  sheetData.push([`Periode: ${periodeLabel}`])
  sheetData.push([])
  sheetData.push(header)

  const merges: any[] = [
    { s: { r: 0, c: 0 }, e: { r: 0, c: header.length - 1 } },
    { s: { r: 1, c: 0 }, e: { r: 1, c: header.length - 1 } },
  ]

  dataSource.value.forEach((e: any) => {
    const tanggalKunjungan = e.snapshot_produk?.tanggal_kunjungan
      ? `${H.formatDate(e.snapshot_produk.tanggal_kunjungan, 'DD/MM/YYYY')} ${e.snapshot_produk.jam_kunjungan || ''}`
      : '-'

    const row = [
      e.no,
      e.kode_transaksi,
      tanggalKunjungan,
      e.nama_paket,
      `${e.gelar ?? ''} ${e.nama_lengkap ?? ''}`.trim(),
      e.no_whatsapp,
      e.deskripsi ?? '',
      e.nama_status ?? '',
    ]

    sheetData.push(row)
  })

  const worksheet = XLSX.utils.aoa_to_sheet(sheetData)

  // Apply merges
  worksheet['!merges'] = merges

  // Style Title (A1)
  if (worksheet['A1']) {
    worksheet['A1'].s = {
      font: { bold: true, sz: 16 },
      alignment: { horizontal: 'center', vertical: 'center' },
    }
  }

  // Style Period (A2)
  if (worksheet['A2']) {
    worksheet['A2'].s = {
      font: { italic: true, sz: 11 },
      alignment: { horizontal: 'center', vertical: 'center' },
    }
  }

  // Style header (row index 3)
  const headerStyle = {
    font: { bold: true, color: { rgb: 'FFFFFF' } },
    fill: { fgColor: { rgb: '4A90E2' } },
    alignment: { horizontal: 'center', vertical: 'center' },
  }
  header.forEach((_, col) => {
    const cell = XLSX.utils.encode_cell({ r: 3, c: col })
    if (worksheet[cell]) worksheet[cell].s = headerStyle
  })

  // Set column width
  worksheet['!cols'] = [
    { wch: 5 }, // No
    { wch: 20 }, // Kode Transaksi
    { wch: 25 }, // Tanggal Kunjungan
    { wch: 25 }, // Nama Paket
    { wch: 30 }, // Nama Lengkap
    { wch: 15 }, // No Whatsapp
    { wch: 40 }, // Catatan
    { wch: 20 }, // Status Transaksi
  ]

  XLSX.utils.book_append_sheet(workbook, worksheet, 'Peminat Edutrip')

  const fileName = `Laporan Peminat Edutrip ${periodeLabel}.xlsx`
  XLSXStyle.writeFile(workbook, fileName)
}

const fetchStatusTransaksi = async () => {
  try {
    const response = await api.get(
      `/sistem-admin/utility/dropdown/status_transaksi_m?select=id,nama_status&limit=50`,
    ) as { data?: unknown[] }
    const raw = Array.isArray(response) ? response : (response as any)?.data ?? response
    const arr = Array.isArray(raw) ? raw : []
    d_StatusTransaksi.value = arr.map((x: any) => ({
      id: x.value ?? x.id,
      label: x.label ?? x.nama_status ?? `#${x.value ?? x.id}`,
      value: x.value ?? x.id,
    }))
  } catch (error) {
    console.error('Error fetching status transaksi:', error)
    d_StatusTransaksi.value = []
  }
}

const handleUpdate = async () => {
  if (!selectedStatusTransaksi.value) {
    alert('Silakan pilih status terlebih dahulu.')
    return
  }

  if (!idTransaksiUpdate.value) {
    alert('ID transaksi tidak ditemukan.')
    return
  }

  try {
    const payload = {
      id: idTransaksiUpdate.value,
      status_id: selectedStatusTransaksi.value ?? null,
    }
    const res = await api.post('/sistem-admin/transaksi/update-status-transaksi', payload) as unknown as Record<string, unknown>
    if (res && (res.status === true || res.status === 'success' || res.status === 1)) {
      ModalUpdateStatus.value = false
      selectedStatusTransaksi.value = null
      idTransaksiUpdate.value = null

      fetchDataAll()
      updateCount()
    }
  } catch (err: unknown) {
    console.error('Error update status:', err)
    const e = err as { response?: { data?: { message?: string } }; message?: string }
    const msg = e?.response?.data?.message ?? e?.message ?? 'Terjadi kesalahan saat update status.'
    alert(msg)
  }
}

onMounted(() => {
  updateIsMobile()
  if (typeof window !== 'undefined') {
    window.addEventListener('resize', updateIsMobile)
  }
  fetchDataAll()
  updateCount()
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
  if (typeof window !== 'undefined') {
    window.removeEventListener('resize', updateIsMobile)
  }
})
</script>

<style scoped>
.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}

.datatable-fixed-height :deep(.p-datatable-table-container) {
  min-height: 420px;
  overflow: visible !important;
  max-height: none !important;
}

@media (max-width: 767px) {
  .datatable-mobile :deep(.p-datatable-wrapper) {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }
  .datatable-mobile :deep(.p-paginator) {
    flex-wrap: wrap;
    gap: 0.25rem;
    padding: 0.5rem;
  }
  .datatable-mobile :deep(.p-datatable-thead > tr > th),
  .datatable-mobile :deep(.p-datatable-tbody > tr > td) {
    padding: 0.5rem 0.375rem;
    font-size: 0.8125rem;
    white-space: nowrap;
  }
}

.action-dropdown-menu {
  min-width: 12rem;
  border-radius: 0.375rem;
  background: white;
  box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
  border: 1px solid rgba(0, 0, 0, 0.05);
  outline: none;
}
</style>
