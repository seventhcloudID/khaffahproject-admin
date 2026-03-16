<template>
  <div class="columns">
    <Card class="column is-12" elevated radius="smooth" padding="md">
      <div class="flex items-center gap-4 mb-6">
        <RouterLink to="/Paket/Master-Kepulangan" class="text-gray-600 hover:text-[#007b6f] flex items-center gap-1">
          <i class="fas fa-arrow-left"></i>
          <span>Kembali</span>
        </RouterLink>
        <h1 class="text-xl font-medium">{{ isEdit ? 'Edit Kepulangan' : 'Tambah Kepulangan' }}</h1>
      </div>

      <form @submit.prevent="saveData" class="space-y-6 max-w-2xl">
        <Field label="Kode (IATA)" id="kode" required>
          <input
            v-model="formData.kode"
            type="text"
            maxlength="10"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
            placeholder="Contoh: JED, MED, TIF"
          />
        </Field>
        <Field label="Nama Bandara / Kota" id="nama" required>
          <input
            v-model="formData.nama"
            type="text"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
            placeholder="Contoh: King Abdulaziz (Jeddah)"
          />
        </Field>
        <Field label="Urutan" id="urutan">
          <input
            v-model.number="formData.urutan"
            type="number"
            min="0"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
          />
        </Field>
        <Field label="Status" id="is_active">
          <label class="flex items-center gap-2">
            <input v-model="formData.is_active" type="checkbox" class="rounded border-gray-300" />
            <span>Aktif</span>
          </label>
        </Field>
        <div class="flex gap-3 pt-4 border-t">
          <button
            type="submit"
            :disabled="saving"
            class="px-4 py-2 bg-[#007b6f] text-white rounded-md hover:bg-[#00665c] disabled:opacity-50"
          >
            {{ saving ? 'Menyimpan...' : 'Simpan' }}
          </button>
          <RouterLink to="/Paket/Master-Kepulangan" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
            Batal
          </RouterLink>
        </div>
      </form>
    </Card>
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
  kode: '',
  nama: '',
  urutan: 0,
  is_active: true,
})

const saving = ref(false)

async function loadDetail() {
  if (!idParam.value) return
  try {
    const res = await api.get(`/sistem-admin/master-kepulangan/${idParam.value}`)
    const d = res.data?.data ?? res.data
    if (d) {
      formData.value = {
        id: d.id,
        kode: d.kode ?? '',
        nama: d.nama ?? '',
        urutan: d.urutan ?? 0,
        is_active: d.is_active !== false,
      }
    }
  } catch (err) {
    console.error('Gagal load detail', err)
  }
}

async function saveData() {
  if (!formData.value.kode.trim() || !formData.value.nama.trim()) {
    alert('Kode dan nama wajib diisi')
    return
  }
  saving.value = true
  try {
    const payload = {
      kode: formData.value.kode.trim(),
      nama: formData.value.nama.trim(),
      urutan: formData.value.urutan ?? 0,
      is_active: formData.value.is_active,
    }
    if (!isEdit.value) {
      await api.post('/sistem-admin/master-kepulangan', payload)
    } else {
      await api.put(`/sistem-admin/master-kepulangan/${formData.value.id}`, payload)
    }
    router.push('/Paket/Master-Kepulangan')
  } catch (err: any) {
    alert(err.response?.data?.message || err.message || 'Gagal menyimpan')
  } finally {
    saving.value = false
  }
}

onMounted(() => { if (idParam.value) loadDetail() })
watch(idParam, (newId) => { if (newId) loadDetail(); else formData.value = { id: null, kode: '', nama: '', urutan: 0, is_active: true } })
</script>
