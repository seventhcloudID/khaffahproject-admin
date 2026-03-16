<template>
  <div class="w-full max-w-full overflow-x-hidden">
    <Card class="w-full max-w-full px-4 sm:px-6 py-4 sm:py-6 box-border" elevated radius="smooth" padding="md">
      <h1 class="text-lg sm:text-xl font-medium mb-3 sm:mb-4">Pendaftaran Mitra</h1>
      <p class="text-sm text-gray-600 mb-4">
        Menampilkan semua pendaftaran. Proses:
        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 text-xs font-medium">Pending {{ countPending }}</span>
        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 text-xs font-medium sm:ml-1 mt-1 sm:mt-0">Diproses {{ countProses }}</span>
        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-green-100 text-green-800 text-xs font-medium sm:ml-1 mt-1 sm:mt-0">Disetujui {{ countDisetujui }}</span>
        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-red-100 text-red-800 text-xs font-medium sm:ml-1 mt-1 sm:mt-0">Ditolak {{ countDitolak }}</span>
      </p>

      <div class="grid grid-cols-1 md:grid-cols-12 gap-3 mb-4 md:items-end">
        <div class="hidden md:block md:col-span-7"></div>
        <div class="md:col-span-3">
          <Field class="w-full" label="Tanggal Pendaftaran" id="tgl-mitra" required :horizontal="!isMobile">
            <DatePicker
              v-model="item.tanggalPendaftaran"
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
            <div v-for="(row, idx) in mobileCardsSlice" :key="row.id_mitra ?? `mitra-${mobilePage}-${idx}`" class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
              <div class="flex items-start justify-between gap-2">
                <div class="min-w-0 flex-1">
                  <p class="text-xs text-gray-500 mb-0.5">{{ row.tanggal_pendaftaran ? H.formatDate(row.tanggal_pendaftaran, 'DD/MM/YYYY') : '-' }}</p>
                  <h3 class="font-semibold text-gray-900 text-sm truncate">{{ row.gelar }} {{ row.nama_lengkap }}</h3>
                  <p class="text-xs text-gray-500 truncate mt-0.5">{{ row.nik }}</p>
                  <p class="text-xs text-gray-600 truncate mt-0.5">{{ row.nama_kecamatan }}, {{ row.nama_kota }}</p>
                  <span
                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium mt-2 min-h-[28px]"
                    :class="{
                      'bg-amber-100 text-amber-800': row.status_kode === 'pending',
                      'bg-blue-100 text-blue-800': row.status_kode === 'diproses',
                      'bg-green-100 text-green-800': row.status_kode === 'disetujui',
                      'bg-red-100 text-red-800': row.status_kode === 'ditolak',
                      'bg-gray-100 text-gray-700': !row.status_kode
                    }"
                  >
                    {{ row.status_kode === 'disetujui' ? 'Mitra' : (row.nama_status || '-') }}
                  </span>
                </div>
                <button
                  type="button"
                  @click.stop="toggleMenu($event, row)"
                  class="action-btn inline-flex items-center justify-center w-10 h-10 min-w-10 min-h-10 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-full transition-colors border border-gray-200 flex-shrink-0"
                  aria-label="Aksi"
                >
                  <i class="fas fa-ellipsis-v"></i>
                </button>
              </div>
              <div class="flex items-center gap-2 mt-3 pt-3 border-t border-gray-100">
                <a
                  :href="`https://wa.me/${(row.no_handphone || '').replace(/^0/, '62').replace(/\D/g, '')}`"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="inline-flex items-center justify-center w-10 h-10 bg-green-500 hover:bg-green-600 rounded-full transition-colors flex-shrink-0"
                  title="WhatsApp"
                >
                  <i class="fab fa-whatsapp text-white"></i>
                </a>
                <span class="text-xs text-gray-500 truncate flex-1 min-w-0">{{ row.no_handphone }}</span>
              </div>
              <p class="text-xs text-gray-500 mt-1">Ijin: {{ row.masa_berlaku_ijin_usaha ? H.formatDate(row.masa_berlaku_ijin_usaha, 'DD/MM/YYYY') : '-' }}</p>
            </div>
          </div>
          <div v-if="mobileTotalPages > 1" class="flex items-center justify-center gap-2 pt-4 pb-2">
            <button type="button" class="px-3 py-2 rounded-lg border border-gray-300 text-sm font-medium disabled:opacity-50" :disabled="mobilePage <= 1" @click="mobilePage = Math.max(1, mobilePage - 1)">Prev</button>
            <span class="text-sm text-gray-600 px-2">{{ mobilePage }} / {{ mobileTotalPages }}</span>
            <button type="button" class="px-3 py-2 rounded-lg border border-gray-300 text-sm font-medium disabled:opacity-50" :disabled="mobilePage >= mobileTotalPages" @click="mobilePage = Math.min(mobileTotalPages, mobilePage + 1)">Next</button>
          </div>
          <p v-if="dataSource.length === 0" class="text-center text-gray-500 py-6 text-sm">Tidak ada data.</p>
        </template>
      </div>

      <!-- Desktop: Tabel (v-if agar tabel di-mount saat tampil, data keluar) -->
      <div v-if="!isMobile" class="overflow-x-auto -mx-4 sm:mx-0 rounded-lg border border-gray-200">
        <div v-if="loadSearch" class="flex justify-center py-12">
          <i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i>
        </div>
        <template v-else>
          <p v-if="dataSource.length === 0" class="text-center text-gray-500 py-8 text-sm">Tidak ada data.</p>
          <DataTable
            v-else
            :key="'mitra-' + dataSource.length"
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
          <Column header="Tanggal Pendaftaran" style="text-align: center">
            <template #body="slotProps">
              {{ slotProps.data.tanggal_pendaftaran ? H.formatDate(slotProps.data.tanggal_pendaftaran, 'DD/MM/YYYY') : '-' }}
            </template>
          </Column>
          <Column header="Nama Lengkap">
            <template #body="slotProps">{{ slotProps.data.gelar }} {{ slotProps.data.nama_lengkap }}</template>
          </Column>
          <Column field="nik" header="NIK" />
          <Column header="No Whatsapp">
            <template #body="slotProps">
              <div class="flex items-center gap-2">
                <span>{{ slotProps.data.no_handphone }}</span>
                <a :href="`https://wa.me/${slotProps.data.no_handphone?.replace(/^0/, '62').replace(/\D/g, '')}`" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center w-7 h-7 bg-green-500 hover:bg-green-600 rounded-full transition-colors" title="Chat via WhatsApp">
                  <i class="fab fa-whatsapp text-white text-sm"></i>
                </a>
              </div>
            </template>
          </Column>
          <Column header="Domisiil Mitra">
            <template #body="slotProps">
              {{ slotProps.data.nama_kecamatan }}, {{ slotProps.data.nama_kota }}, {{ slotProps.data.nama_provinsi }}
            </template>
          </Column>
          <Column field="nomor_ijin_usaha" header="Nomor Ijin Usaha" />
          <Column header="Status / Proses">
            <template #body="slotProps">
              <span
                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium"
                :class="{
                  'bg-amber-100 text-amber-800': slotProps.data.status_kode === 'pending',
                  'bg-blue-100 text-blue-800': slotProps.data.status_kode === 'diproses',
                  'bg-green-100 text-green-800': slotProps.data.status_kode === 'disetujui',
                  'bg-red-100 text-red-800': slotProps.data.status_kode === 'ditolak',
                  'bg-gray-100 text-gray-700': !slotProps.data.status_kode
                }"
              >
                {{ slotProps.data.status_kode === 'disetujui' ? 'Mitra' : (slotProps.data.nama_status || '-') }}
              </span>
            </template>
          </Column>
          <Column header="Masa Berlaku Ijin Usaha" style="min-width: 160px">
            <template #body="slotProps">
              {{ slotProps.data.masa_berlaku_ijin_usaha ? H.formatDate(slotProps.data.masa_berlaku_ijin_usaha, 'DD/MM/YYYY') : '-' }}
            </template>
          </Column>
          <Column header="#" headerClass="text-center action-column" bodyClass="text-center action-column" :headerStyle="{ minWidth: '80px', width: '80px' }" :bodyStyle="{ minWidth: '80px', width: '80px' }">
            <template #body="slotProps">
              <button type="button" @click.stop="toggleMenu($event, slotProps.data)" class="action-btn inline-flex items-center justify-center w-8 h-8 min-w-8 min-h-8 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-full transition-colors border border-gray-200">
                <i class="fas fa-ellipsis-v"></i>
              </button>
            </template>
          </Column>
        </DataTable>
        </template>
      </div>
    </Card>

    <Menu ref="menu" :model="getMenuItems(currentRowData)" :popup="true" appendTo="body">
      <template #item="{ item: menuItem }">
        <a class="flex items-center gap-2 px-3 py-2.5 sm:py-2 cursor-pointer hover:bg-gray-100 min-h-[44px] sm:min-h-0">
          <i :class="[menuItem.icon, menuItem.iconColor]"></i>
          <span>{{ menuItem.label }}</span>
        </a>
      </template>
    </Menu>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, onUnmounted, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import DataTable from 'primevue/datatable'
import AutoComplete from 'primevue/autocomplete'
import DatePicker from 'primevue/datepicker'
import Column from 'primevue/column'
import { useApi } from '@/api/useApi'
import * as XLSX from 'xlsx'
import * as XLSXStyle from 'xlsx-js-style'
import H from '@/utils/appHelper'
import Menu from 'primevue/menu'

const api = useApi()
const router = useRouter()
const menu = ref()
const currentRowData = ref(null)

const dataSource = ref([])
const d_StatusTransaksi = ref([])
const item = ref({
  tanggalPendaftaran: [new Date(), new Date()],
})
const loadSearch = ref(false)
const dataJamaah = ref(null)
const transactionDetail = ref(null)
const activeDropdown = ref(null)
const selectedStatusTransaksi = ref(null)
const idTransaksiUpdate = ref(null)

const ModalDetail = ref(false)
const ModalUpdateStatus = ref(false)

const countPending = ref(0)
const countProses = ref(0)
const countDisetujui = ref(0)
const countDitolak = ref(0)

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

const handleClose = () => {
  dataJamaah.value = null
  ModalDetail.value = false
}

const getMenuItems = (_data: any) => {
  const row = currentRowData.value
  const status = row?.status_kode

  switch (status) {
    case 'pending':
      return [
        {
          label: 'Proses Pendaftaran',
          icon: 'fas fa-paper-plane',
          iconColor: 'text-blue-600',
          command: () => proses(row),
        },
        {
          label: 'Verifikasi',
          icon: 'fas fa-check-circle',
          iconColor: 'text-green-600',
          command: () => verifikasi(row),
        },
      ]

    case 'diproses':
      return [
        {
          label: 'Verifikasi',
          icon: 'fas fa-paper-plane',
          iconColor: 'text-green-600',
          command: () => verifikasi(row),
        },
      ]

    case 'disetujui':
      return [
        {
          label: 'Lihat Detail',
          icon: 'fas fa-eye',
          iconColor: 'text-blue-600',
          command: () => bukaDetail(row),
        },
      ]

    case 'ditolak':
      return [
        {
          label: 'Lihat Detail',
          icon: 'fas fa-eye',
          iconColor: 'text-blue-600',
          command: () => bukaDetail(row),
        },
        {
          label: 'Proses Ulang',
          icon: 'fas fa-redo',
          iconColor: 'text-orange-600',
          command: () => prosesUlang(row),
        },
      ]

    default:
      return []
  }
}

const toggleMenu = (event: any, data: any) => {
  currentRowData.value = data
  menu.value.toggle(event)
}

const bukaDetail = (data: any) => {
  // `data` is the full transaction object
  transactionDetail.value = data

  const jamaah = data?.jamaah_data ?? []
  dataJamaah.value = jamaah.map((el: any, i: any) => ({ ...el, no: i + 1 }))

  console.log('Data jamaah untuk transaksi:', dataJamaah.value)

  ModalDetail.value = true
}

// === DROPDOWN FUNCTIONS ===
const toggleDropdown = (id: number) => {
  activeDropdown.value = activeDropdown.value === id ? null : id
}

const updateStatus = (id: number) => {
  idTransaksiUpdate.value = id
  ModalUpdateStatus.value = true
}

const proses = async (data: any) => {
  console.log('ID pendaftaran mitra:', data.id_mitra)

  try {
    const payload = {
      id_mitra: data.id_mitra,
    }
    const res = await api.post('/sistem-admin/mitra/proses-mitra', payload) as unknown as Record<string, unknown>
    if (res && (res.status === true || res.status === 'success' || res.status === 1)) {
      ModalUpdateStatus.value = false
      selectedStatusTransaksi.value = null
      idTransaksiUpdate.value = null

      fetchData()
    }
  } catch (err: unknown) {
    console.error('Error update status:', err)
    const e = err as { response?: { data?: { message?: string } }; message?: string }
    const msg = e?.response?.data?.message ?? e?.message ?? 'Terjadi kesalahan saat update status.'
    alert(msg)
  }
}

const verifikasi = async (data: any) => {
  router.push({
    name: 'verifikasi-mitra',
    params: { id: data.id_mitra },
    query: { nama: data.nama_lengkap },
  })
}

const prosesUlang = (data: any) => {
  proses(data)
}

// === END DROPDOWN FUNCTIONS ===

const fetchData = async () => {
  try {
    loadSearch.value = true

    const { dateStart, dateEnd } = H.parseDateRange(item.value.tanggalPendaftaran)
    const tglAwal = H.formatDate(dateStart, 'YYYY-MM-DD')
    const tglAkhir = H.formatDate(dateEnd, 'YYYY-MM-DD')

    const res = await api.get(
      `/sistem-admin/mitra/get-list-pendaftaran-mitra?tglAwal=${tglAwal}&tglAkhir=${tglAkhir}`,
    ) as unknown as Record<string, unknown>
    // useApi mengembalikan body JSON { status, message, data: [...] }; fallback jika bentuk lain
    const raw = res
    let list: any[] = []
    if (Array.isArray(raw?.data)) {
      list = raw.data as any[]
    } else if (Array.isArray((raw?.data as Record<string, unknown>)?.data)) {
      list = (raw.data as Record<string, unknown>).data as any[]
    } else if (Array.isArray(raw)) {
      list = raw as any[]
    }
    list.forEach((element: any, i: number) => {
      element.no = i + 1
    })

    dataSource.value = list
    mobilePage.value = 1
    await updateCount()
  } catch (error) {
    console.error('Error fetching data:', error)
    dataSource.value = []
  } finally {
    loadSearch.value = false
  }
}

const updateCount = async () => {
  const { dateStart, dateEnd } = H.parseDateRange(item.value.tanggalPendaftaran)
  const tglAwal = H.formatDate(dateStart, 'YYYY-MM-DD')
  const tglAkhir = H.formatDate(dateEnd, 'YYYY-MM-DD')

  const res = await api.get(
    `/sistem-admin/mitra/get-count-pendaftaran-mitra?tglAwal=${tglAwal}&tglAkhir=${tglAkhir}`,
  ) as { data?: { pending?: number; diproses?: number; disetujui?: number; ditolak?: number } }
  const d = res?.data ?? {}
  countPending.value = d.pending ?? 0
  countProses.value = d.diproses ?? 0
  countDisetujui.value = d.disetujui ?? 0
  countDitolak.value = d.ditolak ?? 0
}

const exportExcel = () => {
  const workbook = XLSX.utils.book_new()

  let { dateStart, dateEnd } = H.parseDateRange(item.value.tanggalPendaftaran)
  const tglAwal = H.formatDate(dateStart, 'DD-MM-YYYY')
  const tglAkhir = H.formatDate(dateEnd, 'DD-MM-YYYY')

  const header = [
    'No',
    'Tanggal Pendaftaran',
    'Nama Lengkap',
    'NIK',
    'No Whatsapp',
    'Domisili',
    'Nomor Ijin Usaha',
    'Status',
    'Masa Berlaku Ijin Usaha',
  ]

  const sheetData: any[] = []
  sheetData.push(['LAPORAN PENDAFTARAN MITRA'])
  sheetData.push([`Periode: ${tglAwal} - ${tglAkhir}`])
  sheetData.push([])
  sheetData.push(header)

  const merges: any[] = [
    { s: { r: 0, c: 0 }, e: { r: 0, c: header.length - 1 } },
    { s: { r: 1, c: 0 }, e: { r: 1, c: header.length - 1 } },
  ]

  dataSource.value.forEach((e: any) => {
    const domisili = [e.nama_kecamatan, e.nama_kota, e.nama_provinsi].filter(Boolean).join(', ') || '-'
    const row = [
      e.no,
      e.tanggal_pendaftaran ? H.formatDate(e.tanggal_pendaftaran, 'DD/MM/YYYY') : '-',
      `${e.gelar ?? ''} ${e.nama_lengkap ?? ''}`.trim(),
      e.nik ?? '',
      e.no_handphone ?? '',
      domisili,
      e.nomor_ijin_usaha ?? '',
      e.nama_status ?? '-',
      e.masa_berlaku_ijin_usaha ? H.formatDate(e.masa_berlaku_ijin_usaha, 'DD/MM/YYYY') : '-',
    ]
    sheetData.push(row)
  })

  const worksheet = XLSX.utils.aoa_to_sheet(sheetData)
  worksheet['!merges'] = merges

  if (worksheet['A1']) {
    worksheet['A1'].s = {
      font: { bold: true, sz: 16 },
      alignment: { horizontal: 'center', vertical: 'center' },
    }
  }
  if (worksheet['A2']) {
    worksheet['A2'].s = {
      font: { italic: true, sz: 11 },
      alignment: { horizontal: 'center', vertical: 'center' },
    }
  }

  const headerStyle = {
    font: { bold: true, color: { rgb: 'FFFFFF' } },
    fill: { fgColor: { rgb: '4A90E2' } },
    alignment: { horizontal: 'center', vertical: 'center' },
  }
  header.forEach((_, col) => {
    const cell = XLSX.utils.encode_cell({ r: 3, c: col })
    if (worksheet[cell]) worksheet[cell].s = headerStyle
  })

  worksheet['!cols'] = [
    { wch: 5 },
    { wch: 18 },
    { wch: 30 },
    { wch: 18 },
    { wch: 15 },
    { wch: 35 },
    { wch: 20 },
    { wch: 14 },
    { wch: 20 },
  ]

  XLSX.utils.book_append_sheet(workbook, worksheet, 'Pendaftaran Mitra')
  const fileName = `Laporan Pendaftaran Mitra ${tglAwal} - ${tglAkhir}.xlsx`
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
  if (typeof window !== 'undefined') window.removeEventListener('resize', updateIsMobile)
})
</script>

<style scoped>
/* Parent jangan clip kolom kanan */
.datatable.datatable-fixed-height {
  overflow: visible !important;
}
.datatable-fixed-height :deep(.p-datatable-wrapper) {
  overflow-x: auto;
  overflow-y: visible;
}
.datatable-fixed-height :deep(.p-datatable-table-container) {
  min-height: 420px;
  overflow: visible !important;
  max-height: none !important;
}
/* Kolom aksi lebar tetap */
.datatable-fixed-height :deep(th.action-column),
.datatable-fixed-height :deep(td.action-column) {
  min-width: 80px !important;
  width: 80px !important;
  max-width: 80px !important;
  white-space: nowrap;
  vertical-align: middle;
}
.datatable-fixed-height :deep(td.action-column) {
  background: #fff;
}
.datatable-fixed-height :deep(tr:nth-child(even) td.action-column) {
  background: #f9fafb;
}
.datatable-fixed-height :deep(tr:hover td.action-column) {
  background: #f0f9f8;
}
/* Tombol aksi selalu terlihat */
.action-btn {
  flex-shrink: 0;
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
</style>
