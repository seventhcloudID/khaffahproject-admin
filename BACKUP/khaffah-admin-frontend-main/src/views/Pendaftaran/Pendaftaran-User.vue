<template>
  <div class="w-full max-w-full overflow-x-hidden">
    <Card class="w-full max-w-full px-4 sm:px-6 py-4 sm:py-6 box-border" elevated radius="smooth" padding="md">
      <h1 class="text-lg sm:text-xl font-medium mb-3 sm:mb-4">Pendaftaran User</h1>

      <div class="grid grid-cols-1 md:grid-cols-12 gap-3 mb-4 md:items-end">
        <div class="hidden md:block md:col-span-7"></div>
        <div class="md:col-span-3">
          <Field class="w-full" label="Tanggal Pendaftaran" id="tgl-user" required :horizontal="!isMobile">
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
            <div v-for="(row, idx) in mobileCardsSlice" :key="`user-${mobilePage}-${idx}-${row.email ?? idx}`" class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
              <p class="text-xs text-gray-500 mb-1">{{ row.created_at ? H.formatDate(row.created_at, 'DD/MM/YYYY') : '-' }}</p>
              <h3 class="font-semibold text-gray-900 text-sm truncate">{{ row.gelar }} {{ row.nama_lengkap }}</h3>
              <p class="text-xs text-gray-500 mt-1">{{ row.tgl_lahir ? H.formatDate(row.tgl_lahir, 'DD/MM/YYYY') : '-' }}</p>
              <p class="text-xs text-gray-600 truncate mt-0.5">{{ row.email }}</p>
              <div class="flex items-center justify-between gap-2 mt-3 pt-3 border-t border-gray-100">
                <a
                  :href="`https://wa.me/${(row.no_handphone || '').replace(/^0/, '62').replace(/\D/g, '')}`"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="inline-flex items-center justify-center w-10 h-10 bg-green-500 hover:bg-green-600 rounded-full transition-colors flex-shrink-0"
                  title="WhatsApp"
                >
                  <i class="fab fa-whatsapp text-white"></i>
                </a>
                <span class="text-xs text-gray-500 truncate flex-1 min-w-0 ml-2">{{ row.no_handphone }}</span>
              </div>
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
          <Column header="Tanggal Pendaftaran" style="text-align: left">
            <template #body="slotProps">
              {{ slotProps.data.created_at ? H.formatDate(slotProps.data.created_at, 'DD/MM/YYYY') : '-' }}
            </template>
          </Column>
          <Column header="Nama Lengkap">
            <template #body="slotProps">{{ slotProps.data.gelar }} {{ slotProps.data.nama_lengkap }}</template>
          </Column>
          <Column header="Tanggal Lahir" style="text-align: left">
            <template #body="slotProps">
              {{ slotProps.data.tgl_lahir ? H.formatDate(slotProps.data.tgl_lahir, 'DD/MM/YYYY') : '-' }}
            </template>
          </Column>
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
          <Column field="email" header="Email" />
        </DataTable>
      </div>
    </Card>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import DataTable from 'primevue/datatable'
import DatePicker from 'primevue/datepicker'
import Column from 'primevue/column'
import { useApi } from '@/api/useApi'
import * as XLSX from 'xlsx'
import * as XLSXStyle from 'xlsx-js-style'
import H from '@/utils/appHelper'

const api = useApi()

const dataSource = ref([])
const item = ref({
  tanggalPendaftaran: [new Date(), new Date()],
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

const fetchData = async () => {
  try {
    loadSearch.value = true

    let { dateStart, dateEnd } = H.parseDateRange(item.value.tanggalPendaftaran)
    let tglAwal = H.formatDate(dateStart, 'YYYY-MM-DD')
    let tglAkhir = H.formatDate(dateEnd, 'YYYY-MM-DD')

    const response = await api.get(
      `/sistem-admin/user/get-list-user-aktif?tglAwal=${tglAwal}&tglAkhir=${tglAkhir}`,
    )
    // useApi mengembalikan response.data (body), jadi array ada di response.data
    const list = Array.isArray((response as any)?.data) ? (response as any).data : []
    list.forEach((element: any, i: number) => {
      element.no = i + 1
    })
    dataSource.value = list
    mobilePage.value = 1
  } catch (error) {
    console.error('Error fetching data:', error)
  } finally {
    loadSearch.value = false
  }
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
    'Tanggal Lahir',
    'No Whatsapp',
    'Email',
  ]

  const sheetData: any[] = []
  sheetData.push(['LAPORAN PENDAFTARAN USER'])
  sheetData.push([`Periode: ${tglAwal} - ${tglAkhir}`])
  sheetData.push([])
  sheetData.push(header)

  const merges: any[] = [
    { s: { r: 0, c: 0 }, e: { r: 0, c: header.length - 1 } },
    { s: { r: 1, c: 0 }, e: { r: 1, c: header.length - 1 } },
  ]

  dataSource.value.forEach((e: any) => {
    const row = [
      e.no,
      e.created_at ? H.formatDate(e.created_at, 'DD/MM/YYYY') : '-',
      `${e.gelar ?? ''} ${e.nama_lengkap ?? ''}`.trim(),
      e.tgl_lahir ? H.formatDate(e.tgl_lahir, 'DD/MM/YYYY') : '-',
      e.no_handphone ?? '',
      e.email ?? '',
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
    { wch: 14 },
    { wch: 15 },
    { wch: 28 },
  ]

  XLSX.utils.book_append_sheet(workbook, worksheet, 'Pendaftaran User')
  const fileName = `Laporan Pendaftaran User ${tglAwal} - ${tglAkhir}.xlsx`
  XLSXStyle.writeFile(workbook, fileName)
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
</style>
