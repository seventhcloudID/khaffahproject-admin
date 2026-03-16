<template>
  <div class="w-full max-w-full overflow-x-hidden">
    <Card class="w-full max-w-full px-4 sm:px-6 py-4 sm:py-6 box-border" elevated radius="smooth" padding="md">
      <h1 class="text-lg sm:text-xl font-medium mb-3 sm:mb-4">Master Paket Edutrip</h1>

      <div class="grid grid-cols-1 md:grid-cols-12 gap-3 mb-4 md:items-end">
        <div class="md:col-span-7 flex items-end">
          <Button class="w-full sm:w-auto" color="success" @click="openModalCreate()" darkOutlined icon="fas fa-plus" :hide-text-on-mobile="true">
            Tambah
          </Button>
        </div>
        <div class="md:col-span-3">
          <Field class="w-full" label="Tanggal Pembuatan" id="nama-edutrip" required :horizontal="!isMobile">
            <DatePicker
              v-model="item.tanggalPembuatan"
              showIcon
              fluid
              iconDisplay="input"
              selectionMode="range"
              dateFormat="dd/mm/yy"
              :manualInput="false"
              placeholder="Pilih tanggal"
            />
          </Field>
        </div>
        <div class="md:col-span-2">
          <Button class="w-full md:w-auto" color="success" @click="fetchData()" darkOutlined icon="fas fa-search" :hide-text-on-mobile="true">
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
              <h3 class="font-semibold text-gray-900 text-sm mb-1 truncate">{{ row.nama_paket }}</h3>
              <p class="text-xs text-gray-500 mb-2">Waktu Konsultasi: {{ row.jumlah_hari }} Hari</p>
              <p v-if="row.deskripsi" class="text-xs text-gray-600 line-clamp-3">{{ row.deskripsi }}</p>
              <div class="flex items-center justify-end gap-2 mt-3 pt-3 border-t border-gray-100">
                <button type="button" class="px-3 py-1.5 text-xs font-medium text-[#007b6f] border border-[#007b6f] rounded-lg active:scale-[0.98]" @click="openModalEdit(row)">
                  Edit
                </button>
                <button type="button" class="inline-flex items-center justify-center w-10 h-10 min-h-[44px] text-gray-600 hover:bg-gray-100 rounded-full transition-colors flex-shrink-0" @click="toggleDropdown(row.id, $event, row)">
                  <i class="fas fa-ellipsis-v"></i>
                </button>
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
          <Column field="no" header="No" style="width: 50px" />
          <Column field="nama_paket" header="Nama Paket" />
          <Column header="Waktu Konsultasi" style="width: 130px">
            <template #body="slotProps"> {{ slotProps.data.jumlah_hari }} Hari </template>
          </Column>
          <Column header="Deskripsi" style="min-width: 200px">
            <template #body="slotProps">
              <span class="text-sm text-gray-700">
                {{ (slotProps.data.deskripsi || '').length > 80 ? (slotProps.data.deskripsi || '').slice(0, 80) + '…' : (slotProps.data.deskripsi || '-') }}
              </span>
            </template>
          </Column>
          <Column
            header="#"
            headerClass="text-center"
            bodyClass="text-center"
            class="action-column"
            :style="{ width: '80px' }"
          >
            <template #body="slotProps">
              <div class="relative inline-block text-left">
                <button
                  @click.stop="toggleDropdown(slotProps.data.id, $event, slotProps.data)"
                  type="button"
                  class="inline-flex items-center justify-center w-8 h-8 min-w-[32px] min-h-[32px] text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-full transition-colors active:bg-gray-200"
                >
                  <i class="fas fa-ellipsis-v"></i>
                </button>
              </div>
            </template>
          </Column>
        </DataTable>
      </div>
    </Card>

    <Teleport to="body">
      <div
        v-if="activeDropdown !== null"
        ref="dropdownMenuRef"
        class="action-dropdown-menu"
        :style="dropdownStyle"
        @click.stop
      >
        <div class="py-1">
          <button @click="openModalEdit(activeDropdownRow)" class="w-full text-left px-4 py-2.5 sm:py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2 min-h-[44px] sm:min-h-0">
            <i class="fas fa-pencil-alt w-4 text-blue-400 mr-2 flex-shrink-0"></i>
            <span>Edit</span>
          </button>
          <button @click="deleteData(activeDropdownRow)" class="w-full text-left px-4 py-2.5 sm:py-2 text-sm text-red-600 hover:bg-red-50 flex items-center gap-2 min-h-[44px] sm:min-h-0">
            <i class="fas fa-trash w-4 mr-2 flex-shrink-0"></i>
            <span>Delete</span>
          </button>
        </div>
      </div>
    </Teleport>

    <!-- Modal Form Create/Edit -->
    <Modal
      :open="modalFormOpen"
      @close="closeModal()"
      :title="modalMode === 'create' ? 'Tambah Master Paket Edutrip' : 'Edit Master Paket Edutrip'"
      size="medium"
      :show-action="true"
      @action="saveData()"
      action-label="Simpan"
    >
      <template #content>
        <div class="space-y-4">
          <Field label="Nama Paket" id="nama_paket" required>
            <input
              v-model="formData.nama_paket"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Masukkan nama paket"
            />
          </Field>

          <Field label="Waktu Konsultasi (Hari)" id="jumlah_hari" required>
            <input
              v-model.number="formData.jumlah_hari"
              type="number"
              min="1"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Masukkan jumlah hari"
            />
          </Field>

          <Field label="Deskripsi" id="deskripsi">
            <textarea
              v-model="formData.deskripsi"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Masukkan deskripsi paket"
              rows="4"
            ></textarea>
          </Field>
        </div>
      </template>
    </Modal>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, onUnmounted, computed, watch, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import DataTable from 'primevue/datatable'
import AutoComplete from 'primevue/autocomplete'
import DatePicker from 'primevue/datepicker'
import Column from 'primevue/column'
import { useApi } from '@/api/useApi'
import * as XLSX from 'xlsx'
import * as XLSXStyle from 'xlsx-js-style'
import H from '@/utils/appHelper'

const api = useApi()
const router = useRouter()

const dataSource = ref([])
const d_StatusTransaksi = ref([])
// Default: 1 bulan terakhir agar paket yang sudah ada ikut terlihat (bukan hanya yang dibuat hari ini)
const defaultDateEnd = new Date()
const defaultDateStart = new Date()
defaultDateStart.setMonth(defaultDateStart.getMonth() - 1)
const item = ref({
  tanggalPembuatan: [defaultDateStart, defaultDateEnd],
})
const loadSearch = ref(false)
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
const activeTab = ref('pending')
const dataJamaah = ref(null)
const transactionDetail = ref(null)
const activeDropdown = ref<number | string | null>(null)
const activeDropdownRow = ref<any>(null)
const dropdownStyle = ref<Record<string, string>>({})
const dropdownMenuRef = ref<HTMLElement | null>(null)
const selectedStatusTransaksi = ref(null)
const idTransaksiUpdate = ref(null)

const ModalDetail = ref(false)
const ModalUpdateStatus = ref(false)

const countPending = ref(0)
const countProses = ref(0)
const countDisetujui = ref(0)
const countDitolak = ref(0)

// Modal & Form state
const modalFormOpen = ref(false)
const modalMode = ref<'create' | 'edit'>('create')
const formData = ref({
  id: null as number | null,
  nama_paket: '',
  jumlah_hari: 1,
  deskripsi: '',
})
const isLoadingSubmit = ref(false)

const fetchDataByStatus = (_tab?: string) => fetchData()

const changeTab = (tab: string) => {
  activeTab.value = tab
  fetchDataByStatus(tab)
}

const handleClose = () => {
  dataJamaah.value = null
  ModalDetail.value = false
}

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

const openModalCreate = () => {
  modalMode.value = 'create'
  formData.value = {
    id: null,
    nama_paket: '',
    jumlah_hari: 1,
    deskripsi: '',
  }
  modalFormOpen.value = true
}

const openModalEdit = (data: any) => {
  closeDropdown()
  modalMode.value = 'edit'
  formData.value = {
    id: data.id,
    nama_paket: data.nama_paket,
    jumlah_hari: data.jumlah_hari,
    deskripsi: data.deskripsi || '',
  }
  modalFormOpen.value = true
}

const closeModal = () => {
  modalFormOpen.value = false
  formData.value = {
    id: null,
    nama_paket: '',
    jumlah_hari: 1,
    deskripsi: '',
  }
}

const saveData = async () => {
  try {
    if (!formData.value.nama_paket.trim()) {
      alert('Nama paket tidak boleh kosong')
      return
    }

    if (formData.value.jumlah_hari < 1) {
      alert('Waktu konsultasi harus lebih dari 0')
      return
    }

    isLoadingSubmit.value = true

    if (modalMode.value === 'create') {
      await api.post('/sistem-admin/paket-edutrip/create-paket-edutrip', {
        nama_paket: formData.value.nama_paket,
        jumlah_hari: formData.value.jumlah_hari,
        deskripsi: formData.value.deskripsi,
      })
    } else {
      await api.put(`/sistem-admin/paket-edutrip/update-paket-edutrip/${formData.value.id}`, {
        nama_paket: formData.value.nama_paket,
        jumlah_hari: formData.value.jumlah_hari,
        deskripsi: formData.value.deskripsi,
      })
    }

    closeModal()
    fetchData()
  } catch (error) {
    console.error('Error saving data:', error)
    alert('Gagal menyimpan data')
  } finally {
    isLoadingSubmit.value = false
  }
}

const deleteData = async (data: any) => {
  closeDropdown()
  if (!confirm(`Apakah Anda yakin ingin menghapus paket "${data.nama_paket}"?`)) {
    return
  }

  try {
    await api.delete(`/sistem-admin/paket-edutrip/delete-paket-edutrip/${data.id}`)
    fetchData()
  } catch (error) {
    console.error('Error deleting data:', error)
    alert('Gagal menghapus data')
  }
}

const getAverageRating = (akomodasi: any[]) => {
  if (!akomodasi || akomodasi.length === 0) return 0
  const totalRating = akomodasi.reduce((sum, hotel) => sum + hotel.rating_hotel, 0)
  return (totalRating / akomodasi.length).toFixed(1)
}

const formatHotelList = (akomodasi: any[]) => {
  if (!akomodasi || akomodasi.length === 0) return '-'
  return akomodasi.map((hotel) => `${hotel.kota}: ${hotel.nama_hotel}`).join('\n')
}

const getFacilitiesList = (fasilitas: any[]) => {
  if (!fasilitas || fasilitas.length === 0) return []
  return fasilitas
}

const verifikasi = async (data: any) => {
  router.push({
    name: 'verifikasi-mitra',
    params: { id: data.id_mitra },
    query: { nama: data.nama_lengkap },
  })
}

const fetchData = async () => {
  try {
    loadSearch.value = true

    let { dateStart, dateEnd } = H.parseDateRange(item.value.tanggalPembuatan)
    let tglAwal = H.formatDate(dateStart, 'YYYY-MM-DD')
    let tglAkhir = H.formatDate(dateEnd, 'YYYY-MM-DD')

    const response = await api.get(
      `/sistem-admin/paket-edutrip/get-paket-edutrip?tglAwal=${tglAwal}&tglAkhir=${tglAkhir}`,
    )

    response.data.forEach((element: any, i: any) => {
      element.no = i + 1
    })

    dataSource.value = response.data
    mobilePage.value = 1
  } catch (error) {
    console.error('Error fetching data:', error)
  } finally {
    loadSearch.value = false
  }
}

const exportExcel = () => {
  const workbook = XLSX.utils.book_new()

  // Parse periode dari filter tanggal saat ini
  let { dateStart, dateEnd } = H.parseDateRange(item.value.tanggalPembuatan)
  const tglAwal = H.formatDate(dateStart, 'DD-MM-YYYY')
  const tglAkhir = H.formatDate(dateEnd, 'DD-MM-YYYY')

  const header = ['No', 'Nama Paket', 'Waktu Konsultasi', 'Deskripsi']

  const sheetData: any[] = []
  sheetData.push(['MASTER PAKET EDUTRIP'])
  sheetData.push([`Periode: ${tglAwal} - ${tglAkhir}`])
  sheetData.push([])
  sheetData.push(header)

  const merges: any[] = [
    { s: { r: 0, c: 0 }, e: { r: 0, c: header.length - 1 } },
    { s: { r: 1, c: 0 }, e: { r: 1, c: header.length - 1 } },
  ]

  dataSource.value.forEach((e: any) => {
    const row = [e.no, e.nama_paket, `${e.jumlah_hari} Hari`, e.deskripsi ?? '-']

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
    alignment: { horizontal: 'center', vertical: 'center', wrapText: true },
  }
  header.forEach((_, col) => {
    const cell = XLSX.utils.encode_cell({ r: 3, c: col })
    if (worksheet[cell]) worksheet[cell].s = headerStyle
  })

  // Set column width
  worksheet['!cols'] = [
    { wch: 5 }, // No
    { wch: 30 }, // Nama Paket
    { wch: 20 }, // Waktu Konsultasi
    { wch: 50 }, // Deskripsi
  ]

  XLSX.utils.book_append_sheet(workbook, worksheet, 'Master Paket Edutrip')

  const fileName = `Master Paket Edutrip ${tglAwal} - ${tglAkhir}.xlsx`
  XLSXStyle.writeFile(workbook, fileName)
}

const fetchStatusTransaksi = async (filter: any) => {
  try {
    const response = await useApi().get(
      `/sistem-admin/utility/dropdown/status_transaksi_m?select=id,nama_status&param_search=nama_status&query=${filter.query}&limit=10`,
    )
    d_StatusTransaksi.value = response.data
  } catch (error) {
    console.error('Error fetching status transaksi:', error)
    d_StatusTransaksi.value = []
  }
}

onMounted(() => {
  updateIsMobile()
  if (typeof window !== 'undefined') window.addEventListener('resize', updateIsMobile)
  fetchData()
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
  if (typeof window !== 'undefined') window.removeEventListener('resize', updateIsMobile)
})
</script>

<style scoped>
.datatable-fixed-height :deep(.p-datatable-table-container) {
  min-height: 420px;
  overflow: visible !important;
  max-height: none !important;
}
@media (max-width: 767px) {
  .datatable-mobile :deep(.p-datatable-wrapper) { overflow-x: auto; -webkit-overflow-scrolling: touch; }
  .datatable-mobile :deep(.p-paginator) { flex-wrap: wrap; gap: 0.25rem; padding: 0.5rem; }
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
