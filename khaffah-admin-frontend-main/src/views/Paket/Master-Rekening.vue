<template>
  <div class="p-4 md:p-6">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
      <h1 class="text-xl font-medium mb-2 text-gray-900">Master Rekening</h1>
      <p class="text-gray-600 text-sm mb-4">
        Kelola rekening bank perusahaan yang ditampilkan ke customer untuk pembayaran (transfer).
      </p>

      <div class="flex flex-wrap gap-3 mb-4">
        <RouterLink
          to="/Paket/Master-Rekening/form"
          class="inline-flex items-center gap-2 px-4 py-2 bg-[#007b6f] text-white rounded-lg hover:bg-[#00665c] text-sm font-medium"
        >
          <i class="fas fa-plus"></i>
          Tambah
        </RouterLink>
        <input
          v-model="searchQuery"
          type="text"
          class="flex-1 min-w-[200px] px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
          placeholder="Cari bank, nomor rekening, atau atas nama..."
          @keyup.enter="fetchData()"
        />
        <select
          v-model="filterActive"
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

      <!-- Pesan error -->
      <div v-if="errorMessage" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm">
        {{ errorMessage }}
      </div>
      <!-- Pesan data kosong -->
      <div v-else-if="!loadSearch && dataSource.length === 0" class="mb-4 p-4 bg-gray-50 border border-gray-200 rounded-lg text-gray-700 text-sm">
        <p class="font-medium">Belum ada data rekening.</p>
        <p class="mt-1">Klik <strong>Tambah</strong> untuk menambah rekening bank perusahaan.</p>
        <button type="button" @click="fetchData()" class="mt-3 px-3 py-1.5 text-sm bg-[#007b6f] text-white rounded hover:bg-[#00665c]">Muat ulang</button>
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
          <Column field="bank_name" header="Bank" />
          <Column field="account_number" header="Nomor Rekening" style="width: 140px" />
          <Column field="account_holder_name" header="Atas Nama" />
          <Column field="keterangan" header="Keterangan" />
          <Column field="urutan" header="Urutan" style="width: 80px" />
          <Column header="Status" style="width: 90px">
            <template #body="slotProps">
              <span :class="slotProps.data.is_active ? 'text-green-600' : 'text-gray-500'">
                {{ slotProps.data.is_active ? 'Aktif' : 'Nonaktif' }}
              </span>
            </template>
          </Column>
          <Column header="#" headerClass="text-center" bodyClass="text-center" :style="{ width: '80px' }">
            <template #body="slotProps">
              <div class="relative inline-block text-left">
                <button
                  @click.stop="toggleDropdown(slotProps.data.id, $event, slotProps.data)"
                  type="button"
                  class="inline-flex items-center justify-center w-8 h-8 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-full transition-colors"
                >
                  <i class="fas fa-ellipsis-v"></i>
                </button>
              </div>
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
            :to="`/Paket/Master-Rekening/form/${activeDropdown}`"
            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2"
            @click="closeDropdown()"
          >
            <i class="fas fa-pencil-alt w-4 text-blue-400 mr-2"></i>
            <span>Edit</span>
          </RouterLink>
          <button
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
const searchQuery = ref('')
const filterActive = ref('')

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
  if (!confirm(`Hapus rekening ${data.bank_name} - ${data.account_number}?`)) return
  try {
    await api.delete(`/sistem-admin/master-rekening/${data.id}`)
    fetchData()
  } catch (err: any) {
    const msg = err.response?.data?.message || err.message || 'Gagal menghapus'
    alert(msg)
  }
}

const fetchData = async () => {
  errorMessage.value = ''
  try {
    loadSearch.value = true
    const params = new URLSearchParams()
    params.set('per_page', '100')
    if (searchQuery.value) params.set('search', searchQuery.value)
    if (filterActive.value !== '') params.set('is_active', filterActive.value)
    const res = await api.get(`/sistem-admin/master-rekening?${params.toString()}`) as { data?: { data?: unknown[]; meta?: { current_page?: number; per_page?: number } }; meta?: { current_page?: number; per_page?: number } }
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
    errorMessage.value = err.response?.data?.message || err.message || 'Gagal memuat data. Pastikan backend berjalan dan Anda sudah login.'
  } finally {
    loadSearch.value = false
  }
}

onMounted(() => {
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
