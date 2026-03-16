<template>
  <div class="p-4 md:p-6">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
      <h1 class="text-xl font-medium mb-2 text-gray-900">Master LA Umrah</h1>
      <p class="text-gray-600 text-sm mb-4">
        Kelola semua kebutuhan paket kostumisasi umrah: <strong>Layanan</strong>, <strong>Hotel</strong>, dan <strong>Penerbangan</strong> yang tampil di halaman mitra/pemesanan/isi-paket-kostumisasi.
      </p>

      <!-- Tabs -->
      <div class="flex border-b border-gray-200 mb-4">
        <button
          type="button"
          :class="['px-4 py-2 text-sm font-medium border-b-2 transition-colors', activeTab === 'layanan' ? 'border-[#007b6f] text-[#007b6f]' : 'border-transparent text-gray-600 hover:text-gray-900']"
          @click="activeTab = 'layanan'; fetchData()"
        >
          Layanan
        </button>
        <button
          type="button"
          :class="['px-4 py-2 text-sm font-medium border-b-2 transition-colors', activeTab === 'hotel' ? 'border-[#007b6f] text-[#007b6f]' : 'border-transparent text-gray-600 hover:text-gray-900']"
          @click="activeTab = 'hotel'; fetchHotels(); fetchMasterHotels()"
        >
          Hotel
        </button>
        <button
          type="button"
          :class="['px-4 py-2 text-sm font-medium border-b-2 transition-colors', activeTab === 'penerbangan' ? 'border-[#007b6f] text-[#007b6f]' : 'border-transparent text-gray-600 hover:text-gray-900']"
          @click="activeTab = 'penerbangan'; fetchMaskapai(); fetchMasterMaskapai()"
        >
          Penerbangan
        </button>
      </div>

      <!-- Tab: Layanan -->
      <template v-if="activeTab === 'layanan'">
        <div class="flex flex-wrap gap-3 mb-4">
          <button type="button" @click="openModalLayanan('tambahan')" class="inline-flex items-center gap-2 px-4 py-2 bg-[#007b6f] text-white rounded-lg hover:bg-[#00665c] text-sm font-medium">
            <i class="fas fa-plus"></i> Tambah Layanan
          </button>
          <select v-model="filterJenis" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#007b6f]">
            <option value="">Semua jenis</option>
            <option value="wajib">Wajib</option>
            <option value="tambahan">Tambahan</option>
          </select>
          <button type="button" @click="fetchData()" class="px-4 py-2 border border-[#007b6f] text-[#007b6f] rounded-lg hover:bg-[#007b6f] hover:text-white text-sm font-medium">
            <i class="fas fa-sync-alt mr-1"></i> Refresh
          </button>
        </div>
        <div v-if="errorMessage" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm">{{ errorMessage }}</div>
        <div v-else-if="loading" class="flex justify-center py-12"><i class="fas fa-spinner fa-spin text-3xl text-[#007b6f]"></i></div>
        <div v-else-if="filteredItems.length === 0" class="mb-4 p-4 bg-gray-50 border border-gray-200 rounded-lg text-gray-700 text-sm">
          <p class="font-medium">Belum ada data layanan.</p>
          <p class="mt-1">Klik <strong>Tambah Layanan</strong> untuk menambah (Visa, Handling, Mutawwif, Transportasi, dll).</p>
        </div>
        <div v-else class="overflow-x-auto">
          <DataTable :value="filteredItems" class="datatable datatable-fixed-height" :paginator="filteredItems.length > 10" :rows="10" :rowsPerPageOptions="[5, 10, 20, 50]" paginatorTemplate="CurrentPageReport FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown" currentPageReportTemplate="Menampilkan {first} - {last} dari {totalRecords} data">
            <Column field="no" header="No" style="width: 60px" />
            <Column field="nama" header="Nama" />
            <Column field="jenis" header="Jenis" style="width: 100px">
              <template #body="slotProps">
                <span :class="slotProps.data.jenis === 'wajib' ? 'text-amber-600' : 'text-teal-600'">{{ slotProps.data.jenis === 'wajib' ? 'Wajib' : 'Tambahan' }}</span>
              </template>
            </Column>
            <Column header="Harga" style="width: 140px">
              <template #body="slotProps">{{ formatRupiah(slotProps.data.harga) }} <span class="text-gray-500 text-xs">{{ slotProps.data.satuan || '/pax' }}</span></template>
            </Column>
            <Column field="urutan" header="Urutan" style="width: 80px" />
            <Column header="Status" style="width: 90px">
              <template #body="slotProps">
                <span :class="slotProps.data.is_active ? 'text-green-600' : 'text-gray-500'">{{ slotProps.data.is_active ? 'Aktif' : 'Nonaktif' }}</span>
              </template>
            </Column>
            <Column header="#" headerClass="text-center" bodyClass="text-center" :style="{ width: '120px' }">
              <template #body="slotProps">
                <button type="button" class="text-[#007b6f] hover:underline mr-2" @click="openModalEditLayanan(slotProps.data)">Edit</button>
                <button type="button" class="text-red-600 hover:underline" @click="deleteLayanan(slotProps.data)">Hapus</button>
              </template>
            </Column>
          </DataTable>
        </div>
      </template>

      <!-- Tab: Hotel -->
      <template v-if="activeTab === 'hotel'">
        <div class="flex flex-wrap gap-3 mb-4">
          <button type="button" @click="openModalHotel()" class="inline-flex items-center gap-2 px-4 py-2 bg-[#007b6f] text-white rounded-lg hover:bg-[#00665c] text-sm font-medium">
            <i class="fas fa-plus"></i> Tambah Hotel
          </button>
          <button type="button" @click="fetchHotels()" class="px-4 py-2 border border-[#007b6f] text-[#007b6f] rounded-lg hover:bg-[#007b6f] hover:text-white text-sm font-medium">
            <i class="fas fa-sync-alt mr-1"></i> Refresh
          </button>
        </div>
        <p class="text-sm text-gray-500 mb-3">
          Daftar ini bersumber dari <RouterLink to="/Paket/Master-Hotel" class="text-[#007b6f] hover:underline font-medium">Master Hotel</RouterLink>. Hotel yang ditambahkan di sini akan tampil di <strong>isi paket kostumisasi</strong> (pilihan Mekkah/Madinah) dan di <strong>Komponen Hotel</strong> (mitra pesan hotel tanpa paket).
        </p>
        <div v-if="loadingHotel" class="flex justify-center py-12"><i class="fas fa-spinner fa-spin text-3xl text-[#007b6f]"></i></div>
        <div v-else-if="laUmrahHotels.length === 0" class="p-4 bg-gray-50 border border-gray-200 rounded-lg text-gray-700 text-sm">
          Belum ada hotel. Klik <strong>Tambah Hotel</strong> dan pilih dari <RouterLink to="/Paket/Master-Hotel" class="text-[#007b6f] hover:underline">Master Hotel</RouterLink>.
        </div>
        <div v-else class="overflow-x-auto">
          <DataTable :value="laUmrahHotelsWithNo" class="datatable" :paginator="laUmrahHotelsWithNo.length > 10" :rows="10">
            <Column field="no" header="No" style="width: 60px" />
            <Column header="Nama Hotel">
              <template #body="slotProps">{{ slotProps.data.hotel?.nama_hotel ?? '-' }}</template>
            </Column>
            <Column header="Kota" style="width: 100px">
              <template #body="slotProps">{{ slotProps.data.hotel?.kota?.nama_kota ?? '-' }}</template>
            </Column>
            <Column header="Jarak" style="width: 100px">
              <template #body="slotProps">{{ slotProps.data.hotel?.jarak_ke_masjid ?? '-' }}</template>
            </Column>
            <Column field="urutan" header="Urutan" style="width: 80px" />
            <Column header="#" bodyClass="text-center" :style="{ width: '80px' }">
              <template #body="slotProps">
                <button type="button" class="text-red-600 hover:underline text-sm" @click="removeHotel(slotProps.data)">Hapus</button>
              </template>
            </Column>
          </DataTable>
        </div>
      </template>

      <!-- Tab: Penerbangan -->
      <template v-if="activeTab === 'penerbangan'">
        <div class="flex flex-wrap gap-3 mb-4">
          <button type="button" @click="openModalMaskapai()" class="inline-flex items-center gap-2 px-4 py-2 bg-[#007b6f] text-white rounded-lg hover:bg-[#00665c] text-sm font-medium">
            <i class="fas fa-plus"></i> Tambah Maskapai
          </button>
          <button type="button" @click="fetchMaskapai()" class="px-4 py-2 border border-[#007b6f] text-[#007b6f] rounded-lg hover:bg-[#007b6f] hover:text-white text-sm font-medium">
            <i class="fas fa-sync-alt mr-1"></i> Refresh
          </button>
        </div>
        <p class="text-sm text-gray-500 mb-3">Maskapai yang dipilih akan tampil di halaman isi paket kostumisasi mitra (penerbangan keberangkatan & kepulangan).</p>
        <div v-if="loadingMaskapai" class="flex justify-center py-12"><i class="fas fa-spinner fa-spin text-3xl text-[#007b6f]"></i></div>
        <div v-else-if="laUmrahMaskapai.length === 0" class="p-4 bg-gray-50 border border-gray-200 rounded-lg text-gray-700 text-sm">
          Belum ada maskapai. Klik <strong>Tambah Maskapai</strong> dan pilih dari Master Maskapai.
        </div>
        <div v-else class="overflow-x-auto">
          <DataTable :value="laUmrahMaskapaiWithNo" class="datatable" :paginator="laUmrahMaskapaiWithNo.length > 10" :rows="10">
            <Column field="no" header="No" style="width: 60px" />
            <Column header="Nama Maskapai">
              <template #body="slotProps">{{ slotProps.data.maskapai?.nama_maskapai ?? '-' }}</template>
            </Column>
            <Column header="Kode" style="width: 80px">
              <template #body="slotProps">{{ slotProps.data.maskapai?.kode_iata ?? '-' }}</template>
            </Column>
            <Column header="Harga/Orang" style="width: 130px">
              <template #body="slotProps">{{ slotProps.data.maskapai?.harga_tiket_per_orang != null ? formatRupiah(slotProps.data.maskapai.harga_tiket_per_orang) : '-' }}</template>
            </Column>
            <Column field="urutan" header="Urutan" style="width: 80px" />
            <Column header="#" bodyClass="text-center" :style="{ width: '80px' }">
              <template #body="slotProps">
                <button type="button" class="text-red-600 hover:underline text-sm" @click="removeMaskapai(slotProps.data)">Hapus</button>
              </template>
            </Column>
          </DataTable>
        </div>
      </template>
    </div>

    <!-- Modal Tambah/Edit Layanan -->
    <Modal :open="modalLayananOpen" @close="modalLayananOpen = false" :title="modalLayananMode === 'create' ? 'Tambah Layanan' : 'Edit Layanan'" size="medium" :show-action="true" @action="saveLayanan()" action-label="Simpan">
      <template #content>
        <div class="space-y-4">
          <Field label="Nama Layanan" id="layanan_nama" required>
            <input v-model="layananForm.nama" type="text" maxlength="255" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]" placeholder="Contoh: Visa, Handling Saudi" />
          </Field>
          <div class="grid grid-cols-2 gap-4">
            <Field label="Harga (Rp)" id="layanan_harga" required>
              <input v-model.number="layananForm.harga" type="number" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]" />
            </Field>
            <Field label="Satuan" id="layanan_satuan">
              <select v-model="layananForm.satuan" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]">
                <option value="/pax">/pax</option>
                <option value="/orang">/orang</option>
                <option value="/pack">/pack</option>
                <option value="/hari">/hari</option>
              </select>
            </Field>
          </div>
          <Field label="Jenis" id="layanan_jenis">
            <select v-model="layananForm.jenis" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]">
              <option value="wajib">Pelayanan Wajib</option>
              <option value="tambahan">Layanan Tambahan</option>
            </select>
          </Field>
          <Field label="Urutan" id="layanan_urutan">
            <input v-model.number="layananForm.urutan" type="number" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]" />
          </Field>
          <Field label="Deskripsi (opsional)" id="layanan_deskripsi">
            <textarea v-model="layananForm.deskripsi" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]" rows="2" placeholder="Deskripsi singkat"></textarea>
          </Field>
          <div v-if="modalLayananMode === 'edit'" class="flex items-center gap-2">
            <input v-model="layananForm.is_active" type="checkbox" id="layanan_is_active" class="rounded border-gray-300 text-[#007b6f] focus:ring-[#007b6f]" />
            <label for="layanan_is_active" class="text-sm text-gray-700">Aktif</label>
          </div>
        </div>
      </template>
    </Modal>

    <!-- Modal Tambah Hotel (pilih dari Master Hotel) -->
    <Modal :open="modalHotelOpen" @close="modalHotelOpen = false" title="Tambah Hotel ke LA Umrah" size="medium" :show-action="true" @action="addHotel()" action-label="Tambah">
      <template #content>
        <Field label="Pilih Hotel dari Master Hotel" id="select_hotel">
          <select v-model="selectedHotelId" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]">
            <option value="">-- Pilih Hotel --</option>
            <option v-for="h in masterHotelsFiltered" :key="h.id" :value="h.id">{{ h.nama_hotel }} ({{ h.kota?.nama_kota ?? '-' }})</option>
          </select>
        </Field>
      </template>
    </Modal>

    <!-- Modal Tambah Maskapai (pilih dari Master Maskapai) -->
    <Modal :open="modalMaskapaiOpen" @close="modalMaskapaiOpen = false" title="Tambah Maskapai ke LA Umrah" size="medium" :show-action="true" @action="addMaskapai()" action-label="Tambah">
      <template #content>
        <Field label="Pilih Maskapai dari Master Maskapai" id="select_maskapai">
          <select v-model="selectedMaskapaiId" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]">
            <option value="">-- Pilih Maskapai --</option>
            <option v-for="m in masterMaskapaiList" :key="m.id" :value="m.id">{{ m.nama_maskapai }} ({{ m.kode_iata || '-' }})</option>
          </select>
        </Field>
      </template>
    </Modal>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import { useApi } from '@/api/useApi'
import Field from '@/components/base/form/Field.vue'
import Modal from '@/components/base/modal/Modal.vue'

const route = useRoute()
const api = useApi()
const activeTab = ref<'layanan' | 'hotel' | 'penerbangan'>('layanan')
const loading = ref(false)
const errorMessage = ref('')
const layananItems = ref<any[]>([])
const filterJenis = ref('')

const laUmrahHotels = ref<any[]>([])
const loadingHotel = ref(false)
const masterHotelsList = ref<any[]>([])
const modalHotelOpen = ref(false)
const selectedHotelId = ref<string | number>('')

const laUmrahMaskapai = ref<any[]>([])
const loadingMaskapai = ref(false)
const masterMaskapaiList = ref<any[]>([])
const modalMaskapaiOpen = ref(false)
const selectedMaskapaiId = ref<string | number>('')

const modalLayananOpen = ref(false)
const modalLayananMode = ref<'create' | 'edit'>('create')
const layananForm = ref({
  id: null as number | null,
  nama: '',
  harga: 0 as number,
  satuan: '/pax',
  jenis: 'tambahan' as string,
  urutan: 0 as number,
  deskripsi: '',
  is_active: true as boolean,
})

const formatRupiah = (val: any) => {
  const n = Number(val)
  if (Number.isNaN(n)) return '-'
  return `Rp ${n.toLocaleString('id-ID')}`
}

const filteredItems = computed(() => {
  let list = [...layananItems.value].sort((a: any, b: any) => (a.urutan ?? 0) - (b.urutan ?? 0))
  if (filterJenis.value) list = list.filter((i: any) => i.jenis === filterJenis.value)
  list.forEach((el: any, i: number) => { el.no = i + 1 })
  return list
})

const laUmrahHotelsWithNo = computed(() => {
  return laUmrahHotels.value.map((r: any, i: number) => ({ ...r, no: i + 1 }))
})

const laUmrahMaskapaiWithNo = computed(() => {
  return laUmrahMaskapai.value.map((r: any, i: number) => ({ ...r, no: i + 1 }))
})

const masterHotelsFiltered = computed(() => {
  const ids = new Set(laUmrahHotels.value.map((r: any) => r.hotel_id))
  return masterHotelsList.value.filter((h: any) => !ids.has(h.id))
})

function fetchData() {
  errorMessage.value = ''
  loading.value = true
  api.get('/sistem-admin/layanan-paket-request/get-list').then((res: any) => {
    layananItems.value = res?.data?.items ?? []
  }).catch((err: any) => {
    layananItems.value = []
    errorMessage.value = err?.response?.data?.message || err?.message || 'Gagal memuat data.'
  }).finally(() => { loading.value = false })
}

async function fetchHotels() {
  loadingHotel.value = true
  try {
    const res = await api.get('/sistem-admin/la-umrah-options/hotels') as { data?: any[] }
    laUmrahHotels.value = res?.data ?? []
  } catch {
    laUmrahHotels.value = []
  } finally {
    loadingHotel.value = false
  }
}

async function fetchMasterHotels() {
  try {
    const res = await api.get('/sistem-admin/la-umrah-options/master-hotels') as { data?: any[] }
    masterHotelsList.value = res?.data ?? []
  } catch {
    masterHotelsList.value = []
  }
}

async function fetchMaskapai() {
  loadingMaskapai.value = true
  try {
    const res = await api.get('/sistem-admin/la-umrah-options/maskapai') as { data?: any[] }
    laUmrahMaskapai.value = res?.data ?? []
  } catch {
    laUmrahMaskapai.value = []
  } finally {
    loadingMaskapai.value = false
  }
}

async function fetchMasterMaskapai() {
  try {
    const res = await api.get('/sistem-admin/la-umrah-options/master-maskapai') as { data?: any[] }
    masterMaskapaiList.value = res?.data ?? []
  } catch {
    masterMaskapaiList.value = []
  }
}

function openModalHotel() {
  selectedHotelId.value = ''
  fetchMasterHotels()
  modalHotelOpen.value = true
}

async function addHotel() {
  if (!selectedHotelId.value) {
    alert('Pilih hotel terlebih dahulu')
    return
  }
  try {
    await api.post('/sistem-admin/la-umrah-options/hotels', { hotel_id: selectedHotelId.value })
    modalHotelOpen.value = false
    await fetchHotels()
  } catch (err: any) {
    alert(err?.response?.data?.message ?? 'Gagal menambah hotel')
  }
}

async function removeHotel(row: any) {
  if (!confirm('Hapus hotel ini dari daftar LA Umrah?')) return
  try {
    await api.delete(`/sistem-admin/la-umrah-options/hotels/${row.id}`)
    await fetchHotels()
  } catch (err: any) {
    alert(err?.response?.data?.message ?? 'Gagal menghapus')
  }
}

function openModalMaskapai() {
  selectedMaskapaiId.value = ''
  fetchMasterMaskapai()
  modalMaskapaiOpen.value = true
}

async function addMaskapai() {
  if (!selectedMaskapaiId.value) {
    alert('Pilih maskapai terlebih dahulu')
    return
  }
  try {
    await api.post('/sistem-admin/la-umrah-options/maskapai', { maskapai_id: selectedMaskapaiId.value })
    modalMaskapaiOpen.value = false
    await fetchMaskapai()
  } catch (err: any) {
    alert(err?.response?.data?.message ?? 'Gagal menambah maskapai')
  }
}

async function removeMaskapai(row: any) {
  if (!confirm('Hapus maskapai ini dari daftar LA Umrah?')) return
  try {
    await api.delete(`/sistem-admin/la-umrah-options/maskapai/${row.id}`)
    await fetchMaskapai()
  } catch (err: any) {
    alert(err?.response?.data?.message ?? 'Gagal menghapus')
  }
}

const openModalLayanan = (jenis: string) => {
  modalLayananMode.value = 'create'
  layananForm.value = {
    id: null,
    nama: '',
    harga: 0,
    satuan: '/pax',
    jenis,
    urutan: layananItems.value.filter((i: any) => i.jenis === jenis).length,
    deskripsi: '',
    is_active: true,
  }
  modalLayananOpen.value = true
}

const openModalEditLayanan = (row: any) => {
  modalLayananMode.value = 'edit'
  layananForm.value = {
    id: row.id,
    nama: row.nama ?? '',
    harga: Number(row.harga) ?? 0,
    satuan: row.satuan ?? '/pax',
    jenis: row.jenis ?? 'tambahan',
    urutan: Number(row.urutan) ?? 0,
    deskripsi: row.deskripsi ?? '',
    is_active: row.is_active !== false,
  }
  modalLayananOpen.value = true
}

const saveLayanan = async () => {
  const nama = (layananForm.value.nama ?? '').trim()
  if (!nama) {
    alert('Nama layanan tidak boleh kosong')
    return
  }
  try {
    if (modalLayananMode.value === 'create') {
      await api.post('/sistem-admin/layanan-paket-request/create-layanan', {
        nama,
        harga: layananForm.value.harga,
        satuan: layananForm.value.satuan,
        jenis: layananForm.value.jenis,
        urutan: layananForm.value.urutan,
        deskripsi: layananForm.value.deskripsi?.trim() || null,
      })
    } else {
      await api.put(`/sistem-admin/layanan-paket-request/update-layanan/${layananForm.value.id}`, {
        nama,
        harga: layananForm.value.harga,
        satuan: layananForm.value.satuan,
        jenis: layananForm.value.jenis,
        urutan: layananForm.value.urutan,
        deskripsi: layananForm.value.deskripsi?.trim() || null,
        is_active: layananForm.value.is_active,
      })
    }
    modalLayananOpen.value = false
    await fetchData()
  } catch (err: any) {
    alert(err?.response?.data?.message ?? 'Gagal menyimpan layanan')
  }
}

const deleteLayanan = async (row: any) => {
  if (!confirm(`Hapus layanan "${row.nama}"?`)) return
  try {
    await api.delete(`/sistem-admin/layanan-paket-request/delete-layanan/${row.id}`)
    await fetchData()
  } catch (err: any) {
    alert(err?.response?.data?.message ?? 'Gagal menghapus')
  }
}

onMounted(() => {
  const tab = route.query.tab as string
  if (tab === 'hotel') {
    activeTab.value = 'hotel'
    fetchHotels()
    fetchMasterHotels()
  } else if (tab === 'penerbangan') {
    activeTab.value = 'penerbangan'
    fetchMaskapai()
    fetchMasterMaskapai()
  } else {
    fetchData()
  }
})
</script>

<style scoped>
.datatable-fixed-height :deep(.p-datatable-table-container) {
  min-height: 320px;
  overflow: visible !important;
  max-height: none !important;
}
</style>
