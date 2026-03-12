<template>
  <div class="w-full max-w-full overflow-x-hidden">
    <Card class="w-full max-w-full px-4 sm:px-6 py-4 sm:py-6 box-border" elevated radius="smooth" padding="md">
      <h1 class="text-lg sm:text-xl font-medium mb-3 sm:mb-4">Daftar Refund (Pengembalian Dana)</h1>

      <!-- Filter -->
      <div class="grid grid-cols-1 md:grid-cols-12 gap-3 mb-4 md:items-end">
        <div class="md:col-span-3">
          <Field class="w-full" label="Tanggal Pengajuan Refund (Opsional)" id="tgl-refund" :horizontal="!isMobile">
            <DatePicker
              v-model="item.tanggalPengajuan"
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
        <div class="md:col-span-7 flex items-end justify-end gap-2">
          <span class="text-sm text-gray-600 py-2">
            <span class="font-semibold text-amber-600">{{ countDiajukan }}</span> pengajuan refund
          </span>
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
                <span class="inline-flex items-center self-start text-[11px] sm:text-xs px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 min-h-[28px]">{{ row.status_nama ?? 'Refund Diajukan' }}</span>
              </div>
              <p class="text-sm text-gray-700 font-medium truncate">{{ row.gelar }} {{ row.nama_lengkap }}</p>
              <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">{{ row.nama_paket }}</p>
              <p class="text-xs text-gray-500 mt-1">{{ row.refund_requested_at ? H.formatDate(row.refund_requested_at, 'DD/MM/YYYY HH:mm') : '-' }}</p>
              <p v-if="row.refund_alasan" class="text-xs text-gray-600 mt-1 line-clamp-2">{{ row.refund_alasan }}</p>
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
                  <button type="button" class="px-3 py-1.5 text-xs font-medium text-[#007b6f] border border-[#007b6f] rounded-lg active:scale-[0.98]" @click="goToDetail(row)">Detail</button>
                  <button type="button" class="px-3 py-1.5 text-xs font-medium text-blue-600 border border-blue-600 rounded-lg active:scale-[0.98]" @click="openUpdateStatus(row)">Update Status</button>
                </div>
              </div>
            </div>
          </div>
          <div v-if="mobileTotalPages > 1" class="flex items-center justify-center gap-2 pt-4 pb-2">
            <button type="button" class="px-3 py-2 rounded-lg border border-gray-300 text-sm font-medium disabled:opacity-50 disabled:pointer-events-none" :disabled="mobilePage <= 1" @click="mobilePage = Math.max(1, mobilePage - 1)">Prev</button>
            <span class="text-sm text-gray-600 px-2">{{ mobilePage }} / {{ mobileTotalPages }}</span>
            <button type="button" class="px-3 py-2 rounded-lg border border-gray-300 text-sm font-medium disabled:opacity-50 disabled:pointer-events-none" :disabled="mobilePage >= mobileTotalPages" @click="mobilePage = Math.min(mobileTotalPages, mobilePage + 1)">Next</button>
          </div>
          <p v-if="dataSource.length === 0" class="text-center text-gray-500 py-6 text-sm">Tidak ada pengajuan refund.</p>
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
          <Column header="Tanggal Pengajuan" style="min-width: 140px">
            <template #body="slotProps">{{ slotProps.data.refund_requested_at ? H.formatDate(slotProps.data.refund_requested_at, 'DD/MM/YYYY HH:mm') : '-' }}</template>
          </Column>
          <Column field="nama_paket" header="Nama Paket / Tipe" />
          <Column header="Pemesan">
            <template #body="slotProps">
              <span>{{ slotProps.data.gelar }} {{ slotProps.data.nama_lengkap }}</span>
              <span v-if="slotProps.data.pemesan_email" class="block text-xs text-gray-500">{{ slotProps.data.pemesan_email }}</span>
            </template>
          </Column>
          <Column header="No WhatsApp">
            <template #body="slotProps">
              <div class="flex items-center gap-2">
                <span>{{ slotProps.data.no_whatsapp }}</span>
                <a :href="`https://wa.me/${(slotProps.data.no_whatsapp || '').replace(/^0/, '62').replace(/\D/g, '')}`" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center w-7 h-7 bg-green-500 hover:bg-green-600 rounded-full transition-colors" title="Chat via WhatsApp">
                  <i class="fab fa-whatsapp text-white text-sm"></i>
                </a>
              </div>
            </template>
          </Column>
          <Column header="Total Biaya" style="min-width: 100px">
            <template #body="slotProps">{{ slotProps.data.total_biaya != null ? H.formatRupiah(slotProps.data.total_biaya) : '-' }}</template>
          </Column>
          <Column field="refund_alasan" header="Alasan Refund" style="max-width: 200px">
            <template #body="slotProps">
              <span class="line-clamp-2 block" :title="slotProps.data.refund_alasan">{{ slotProps.data.refund_alasan || '-' }}</span>
            </template>
          </Column>
          <Column header="Aksi" headerClass="text-center" bodyClass="text-center" :style="{ width: '140px' }">
            <template #body="slotProps">
              <div class="flex items-center justify-center gap-1 flex-wrap">
                <button type="button" class="px-2 py-1.5 text-xs font-medium text-[#007b6f] border border-[#007b6f] rounded-lg hover:bg-teal-50" @click="goToDetail(slotProps.data)">Detail</button>
                <button type="button" class="px-2 py-1.5 text-xs font-medium text-blue-600 border border-blue-600 rounded-lg hover:bg-blue-50" @click="openUpdateStatus(slotProps.data)">Update Status</button>
              </div>
            </template>
          </Column>
        </DataTable>
      </div>
    </Card>

    <!-- Modal Update Status -->
    <Modal
      :open="modalUpdateStatus"
      size="small"
      rounded
      noscroll
      :closeOnEsc="true"
      :closeOnOutside="false"
      actions="right"
      cancelLabel="Tutup"
      actionLabel="Simpan"
      :showAction="true"
      @close="modalUpdateStatus = false"
      @action="handleUpdateStatus()"
    >
      <template #header>
        <div class="w-full flex items-center">
          <h3 class="font-semibold">Update Status Transaksi</h3>
          <button class="ml-auto text-gray-500 hover:text-red-500" @click="modalUpdateStatus = false">✕</button>
        </div>
      </template>
      <template #content>
        <div class="column is-12 text-center">
          <label for="status-transaksi-refund">Status Transaksi (setelah proses refund)</label>
          <Dropdown
            id="status-transaksi-refund"
            v-model="selectedStatusTransaksi"
            :options="d_StatusTransaksi"
            optionLabel="label"
            optionValue="value"
            placeholder="Pilih Status (mis. Dibatalkan)..."
            class="w-full"
            appendTo="body"
          />
        </div>
      </template>
    </Modal>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import DataTable from 'primevue/datatable'
import Dropdown from 'primevue/dropdown'
import DatePicker from 'primevue/datepicker'
import Column from 'primevue/column'
import { useApi } from '@/api/useApi'
import * as XLSX from 'xlsx'
import H from '@/utils/appHelper'
import Card from '@/components/base/card/Card.vue'
import Button from '@/components/base/button/Button.vue'
import Field from '@/components/base/form/Field.vue'
import Modal from '@/components/base/modal/Modal.vue'

const router = useRouter()
const api = useApi()

const dataSource = ref<any[]>([])
const item = ref({ tanggalPengajuan: null as any })
const loadSearch = ref(false)
const countDiajukan = ref(0)

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

const modalUpdateStatus = ref(false)
const selectedStatusTransaksi = ref<number | null>(null)
const idTransaksiUpdate = ref<number | null>(null)
const d_StatusTransaksi = ref<any[]>([])

function detailPath(row: any): string {
  const jenisId = row.jenis_transaksi_id
  const id = row.id
  if (jenisId === 1) return `/Daftar-Transaksi/Pemesanan-Paket-Umrah/${id}`
  if (jenisId === 2) return `/Daftar-Transaksi/Pendaftaran-Haji/${id}`
  if (jenisId === 3) return `/Daftar-Transaksi/Peminat-Edutrip/${id}`
  return `/Daftar-Transaksi/Permintaan-Custom/${id}`
}

const goToDetail = (row: any) => {
  router.push(detailPath(row))
}

const openUpdateStatus = (row: any) => {
  idTransaksiUpdate.value = row.id
  selectedStatusTransaksi.value = null
  modalUpdateStatus.value = true
  fetchStatusTransaksi()
}

const fetchStatusTransaksi = async () => {
  try {
    const response = await api.get('/sistem-admin/utility/dropdown/status_transaksi_m?select=id,nama_status&limit=50') as { data?: unknown[] }
    const raw = Array.isArray(response) ? response : (response as any)?.data ?? response
    const arr = Array.isArray(raw) ? raw : []
    d_StatusTransaksi.value = arr.map((x: any) => ({
      id: x.value ?? x.id,
      label: x.label ?? x.nama_status ?? `#${x.value ?? x.id}`,
      value: x.value ?? x.id,
    }))
  } catch (e) {
    console.error('Error fetching status transaksi:', e)
    d_StatusTransaksi.value = []
  }
}

const handleUpdateStatus = async () => {
  if (selectedStatusTransaksi.value == null) {
    alert('Silakan pilih status transaksi terlebih dahulu (mis. Dibatalkan).')
    return
  }
  if (!idTransaksiUpdate.value) return
  try {
    const res = await api.post('/sistem-admin/transaksi/update-status-transaksi', {
      id: idTransaksiUpdate.value,
      status_transaksi_id: selectedStatusTransaksi.value,
    })
    if (res && (res as any).status === true) {
      modalUpdateStatus.value = false
      fetchData()
      updateCount()
      alert('Status transaksi berhasil diupdate.')
    } else {
      alert((res as any)?.message ?? 'Update status gagal.')
    }
  } catch (error: any) {
    const msg = error?.response?.data?.message ?? error?.message ?? 'Terjadi kesalahan.'
    alert(msg)
  }
}

const fetchData = async () => {
  try {
    loadSearch.value = true
    const { dateStart, dateEnd } = H.parseDateRange(item.value.tanggalPengajuan)
    let url = '/sistem-admin/transaksi/get-list-refund'
    if (dateStart && dateEnd) {
      const tglAwal = H.formatDate(dateStart, 'YYYY-MM-DD')
      const tglAkhir = H.formatDate(dateEnd, 'YYYY-MM-DD')
      url += `?tglAwal=${tglAwal}&tglAkhir=${tglAkhir}`
    }
    const response = await api.get(url) as { data?: any[] }
    const list = Array.isArray(response?.data) ? response.data : []
    list.forEach((el: any, i: number) => { el.no = i + 1 })
    dataSource.value = list
    mobilePage.value = 1
    updateCount()
  } catch (error: any) {
    console.error('Error fetching Daftar Refund:', error)
    alert(error?.response?.data?.message ?? 'Gagal memuat data refund.')
  } finally {
    loadSearch.value = false
  }
}

const updateCount = async () => {
  try {
    const { dateStart, dateEnd } = H.parseDateRange(item.value.tanggalPengajuan)
    let url = '/sistem-admin/transaksi/get-count-refund'
    if (dateStart && dateEnd) {
      const tglAwal = H.formatDate(dateStart, 'YYYY-MM-DD')
      const tglAkhir = H.formatDate(dateEnd, 'YYYY-MM-DD')
      url += `?tglAwal=${tglAwal}&tglAkhir=${tglAkhir}`
    }
    const res = await api.get(url) as { data?: { diajukan?: number } }
    countDiajukan.value = res?.data?.diajukan ?? 0
  } catch {
    countDiajukan.value = 0
  }
}

const exportExcel = () => {
  const workbook = XLSX.utils.book_new()
  const header = ['No', 'Kode Transaksi', 'Tanggal Pengajuan', 'Nama Paket', 'Pemesan', 'Email', 'No WhatsApp', 'Total Biaya', 'Alasan Refund']
  const rows = dataSource.value.map((e: any, i: number) => [
    i + 1,
    e.kode_transaksi ?? '',
    e.refund_requested_at ? H.formatDate(e.refund_requested_at, 'DD/MM/YYYY HH:mm') : '',
    e.nama_paket ?? '',
    `${e.gelar || ''} ${e.nama_lengkap || ''}`.trim(),
    e.pemesan_email ?? '',
    e.no_whatsapp ?? '',
    e.total_biaya != null ? e.total_biaya : '',
    e.refund_alasan ?? '',
  ])
  const sheetData = [['LAPORAN DAFTAR REFUND'], [], header, ...rows]
  const ws = XLSX.utils.aoa_to_sheet(sheetData)
  XLSX.utils.book_append_sheet(workbook, ws, 'Daftar Refund')
  XLSX.writeFile(workbook, `Daftar-Refund-${H.formatDate(new Date(), 'YYYY-MM-DD')}.xlsx`)
}

onMounted(() => {
  updateIsMobile()
  window.addEventListener('resize', updateIsMobile)
  fetchData()
})

onUnmounted(() => {
  window.removeEventListener('resize', updateIsMobile)
})
</script>
