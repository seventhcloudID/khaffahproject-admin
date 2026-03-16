<template>
  <div class="p-4 md:p-6">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
      <div class="flex items-center gap-4 mb-6">
        <RouterLink
          :to="'/Paket/Master-Badal-Umrah'"
          class="text-gray-600 hover:text-[#007b6f] flex items-center gap-1"
        >
          <i class="fas fa-arrow-left"></i>
          <span>Kembali</span>
        </RouterLink>
        <h1 class="text-xl font-medium text-gray-900">
          {{ isEdit ? 'Edit Layanan Badal Umrah' : 'Tambah Layanan Badal Umrah' }}
        </h1>
      </div>

      <form @submit.prevent="saveData" class="space-y-6 max-w-2xl">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label for="nama_layanan" class="block text-sm font-medium text-gray-700 mb-1">Nama Layanan <span class="text-red-500">*</span></label>
            <input
              v-model="formData.nama_layanan"
              type="text"
              id="nama_layanan"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
              placeholder="Contoh: Badal Umrah"
            />
          </div>

          <div>
            <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
            <input
              v-model="formData.slug"
              type="text"
              id="slug"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
              placeholder="badal-umrah"
            />
            <p class="text-xs text-gray-500 mt-1">Kosongkan agar diisi otomatis dari nama layanan.</p>
          </div>
        </div>

        <div>
          <label for="deskripsi" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
          <textarea
            v-model="formData.deskripsi"
            id="deskripsi"
            rows="2"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
            placeholder="Pelaksanaan ibadah Umrah atas nama orang lain (sesuai syariat)"
          />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label for="harga" class="block text-sm font-medium text-gray-700 mb-1">Harga per pax (IDR) <span class="text-red-500">*</span></label>
            <input
              v-model.number="formData.harga"
              type="number"
              id="harga"
              min="0"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
              placeholder="350000"
            />
            <p class="text-xs text-gray-500 mt-1">Harga ini tampil di form pemesanan mitra (/mitra/komponen/badal_umrah).</p>
          </div>

          <div>
            <label for="urutan" class="block text-sm font-medium text-gray-700 mb-1">Urutan</label>
            <input
              v-model.number="formData.urutan"
              type="number"
              id="urutan"
              min="0"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
              placeholder="0"
            />
          </div>
        </div>

        <div>
          <label class="flex items-center gap-2">
            <input v-model="formData.is_active" type="checkbox" class="rounded border-gray-300" />
            <span>Aktif</span>
          </label>
          <p class="text-xs text-gray-500 mt-1">Hanya layanan aktif yang tampil di halaman /mitra/komponen/badal_umrah.</p>
        </div>

        <div class="flex gap-3 pt-4 border-t">
          <button
            type="submit"
            :disabled="saving"
            class="px-4 py-2 bg-[#007b6f] text-white rounded-md hover:bg-[#00665c] disabled:opacity-50"
          >
            {{ saving ? 'Menyimpan...' : 'Simpan' }}
          </button>
          <RouterLink
            :to="'/Paket/Master-Badal-Umrah'"
            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
          >
            Batal
          </RouterLink>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useApi } from '@/api/useApi'

const route = useRoute()
const router = useRouter()
const api = useApi()

const idParam = computed(() => {
  const id = route.params.id
  if (id === undefined || id === '' || id === 'new') return null
  const n = parseInt(String(id), 10)
  return isNaN(n) ? null : n
})

const isEdit = computed(() => idParam.value != null)

const formData = ref({
  id: null as number | null,
  nama_layanan: '',
  slug: '',
  deskripsi: '',
  harga: 0 as number,
  urutan: 0 as number,
  is_active: true,
})

const saving = ref(false)

async function loadDetail() {
  if (!idParam.value) return
  try {
    const res = await api.get(`/sistem-admin/master-badal-umrah/${idParam.value}`)
    const d = res.data?.data ?? res.data
    if (d) {
      formData.value = {
        id: d.id,
        nama_layanan: d.nama_layanan ?? '',
        slug: d.slug ?? '',
        deskripsi: d.deskripsi ?? '',
        harga: Number(d.harga) || 0,
        urutan: Number(d.urutan) || 0,
        is_active: d.is_active !== false,
      }
    }
  } catch (err) {
    console.error('Gagal load detail Badal Umrah', err)
  }
}

async function saveData() {
  if (!formData.value.nama_layanan.trim()) {
    alert('Nama layanan tidak boleh kosong')
    return
  }
  const harga = Number(formData.value.harga)
  if (isNaN(harga) || harga < 0) {
    alert('Harga harus angka dan tidak negatif')
    return
  }
  saving.value = true
  try {
    const payload = {
      nama_layanan: formData.value.nama_layanan,
      slug: formData.value.slug || undefined,
      deskripsi: formData.value.deskripsi || undefined,
      harga,
      urutan: Number(formData.value.urutan) || 0,
      is_active: formData.value.is_active,
    }
    if (!isEdit.value) {
      await api.post('/sistem-admin/master-badal-umrah', payload)
    } else {
      await api.put(`/sistem-admin/master-badal-umrah/${formData.value.id}`, payload)
    }
    router.push('/Paket/Master-Badal-Umrah')
  } catch (err: any) {
    const msg = err.response?.data?.message || err.message || 'Gagal menyimpan'
    alert(msg)
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  if (idParam.value) loadDetail()
})

watch(idParam, (newId) => {
  if (newId) loadDetail()
  else {
    formData.value = {
      id: null,
      nama_layanan: '',
      slug: '',
      deskripsi: '',
      harga: 0,
      urutan: 0,
      is_active: true,
    }
  }
})
</script>
