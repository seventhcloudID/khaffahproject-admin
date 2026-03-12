<template>
  <div class="p-4 md:p-6">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
      <div class="flex items-center gap-4 mb-6">
        <RouterLink
          :to="'/Paket/Master-Level-Mitra'"
          class="text-gray-600 hover:text-[#007b6f] flex items-center gap-1"
        >
          <i class="fas fa-arrow-left"></i>
          <span>Kembali</span>
        </RouterLink>
        <h1 class="text-xl font-medium text-gray-900">
          {{ isEdit ? 'Edit Level Mitra' : 'Tambah Level Mitra' }}
        </h1>
      </div>

      <form @submit.prevent="saveData" class="space-y-6 max-w-2xl">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label for="nama_level" class="block text-sm font-medium text-gray-700 mb-1">Nama Level <span class="text-red-500">*</span></label>
            <input
              v-model="formData.nama_level"
              type="text"
              id="nama_level"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
              placeholder="Contoh: Silver, Gold"
            />
          </div>

          <div>
            <label for="persen_potongan" class="block text-sm font-medium text-gray-700 mb-1">Persen Potongan (%) <span class="text-red-500">*</span></label>
            <input
              v-model.number="formData.persen_potongan"
              type="number"
              id="persen_potongan"
              min="0"
              max="100"
              step="0.5"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
              placeholder="0"
            />
            <p class="text-xs text-gray-500 mt-1">Potongan harga paket umrah yang dilihat mitra (0–100%).</p>
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
            :to="'/Paket/Master-Level-Mitra'"
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
  nama_level: '',
  persen_potongan: 0 as number,
  urutan: 0 as number,
  is_active: true,
})

const saving = ref(false)

async function loadDetail() {
  if (!idParam.value) return
  try {
    const res = await api.get(`/sistem-admin/master-level-mitra/${idParam.value}`)
    const d = res.data?.data ?? res.data
    if (d) {
      formData.value = {
        id: d.id,
        nama_level: d.nama_level ?? '',
        persen_potongan: Number(d.persen_potongan) || 0,
        urutan: Number(d.urutan) || 0,
        is_active: d.is_active !== false,
      }
    }
  } catch (err) {
    console.error('Gagal load detail level mitra', err)
  }
}

async function saveData() {
  if (!formData.value.nama_level.trim()) {
    alert('Nama level tidak boleh kosong')
    return
  }
  const persen = Number(formData.value.persen_potongan)
  if (isNaN(persen) || persen < 0 || persen > 100) {
    alert('Persen potongan harus antara 0 dan 100')
    return
  }
  saving.value = true
  try {
    const payload = {
      nama_level: formData.value.nama_level,
      persen_potongan: persen,
      urutan: Number(formData.value.urutan) || 0,
      is_active: formData.value.is_active,
    }
    if (!isEdit.value) {
      await api.post('/sistem-admin/master-level-mitra', payload)
    } else {
      await api.put(`/sistem-admin/master-level-mitra/${formData.value.id}`, payload)
    }
    router.push('/Paket/Master-Level-Mitra')
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
      nama_level: '',
      persen_potongan: 0,
      urutan: 0,
      is_active: true,
    }
  }
})
</script>
