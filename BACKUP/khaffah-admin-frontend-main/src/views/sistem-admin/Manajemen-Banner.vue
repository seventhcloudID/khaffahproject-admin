<template>
  <div class="p-4 md:p-6">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
      <h1 class="text-xl font-medium mb-2 text-gray-900">Manajemen Banner</h1>
      <p class="text-gray-600 text-sm mb-4">
        Atur banner yang ditampilkan di halaman depan (http://127.0.0.1:3000) dan halaman lainnya. Gunakan lokasi sesuai section yang ingin ditampilkan.
      </p>
      <div class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg text-amber-800 text-sm">
        <strong>Supaya banner dipakai di homepage:</strong> untuk section &quot;Promo Umrah Plus Liburan&quot; (Edutrip), buat banner dengan <strong>Lokasi = Home - Section Edutrip</strong> dan <strong>Status = Aktif</strong>. Jika tidak ada banner aktif untuk lokasi tersebut, homepage akan menampilkan judul dan gambar default.
      </div>

      <div class="flex flex-wrap gap-3 mb-4">
        <RouterLink
          to="/sistem-admin/Manajemen-Banner/form"
          class="inline-flex items-center gap-2 px-4 py-2 bg-[#007b6f] text-white rounded-lg hover:bg-[#00665c] text-sm font-medium"
        >
          <i class="fas fa-plus"></i>
          Tambah Banner
        </RouterLink>
        <select
          v-model="filterLokasi"
          class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#007b6f]"
        >
          <option value="">Semua lokasi</option>
          <option v-for="opt in lokasiOptions" :key="opt.value" :value="opt.value">
            {{ opt.label }}
          </option>
        </select>
        <select
          v-model="filterAktif"
          class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#007b6f]"
        >
          <option value="">Semua status</option>
          <option value="1">Aktif</option>
          <option value="0">Nonaktif</option>
        </select>
        <button
          type="button"
          @click="fetchData()"
          class="px-4 py-2 border border-[#007b6f] text-[#007b6f] rounded-lg hover:bg-[#007b6f] hover:text-white text-sm font-medium"
        >
          <i class="fas fa-search mr-1"></i>
          Cari
        </button>
      </div>

      <div v-if="errorMessage" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm">
        {{ errorMessage }}
      </div>
      <div v-else-if="!loadSearch && dataSource.length === 0" class="mb-4 p-4 bg-gray-50 border border-gray-200 rounded-lg text-gray-700 text-sm">
        Belum ada banner. Klik <strong>Tambah Banner</strong> untuk menambah.
        <button type="button" @click="fetchData()" class="mt-3 ml-2 px-3 py-1.5 text-sm bg-[#007b6f] text-white rounded hover:bg-[#00665c]">Muat ulang</button>
      </div>

      <div class="overflow-x-auto">
        <DataTable
          :value="dataSource"
          class="datatable datatable-fixed-height"
          :paginator="true"
          :rows="10"
          :rowsPerPageOptions="[5, 10, 20, 50]"
          paginatorTemplate="CurrentPageReport FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
          currentPageReportTemplate="Menampilkan {first} - {last} dari {totalRecords} data"
          :loading="loadSearch"
        >
          <Column field="no" header="No" style="width: 50px" />
          <Column field="judul" header="Judul" />
          <Column field="lokasi" header="Lokasi" style="width: 140px">
            <template #body="slotProps">
              {{ getLokasiLabel(slotProps.data.lokasi) }}
            </template>
          </Column>
          <Column field="urutan" header="Urutan" style="width: 80px" />
          <Column header="Gambar" style="width: 80px">
            <template #body="slotProps">
              <img
                v-if="slotProps.data.gambar_url"
                :src="normalizeBannerImageUrl(slotProps.data.gambar_url)"
                alt="banner"
                class="h-10 w-16 object-cover rounded border border-gray-200"
              />
              <span v-else class="text-gray-400 text-sm">—</span>
            </template>
          </Column>
          <Column header="Status" style="width: 90px">
            <template #body="slotProps">
              <span :class="slotProps.data.is_aktif ? 'text-green-600' : 'text-gray-500'">
                {{ slotProps.data.is_aktif ? 'Aktif' : 'Nonaktif' }}
              </span>
            </template>
          </Column>
          <Column header="#" headerClass="text-center" bodyClass="text-center" :style="{ width: '80px' }">
            <template #body="slotProps">
              <button
                type="button"
                @click.stop="toggleDropdown(slotProps.data.id, $event, slotProps.data)"
                class="inline-flex items-center justify-center w-8 h-8 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-full transition-colors"
              >
                <i class="fas fa-ellipsis-v"></i>
              </button>
            </template>
          </Column>
        </DataTable>
      </div>
    </div>

    <Teleport to="body">
      <div
        v-if="activeDropdown !== null"
        ref="dropdownMenuRef"
        class="action-dropdown-menu"
        :style="dropdownStyle"
        @click.stop
      >
        <div class="py-1">
          <RouterLink
            :to="`/sistem-admin/Manajemen-Banner/form/${activeDropdown}`"
            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2"
            @click="closeDropdown()"
          >
            <i class="fas fa-pencil-alt w-4 text-blue-400 mr-2"></i>
            <span>Edit</span>
          </RouterLink>
          <button
            type="button"
            @click="deleteData(activeDropdownRow)"
            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2"
          >
            <i class="fas fa-trash w-4 text-red-600 mr-2"></i>
            <span>Hapus</span>
          </button>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, nextTick } from 'vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import { useApi } from '@/api/useApi'

const api = useApi()
const dataSource = ref<any[]>([])
const loadSearch = ref(false)
const errorMessage = ref('')
const activeDropdown = ref<number | string | null>(null)
const activeDropdownRow = ref<any>(null)
const dropdownStyle = ref<Record<string, string>>({})
const dropdownMenuRef = ref<HTMLElement | null>(null)
const filterLokasi = ref('')
const filterAktif = ref('')

const lokasiOptions = ref<{ value: string; label: string }[]>([])

const DROPDOWN_WIDTH = 192

function getLokasiLabel(lokasi: string) {
  const o = lokasiOptions.value.find((x) => x.value === lokasi)
  return o ? o.label : lokasi
}

/** URL .../storage/banner/... → .../storage/app/public/banner/... agar gambar tampil (document root = project root) */
function normalizeBannerImageUrl(url: string): string {
  if (!url || typeof url !== 'string') return url
  if (url.includes('/storage/app/public/')) return url
  return url.replace(/\/storage\/banner\//i, '/storage/app/public/banner/')
}

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
  let left = rect.right - DROPDOWN_WIDTH
  if (left < 8) left = 8
  dropdownStyle.value = {
    position: 'fixed',
    top: `${rect.bottom + 8}px`,
    left: `${left}px`,
    zIndex: '9999',
  }
  activeDropdown.value = id
  activeDropdownRow.value = row ?? dataSource.value.find((r: any) => r.id === id) ?? null
  nextTick(() => document.addEventListener('click', handleClickOutside))
}

const deleteData = async (data: any) => {
  closeDropdown()
  if (!confirm(`Hapus banner "${data.judul}"?`)) return
  try {
    await api.delete(`/sistem-admin/banner/${data.id}`)
    fetchData()
  } catch (err: any) {
    const msg = err.response?.data?.message || err.message || 'Gagal menghapus'
    alert(msg)
  }
}

async function loadLokasiOptions() {
  try {
    const res = await api.get('/sistem-admin/banner/lokasi-options')
    const d = res?.data?.data ?? res?.data ?? []
    lokasiOptions.value = Array.isArray(d) ? d : []
  } catch {
    lokasiOptions.value = [
      { value: 'home', label: 'Home (Utama)' },
      { value: 'home_edutrip', label: 'Home - Section Edutrip' },
      { value: 'umrah', label: 'Halaman Program Umrah' },
      { value: 'haji', label: 'Halaman Program Haji' },
      { value: 'edutrip', label: 'Halaman Edutrip' },
      { value: 'request_product', label: 'Request Product' },
      { value: 'land_arrangement', label: 'Land Arrangement' },
      { value: 'join_partner', label: 'Join Partner' },
    ]
  }
}

const fetchData = async () => {
  errorMessage.value = ''
  try {
    loadSearch.value = true
    const params = new URLSearchParams()
    params.set('per_page', '50')
    if (filterLokasi.value) params.set('lokasi', filterLokasi.value)
    if (filterAktif.value !== '') params.set('is_aktif', filterAktif.value)
    const res = await api.get(`/sistem-admin/banner?${params.toString()}`) as { data?: { data?: unknown[]; meta?: { current_page?: number; per_page?: number } }; meta?: { current_page?: number; per_page?: number } }
    const list = res?.data?.data ?? (res as Record<string, unknown>)?.data ?? []
    const meta = res?.data?.meta ?? res?.meta ?? {}
    const items = Array.isArray(list) ? list : []
    items.forEach((el: any, i: number) => {
      el.no = (meta.current_page - 1) * (meta.per_page || 10) + i + 1
    })
    dataSource.value = items
  } catch (err: any) {
    console.error(err)
    dataSource.value = []
    const isNetworkError = err.message === 'Network Error' || err.code === 'ERR_NETWORK'
    const baseMsg = err.response?.data?.message || err.message || 'Gagal memuat data.'
    errorMessage.value = isNetworkError
      ? 'Network Error: Backend tidak terjangkau. Pastikan (1) Laravel berjalan (php artisan serve), (2) file .env admin punya VITE_API_BASE_URL=http://localhost:8000/api (sesuaikan host/port jika beda), (3) restart dev server setelah ubah .env.'
      : baseMsg + ' Pastikan Anda sudah login sebagai superadmin.'
  } finally {
    loadSearch.value = false
  }
}

onMounted(() => {
  loadLokasiOptions()
  fetchData()
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
.datatable-fixed-height :deep(.p-datatable-table-container) {
  min-height: 420px;
  overflow: visible !important;
  max-height: none !important;
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
