<template>
  <div class="w-full max-w-full overflow-x-hidden">
    <Card class="w-full max-w-full px-4 sm:px-6 py-4 sm:py-6 box-border" elevated radius="smooth" padding="md">
      <h1 class="text-lg sm:text-xl font-medium mb-3 sm:mb-4">Paket Request</h1>
      <p class="text-xs sm:text-sm text-gray-500 mb-4">
        Kelola <strong>Pelayanan Wajib</strong> (visa, transportasi, perlengkapan — otomatis ikut) dan
        <strong>Layanan Tambahan</strong> (user centang sesuai kebutuhan) untuk halaman
        <a :href="productRequestUrl" target="_blank" rel="noopener" class="text-teal-600 underline">product-request</a>.
      </p>

      <!-- Master Layanan: Pelayanan Wajib & Layanan Tambahan -->
      <div class="space-y-6 sm:space-y-8">
        <div v-if="loadingLayanan" class="flex justify-center items-center py-12">
          <i class="fas fa-spinner fa-spin text-3xl text-teal-600"></i>
        </div>
        <template v-else>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
          <div class="border rounded-lg p-4 bg-gray-50">
            <div class="flex justify-between items-start">
              <div>
                <h3 class="font-semibold text-gray-900">Section Pelayanan Wajib</h3>
                <p class="text-sm text-gray-600 mt-1">{{ sectionWajib?.judul ?? '-' }}</p>
                <p class="text-xs text-gray-500 mt-0.5">{{ sectionWajib?.deskripsi ?? '-' }}</p>
              </div>
              <button
                type="button"
                class="text-sm text-teal-600 hover:underline"
                @click="openModalSection(sectionWajib)"
              >
                Edit judul & deskripsi
              </button>
            </div>
          </div>
          <div class="border rounded-lg p-4 bg-gray-50">
            <div class="flex justify-between items-start">
              <div>
                <h3 class="font-semibold text-gray-900">Section Layanan Tambahan</h3>
                <p class="text-sm text-gray-600 mt-1">{{ sectionTambahan?.judul ?? '-' }}</p>
                <p class="text-xs text-gray-500 mt-0.5">{{ sectionTambahan?.deskripsi ?? '-' }}</p>
              </div>
              <button
                type="button"
                class="text-sm text-teal-600 hover:underline"
                @click="openModalSection(sectionTambahan)"
              >
                Edit judul & deskripsi
              </button>
            </div>
          </div>
        </div>

        <div>
          <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2 mb-3">
            <h3 class="font-semibold text-sm sm:text-base">Pelayanan Wajib (ditampilkan tetap, tanpa checkbox)</h3>
            <Button color="success" darkOutlined icon="fas fa-plus" class="w-full sm:w-auto" @click="openModalLayanan('wajib')">
              Tambah Layanan Wajib
            </Button>
          </div>
          <div class="overflow-x-auto -mx-4 sm:mx-0 rounded-lg border border-gray-200">
          <DataTable :value="layananWajib" class="datatable datatable-mobile" :paginator="layananWajib.length > 10" :rows="10">
            <Column field="urutan" header="No" style="width: 60px" />
            <Column field="nama" header="Nama" />
            <Column header="Harga">
              <template #body="slotProps">
                {{ formatRupiah(slotProps.data.harga) }} <span class="text-gray-500">{{ slotProps.data.satuan }}</span>
              </template>
            </Column>
            <Column header="#" :style="{ width: '100px' }">
              <template #body="slotProps">
                <button type="button" class="text-teal-600 hover:underline mr-2" @click="openModalEditLayanan(slotProps.data)">Edit</button>
                <button type="button" class="text-red-600 hover:underline" @click="deleteLayanan(slotProps.data)">Hapus</button>
              </template>
            </Column>
          </DataTable>
          </div>
        </div>

        <div>
          <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2 mb-3">
            <h3 class="font-semibold text-sm sm:text-base">Layanan Tambahan (opsional, dengan checkbox)</h3>
            <Button color="success" darkOutlined icon="fas fa-plus" class="w-full sm:w-auto" @click="openModalLayanan('tambahan')">
              Tambah Layanan Tambahan
            </Button>
          </div>
          <div class="overflow-x-auto -mx-4 sm:mx-0 rounded-lg border border-gray-200">
          <DataTable :value="layananTambahan" class="datatable datatable-mobile" :paginator="layananTambahan.length > 10" :rows="10">
            <Column field="urutan" header="No" style="width: 60px" />
            <Column field="nama" header="Nama" />
            <Column header="Harga">
              <template #body="slotProps">
                {{ formatRupiah(slotProps.data.harga) }} <span class="text-gray-500">{{ slotProps.data.satuan }}</span>
              </template>
            </Column>
            <Column header="#" :style="{ width: '100px' }">
              <template #body="slotProps">
                <button type="button" class="text-teal-600 hover:underline mr-2" @click="openModalEditLayanan(slotProps.data)">Edit</button>
                <button type="button" class="text-red-600 hover:underline" @click="deleteLayanan(slotProps.data)">Hapus</button>
              </template>
            </Column>
          </DataTable>
          </div>
        </div>

        <!-- Master Tujuan Tambahan (Negara/Kota Liburan - Umrah Plus Liburan) -->
        <div>
          <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2 mb-3">
            <h3 class="font-semibold text-sm sm:text-base">Tujuan Tambahan (Negara/Kota Liburan)</h3>
            <Button color="success" darkOutlined icon="fas fa-plus" class="w-full sm:w-auto" @click="openModalTujuan()">
              Tambah Tujuan Tambahan
            </Button>
          </div>
          <p class="text-xs sm:text-sm text-gray-500 mb-3">
            Daftar ini dipakai di form <strong>Umrah Plus Liburan</strong> sebagai opsi dropdown &quot;Pilih Negara / Kota Tujuan&quot;.
          </p>
          <div v-if="loadingTujuan" class="flex justify-center items-center py-8">
            <i class="fas fa-spinner fa-spin text-2xl text-teal-600"></i>
          </div>
          <div v-else class="overflow-x-auto -mx-4 sm:mx-0 rounded-lg border border-gray-200">
          <DataTable :value="tujuanTambahanList" class="datatable datatable-mobile" :paginator="tujuanTambahanList.length > 10" :rows="10">
            <Column field="urutan" header="No" style="width: 60px" />
            <Column field="nama" header="Nama Negara/Kota" />
            <Column header="Status">
              <template #body="slotProps">
                <span :class="slotProps.data.is_active ? 'text-green-600' : 'text-gray-400'">
                  {{ slotProps.data.is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
              </template>
            </Column>
            <Column header="#" :style="{ width: '120px' }">
              <template #body="slotProps">
                <button type="button" class="text-teal-600 hover:underline mr-2" @click="openModalEditTujuan(slotProps.data)">Edit</button>
                <button type="button" class="text-red-600 hover:underline" @click="deleteTujuan(slotProps.data)">Hapus</button>
              </template>
            </Column>
          </DataTable>
          </div>
        </div>
        </template>
      </div>
    </Card>

    <Modal
      :open="modalSectionOpen"
      @close="modalSectionOpen = false"
      title="Edit Judul & Deskripsi Section"
      size="medium"
      :show-action="true"
      @action="saveSection()"
      action-label="Simpan"
    >
      <template #content>
        <div class="space-y-4">
          <Field label="Judul" id="section_judul" required>
            <input
              v-model="sectionForm.judul"
              type="text"
              maxlength="255"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500"
            />
          </Field>
          <Field label="Deskripsi" id="section_deskripsi">
            <textarea
              v-model="sectionForm.deskripsi"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500"
              rows="3"
            ></textarea>
          </Field>
        </div>
      </template>
    </Modal>

    <Modal
      :open="modalLayananOpen"
      @close="modalLayananOpen = false"
      :title="modalLayananMode === 'create' ? 'Tambah Layanan' : 'Edit Layanan'"
      size="medium"
      :show-action="true"
      @action="saveLayanan()"
      action-label="Simpan"
    >
      <template #content>
        <div class="space-y-4">
          <Field label="Nama Layanan" id="layanan_nama" required>
            <input
              v-model="layananForm.nama"
              type="text"
              maxlength="255"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500"
              placeholder="Contoh: Visa dan Pasport Umrah"
            />
          </Field>
          <div class="grid grid-cols-2 gap-4">
            <Field label="Harga (Rp)" id="layanan_harga" required>
              <input
                v-model.number="layananForm.harga"
                type="number"
                min="0"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500"
              />
            </Field>
            <Field label="Satuan" id="layanan_satuan">
              <select
                v-model="layananForm.satuan"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500"
              >
                <option value="/pax">/pax</option>
                <option value="/orang">/orang</option>
                <option value="/hari">/hari</option>
              </select>
            </Field>
          </div>
          <Field label="Jenis" id="layanan_jenis">
            <select
              v-model="layananForm.jenis"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500"
            >
              <option value="wajib">Pelayanan Wajib</option>
              <option value="tambahan">Layanan Tambahan</option>
            </select>
          </Field>
          <Field label="Deskripsi (opsional)" id="layanan_deskripsi">
            <textarea
              v-model="layananForm.deskripsi"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500"
              rows="2"
            ></textarea>
          </Field>
        </div>
      </template>
    </Modal>

    <Modal
      :open="modalTujuanOpen"
      @close="modalTujuanOpen = false"
      :title="modalTujuanMode === 'create' ? 'Tambah Tujuan Tambahan' : 'Edit Tujuan Tambahan'"
      size="medium"
      :show-action="true"
      @action="saveTujuan()"
      action-label="Simpan"
    >
      <template #content>
        <div class="space-y-4">
          <Field label="Nama Negara/Kota" id="tujuan_nama" required>
            <input
              v-model="tujuanForm.nama"
              type="text"
              maxlength="100"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500"
              placeholder="Contoh: Dubai, Turki, Mesir"
            />
          </Field>
          <Field label="Urutan" id="tujuan_urutan">
            <input
              v-model.number="tujuanForm.urutan"
              type="number"
              min="0"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500"
            />
          </Field>
          <Field label="Aktif" id="tujuan_is_active">
            <label class="flex items-center gap-2">
              <input v-model="tujuanForm.is_active" type="checkbox" class="rounded border-gray-300 text-teal-600 focus:ring-amber-500" />
              <span>Tampilkan di dropdown form Umrah Plus Liburan</span>
            </label>
          </Field>
        </div>
      </template>
    </Modal>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import { useApi } from '@/api/useApi'
import Card from '@/components/base/card/Card.vue'
import Button from '@/components/base/button/Button.vue'
import Field from '@/components/base/form/Field.vue'
import Modal from '@/components/base/modal/Modal.vue'

const api = useApi()

const layananSections = ref<any[]>([])
const layananItems = ref<any[]>([])
const sectionWajib = computed(() => layananSections.value.find((s: any) => s.jenis === 'wajib'))
const sectionTambahan = computed(() => layananSections.value.find((s: any) => s.jenis === 'tambahan'))
const layananWajib = computed(() =>
  layananItems.value.filter((i: any) => i.jenis === 'wajib').sort((a: any, b: any) => a.urutan - b.urutan)
)
const layananTambahan = computed(() =>
  layananItems.value.filter((i: any) => i.jenis === 'tambahan').sort((a: any, b: any) => a.urutan - b.urutan)
)

const modalSectionOpen = ref(false)
const sectionForm = ref({ id: null as number | null, judul: '', deskripsi: '' })

const modalLayananOpen = ref(false)
const modalLayananMode = ref<'create' | 'edit'>('create')
const layananForm = ref({
  id: null as number | null,
  nama: '',
  harga: 0 as number,
  satuan: '/pax',
  jenis: 'wajib' as string,
  deskripsi: '',
})

const loadingLayanan = ref(false)
const layananLoadedOnce = ref(false)

const tujuanTambahanList = ref<any[]>([])
const loadingTujuan = ref(false)
const modalTujuanOpen = ref(false)
const modalTujuanMode = ref<'create' | 'edit'>('create')
const tujuanForm = ref({
  id: null as number | null,
  nama: '',
  urutan: 0 as number,
  is_active: true as boolean,
})

/** Preload / refresh data Master Layanan. Dipanggil on mount (parallel dengan paket) dan setelah CRUD. */
const fetchLayanan = async () => {
  loadingLayanan.value = true
  try {
    const res = await api.get('/sistem-admin/layanan-paket-request/get-list')
    const data = (res as any)?.data
    layananSections.value = data?.sections ?? []
    layananItems.value = data?.items ?? []
    layananLoadedOnce.value = true
  } catch (err) {
    console.error('Error fetch layanan:', err)
    layananSections.value = []
    layananItems.value = []
  } finally {
    loadingLayanan.value = false
  }
}

const openModalSection = (section: any) => {
  if (!section) return
  sectionForm.value = { id: section.id, judul: section.judul ?? '', deskripsi: section.deskripsi ?? '' }
  modalSectionOpen.value = true
}

const saveSection = async () => {
  if (!sectionForm.value.judul?.trim()) {
    alert('Judul tidak boleh kosong')
    return
  }
  try {
    await api.put(`/sistem-admin/layanan-paket-request/update-section/${sectionForm.value.id}`, {
      judul: sectionForm.value.judul.trim(),
      deskripsi: sectionForm.value.deskripsi?.trim() ?? '',
    })
    modalSectionOpen.value = false
    fetchLayanan()
  } catch (err: any) {
    alert(err?.response?.data?.message ?? 'Gagal menyimpan section')
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
    deskripsi: '',
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
    jenis: row.jenis ?? 'wajib',
    deskripsi: row.deskripsi ?? '',
  }
  modalLayananOpen.value = true
}

const saveLayanan = async () => {
  const nama = layananForm.value.nama?.trim() ?? ''
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
        deskripsi: layananForm.value.deskripsi?.trim() || null,
      })
    } else {
      await api.put(`/sistem-admin/layanan-paket-request/update-layanan/${layananForm.value.id}`, {
        nama,
        harga: layananForm.value.harga,
        satuan: layananForm.value.satuan,
        jenis: layananForm.value.jenis,
        deskripsi: layananForm.value.deskripsi?.trim() || null,
      })
    }
    modalLayananOpen.value = false
    fetchLayanan()
  } catch (err: any) {
    alert(err?.response?.data?.message ?? 'Gagal menyimpan layanan')
  }
}

const deleteLayanan = async (row: any) => {
  if (!confirm(`Hapus layanan "${row.nama}"?`)) return
  try {
    await api.delete(`/sistem-admin/layanan-paket-request/delete-layanan/${row.id}`)
    fetchLayanan()
  } catch (err: any) {
    alert(err?.response?.data?.message ?? 'Gagal menghapus')
  }
}

const fetchTujuanTambahan = async () => {
  loadingTujuan.value = true
  try {
    const res = await api.get('/sistem-admin/tujuan-tambahan/get-list')
    const data = (res as any)?.data
    tujuanTambahanList.value = Array.isArray(data) ? data : []
  } catch (err) {
    console.error('Error fetch tujuan tambahan:', err)
    tujuanTambahanList.value = []
  } finally {
    loadingTujuan.value = false
  }
}

const openModalTujuan = () => {
  modalTujuanMode.value = 'create'
  tujuanForm.value = { id: null, nama: '', urutan: 0, is_active: true }
  modalTujuanOpen.value = true
}

const openModalEditTujuan = (row: any) => {
  modalTujuanMode.value = 'edit'
  tujuanForm.value = {
    id: row.id,
    nama: row.nama ?? '',
    urutan: Number(row.urutan) ?? 0,
    is_active: row.is_active !== false,
  }
  modalTujuanOpen.value = true
}

const saveTujuan = async () => {
  const nama = tujuanForm.value.nama?.trim() ?? ''
  if (!nama) {
    alert('Nama negara/kota tidak boleh kosong')
    return
  }
  try {
    if (modalTujuanMode.value === 'create') {
      await api.post('/sistem-admin/tujuan-tambahan/create', {
        nama,
        urutan: tujuanForm.value.urutan,
        is_active: tujuanForm.value.is_active,
      })
    } else {
      await api.put(`/sistem-admin/tujuan-tambahan/update/${tujuanForm.value.id}`, {
        nama,
        urutan: tujuanForm.value.urutan,
        is_active: tujuanForm.value.is_active,
      })
    }
    modalTujuanOpen.value = false
    fetchTujuanTambahan()
  } catch (err: any) {
    alert(err?.response?.data?.message ?? 'Gagal menyimpan tujuan tambahan')
  }
}

const deleteTujuan = async (row: any) => {
  if (!confirm(`Hapus tujuan "${row.nama}"?`)) return
  try {
    await api.delete(`/sistem-admin/tujuan-tambahan/delete/${row.id}`)
    fetchTujuanTambahan()
  } catch (err: any) {
    alert(err?.response?.data?.message ?? 'Gagal menghapus')
  }
}

const productRequestUrl =
  (import.meta.env?.VITE_APP_URL || (typeof window !== 'undefined' ? window.location.origin : '')) + '/product-request'

const formatRupiah = (val: any) => {
  if (val == null || val === '') return '-'
  const n = Number(val)
  if (isNaN(n)) return '-'
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(n)
}

onMounted(() => {
  fetchLayanan()
  fetchTujuanTambahan()
})
</script>

<style scoped>
@media (max-width: 767px) {
  .datatable-mobile :deep(.p-datatable-wrapper) {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }
  .datatable-mobile :deep(.p-datatable-thead > tr > th),
  .datatable-mobile :deep(.p-datatable-tbody > tr > td) {
    padding: 0.5rem 0.375rem;
    font-size: 0.8125rem;
    white-space: nowrap;
  }
}
</style>
