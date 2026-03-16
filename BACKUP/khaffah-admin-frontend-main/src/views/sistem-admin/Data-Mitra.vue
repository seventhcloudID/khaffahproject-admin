<template>
  <div class="data-mitra-page">
    <div class="page-header">
      <div class="page-header-inner">
        <h1 class="page-title">
          <i class="fas fa-handshake page-title-icon"></i>
          Data Mitra
        </h1>
        <p class="page-subtitle">
          Daftar semua akun mitra beserta level dan persen potongan harga.
        </p>
        <div v-if="!loading" class="page-stats">
          <span class="stats-badge">
            <i class="fas fa-users"></i>
            {{ filteredList.length }} mitra
          </span>
        </div>
      </div>
    </div>

    <div class="toolbar">
      <div class="toolbar-search">
        <i class="fas fa-search toolbar-search-icon"></i>
        <input
          v-model="searchQuery"
          type="text"
          class="toolbar-input"
          placeholder="Cari nama, email, atau NIK..."
        />
      </div>
      <select v-model="filterStatus" class="toolbar-select">
        <option value="">Semua status</option>
        <option value="disetujui">Disetujui (Mitra)</option>
        <option value="pending">Pending</option>
        <option value="diproses">Diproses</option>
        <option value="ditolak">Ditolak</option>
      </select>
      <button type="button" class="btn btn-primary" :disabled="loading" @click="fetchData">
        <i class="fas fa-search"></i>
        Cari
      </button>
      <button type="button" class="btn btn-outline" :disabled="loading" @click="fetchData">
        <i class="fas fa-sync-alt"></i>
        Refresh
      </button>
    </div>

    <div class="table-wrap">
      <div v-if="loading" class="loading-wrap">
        <i class="fas fa-spinner fa-spin loading-spinner"></i>
        <p class="loading-text">Memuat data...</p>
      </div>

      <DataTable
        v-else
        :value="filteredList"
        class="data-mitra-table"
        :paginator="true"
        :rows="10"
        :rowsPerPageOptions="[5, 10, 20, 50, 100]"
        paginatorTemplate="CurrentPageReport FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
        currentPageReportTemplate="Menampilkan {first} - {last} dari {totalRecords} mitra"
        responsiveLayout="scroll"
      >
        <template #empty>
          <div class="empty-state">
            <i class="fas fa-handshake empty-icon"></i>
            <p class="empty-title">Tidak ada data mitra</p>
            <p class="empty-desc">Ubah filter atau refresh untuk memuat ulang.</p>
          </div>
        </template>
        <Column field="no" header="No" class="col-no" />
        <Column field="nama_lengkap" header="Nama Lengkap" class="col-nama" />
        <Column field="email" header="Email" class="col-email" />
        <Column header="No. HP" class="col-hp">
          <template #body="{ data }">
            {{ data.no_handphone || '–' }}
          </template>
        </Column>
        <Column header="Domisili" class="col-domisili">
          <template #body="{ data }">
            {{ [data.nama_kota, data.nama_provinsi].filter(Boolean).join(', ') || '–' }}
          </template>
        </Column>
        <Column header="Level" class="col-level">
          <template #body="{ data }">
            <span v-if="data.level_nama" class="badge badge-level">
              {{ data.level_nama }}
            </span>
            <span v-else class="text-muted">–</span>
          </template>
        </Column>
        <Column header="Potongan" class="col-potongan">
          <template #body="{ data }">
            <span v-if="data.level_persen_potongan != null" class="text-potongan">
              {{ data.level_persen_potongan }}%
            </span>
            <span v-else class="text-muted">–</span>
          </template>
        </Column>
        <Column header="Jemaah" class="col-jemaah">
          <template #body="{ data }">
            <span class="text-jemaah">{{ data.jumlah_jemaah ?? 0 }}</span>
          </template>
        </Column>
        <Column header="Status" class="col-status">
          <template #body="{ data }">
            <span
              v-if="data.status_kode === 'disetujui'"
              class="badge badge-mitra"
            >
              Mitra
            </span>
            <span v-else-if="data.status_kode === 'pending'" class="badge badge-pending">
              {{ data.nama_status || 'Pending' }}
            </span>
            <span v-else-if="data.status_kode === 'ditolak'" class="badge badge-ditolak">
              {{ data.nama_status || 'Ditolak' }}
            </span>
            <span v-else class="badge badge-default">
              {{ data.nama_status || data.status_kode || '–' }}
            </span>
          </template>
        </Column>
        <Column header="Aksi" class="col-aksi">
          <template #body="{ data }">
            <button
              type="button"
              class="btn btn-sm btn-outline-primary"
              @click="verifikasi(data)"
            >
              <i class="fas fa-eye"></i>
              Detail
            </button>
          </template>
        </Column>
      </DataTable>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import { useApi } from '@/api/useApi'

const api = useApi()
const router = useRouter()

const loading = ref(false)
const searchQuery = ref('')
const filterStatus = ref('')
const dataSource = ref<any[]>([])

function formatDate(val: string) {
  if (!val) return '-'
  try {
    const d = new Date(val)
    return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
  } catch {
    return val
  }
}

const filteredList = computed(() => {
  let result = dataSource.value
  const q = (searchQuery.value || '').trim().toLowerCase()
  if (q) {
    result = result.filter(
      (m: any) =>
        (m.nama_lengkap && m.nama_lengkap.toLowerCase().includes(q)) ||
        (m.email && m.email.toLowerCase().includes(q)) ||
        (m.nik && String(m.nik).toLowerCase().includes(q)),
    )
  }
  return result.map((m: any, i: number) => ({ ...m, no: i + 1 }))
})

function verifikasi(data: any) {
  router.push({
    name: 'verifikasi-mitra',
    params: { id: data.id_mitra },
    query: { nama: data.nama_lengkap },
  })
}

async function fetchData() {
  loading.value = true
  try {
    const params = new URLSearchParams()
    if (filterStatus.value) params.set('status', filterStatus.value)
    const response = await api.get(
      `/sistem-admin/mitra/get-list-semua-mitra?${params.toString()}`,
    )
    const arr = response?.data ?? []
    arr.forEach((element: any, i: number) => {
      element.no = i + 1
    })
    dataSource.value = arr
  } catch (error) {
    console.error('Error fetching data mitra:', error)
    dataSource.value = []
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchData()
})
</script>

<style scoped>
.data-mitra-page {
  max-width: 1400px;
  margin: 0 auto;
  padding: 1.5rem;
}

.page-header {
  margin-bottom: 1.5rem;
}

.page-header-inner {
  padding-bottom: 1rem;
  border-bottom: 1px solid #e5e7eb;
}

.page-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: #111827;
  margin: 0 0 0.25rem 0;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.page-title-icon {
  color: #007b6f;
  font-size: 1.35rem;
}

.page-subtitle {
  color: #6b7280;
  font-size: 0.9375rem;
  margin: 0 0 0.75rem 0;
}

.page-stats {
  margin-top: 0.5rem;
}

.stats-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.25rem 0.75rem;
  background: #f0fdf4;
  color: #007b6f;
  font-size: 0.875rem;
  font-weight: 500;
  border-radius: 9999px;
}

.toolbar {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1.25rem;
  padding: 1rem;
  background: #f9fafb;
  border-radius: 0.75rem;
  border: 1px solid #e5e7eb;
}

.toolbar-search {
  flex: 1;
  min-width: 200px;
  position: relative;
}

.toolbar-search-icon {
  position: absolute;
  left: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  color: #9ca3af;
  font-size: 0.875rem;
}

.toolbar-input {
  width: 100%;
  padding: 0.5rem 0.75rem 0.5rem 2.25rem;
  font-size: 0.875rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  background: #fff;
  outline: none;
}

.toolbar-input:focus {
  border-color: #007b6f;
  box-shadow: 0 0 0 2px rgba(0, 123, 111, 0.2);
}

.toolbar-select {
  min-width: 160px;
  padding: 0.5rem 0.75rem;
  font-size: 0.875rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  background: #fff;
  outline: none;
}

.toolbar-select:focus {
  border-color: #007b6f;
}

.btn {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
  font-weight: 500;
  border-radius: 0.5rem;
  cursor: pointer;
  border: none;
  outline: none;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-primary {
  background: #007b6f;
  color: #fff;
}

.btn-primary:hover:not(:disabled) {
  background: #00665c;
}

.btn-outline {
  background: #fff;
  color: #374151;
  border: 1px solid #d1d5db;
}

.btn-outline:hover:not(:disabled) {
  background: #f9fafb;
  border-color: #9ca3af;
}

.btn-sm {
  padding: 0.375rem 0.75rem;
  font-size: 0.8125rem;
}

.btn-outline-primary {
  background: #fff;
  color: #007b6f;
  border: 1px solid #007b6f;
}

.btn-outline-primary:hover {
  background: #f0fdf4;
}

.table-wrap {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 0.75rem;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.loading-wrap {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem 1rem;
}

.loading-spinner {
  font-size: 2rem;
  color: #007b6f;
  margin-bottom: 0.75rem;
}

.loading-text {
  color: #6b7280;
  font-size: 0.875rem;
  margin: 0;
}

.empty-state {
  text-align: center;
  padding: 3rem 1rem;
}

.empty-icon {
  font-size: 3rem;
  color: #d1d5db;
  margin-bottom: 1rem;
  display: block;
}

.empty-title {
  font-size: 1rem;
  font-weight: 500;
  color: #374151;
  margin: 0 0 0.25rem 0;
}

.empty-desc {
  font-size: 0.875rem;
  color: #6b7280;
  margin: 0;
}

.text-muted {
  color: #9ca3af;
  font-size: 0.875rem;
}

.text-potongan {
  font-weight: 600;
  color: #007b6f;
}

.text-jemaah {
  font-weight: 600;
  color: #0d9488;
}

.badge {
  display: inline-flex;
  align-items: center;
  padding: 0.25rem 0.625rem;
  font-size: 0.75rem;
  font-weight: 500;
  border-radius: 0.375rem;
}

.badge-level {
  background: #ccfbf1;
  color: #0d9488;
}

.badge-mitra {
  background: #d1fae5;
  color: #059669;
}

.badge-pending {
  background: #fef3c7;
  color: #b45309;
}

.badge-ditolak {
  background: #fee2e2;
  color: #dc2626;
}

.badge-default {
  background: #f3f4f6;
  color: #4b5563;
}

/* PrimeVue DataTable overrides */
.data-mitra-page :deep(.data-mitra-table) {
  font-size: 0.875rem;
}

.data-mitra-page :deep(.p-datatable .p-datatable-thead > tr > th) {
  background: #f8fafc;
  color: #475569;
  font-weight: 600;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.025em;
  padding: 0.75rem 1rem;
  border-color: #e2e8f0;
}

.data-mitra-page :deep(.p-datatable .p-datatable-tbody > tr > td) {
  padding: 0.75rem 1rem;
  border-color: #f1f5f9;
  vertical-align: middle;
}

.data-mitra-page :deep(.p-datatable .p-datatable-tbody > tr:hover) {
  background: #f8fafc;
}

.data-mitra-page :deep(.p-paginator) {
  background: #f9fafb;
  border-color: #e5e7eb;
  padding: 0.5rem 1rem;
}

.data-mitra-page :deep(.p-paginator .p-paginator-current) {
  color: #6b7280;
  font-size: 0.8125rem;
}
</style>
