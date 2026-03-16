<template>
  <div class="p-4 md:p-6">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
      <div class="flex items-center gap-4 mb-6">
        <RouterLink
          :to="'/Paket/Master-Rekening'"
          class="text-gray-600 hover:text-[#007b6f] flex items-center gap-1"
        >
          <i class="fas fa-arrow-left"></i>
          <span>Kembali</span>
        </RouterLink>
        <h1 class="text-xl font-medium text-gray-900">
          {{ isEdit ? 'Edit Rekening' : 'Tambah Rekening' }}
        </h1>
      </div>

      <form @submit.prevent="saveData" class="space-y-6 max-w-2xl">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label for="bank_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Bank <span class="text-red-500">*</span></label>
            <input
              v-model="formData.bank_name"
              type="text"
              id="bank_name"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
              placeholder="Contoh: BCA, Mandiri, BNI"
            />
          </div>

          <div>
            <label for="account_number" class="block text-sm font-medium text-gray-700 mb-1">Nomor Rekening <span class="text-red-500">*</span></label>
            <input
              v-model="formData.account_number"
              type="text"
              id="account_number"
              maxlength="30"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
              placeholder="Hanya angka"
            />
            <p class="text-xs text-gray-500 mt-1">Nomor rekening hanya boleh berisi angka.</p>
          </div>

          <div class="md:col-span-2">
            <label for="account_holder_name" class="block text-sm font-medium text-gray-700 mb-1">Atas Nama <span class="text-red-500">*</span></label>
            <input
              v-model="formData.account_holder_name"
              type="text"
              id="account_holder_name"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
              placeholder="Nama pemilik rekening"
            />
          </div>

          <div class="md:col-span-2">
            <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
            <input
              v-model="formData.keterangan"
              type="text"
              id="keterangan"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
              placeholder="Opsional, misal: Rekening utama"
            />
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
            :to="'/Paket/Master-Rekening'"
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
  bank_name: '',
  account_number: '',
  account_holder_name: '',
  keterangan: '',
  urutan: 0 as number,
  is_active: true,
})

const saving = ref(false)

async function loadDetail() {
  if (!idParam.value) return
  try {
    const res = await api.get(`/sistem-admin/master-rekening/${idParam.value}`)
    const d = res.data?.data ?? res.data
    if (d) {
      formData.value = {
        id: d.id,
        bank_name: d.bank_name ?? '',
        account_number: d.account_number ?? '',
        account_holder_name: d.account_holder_name ?? '',
        keterangan: d.keterangan ?? '',
        urutan: Number(d.urutan) || 0,
        is_active: d.is_active !== false,
      }
    }
  } catch (err) {
    console.error('Gagal load detail rekening', err)
  }
}

function validate(): string | null {
  if (!formData.value.bank_name.trim()) return 'Nama bank tidak boleh kosong'
  if (!formData.value.account_number.trim()) return 'Nomor rekening tidak boleh kosong'
  if (!/^[0-9]+$/.test(formData.value.account_number)) return 'Nomor rekening hanya boleh berisi angka'
  if (!formData.value.account_holder_name.trim()) return 'Atas nama tidak boleh kosong'
  return null
}

async function saveData() {
  const err = validate()
  if (err) {
    alert(err)
    return
  }
  saving.value = true
  try {
    const payload = {
      bank_name: formData.value.bank_name.trim(),
      account_number: formData.value.account_number.trim(),
      account_holder_name: formData.value.account_holder_name.trim(),
      keterangan: formData.value.keterangan?.trim() || null,
      urutan: Number(formData.value.urutan) || 0,
      is_active: formData.value.is_active,
    }
    if (!isEdit.value) {
      await api.post('/sistem-admin/master-rekening', payload)
    } else {
      await api.put(`/sistem-admin/master-rekening/${formData.value.id}`, payload)
    }
    router.push('/Paket/Master-Rekening')
  } catch (err: any) {
    const msg = err.response?.data?.message || err.message || 'Gagal menyimpan'
    const errors = err.response?.data?.errors
    if (errors && typeof errors === 'object') {
      const first = Object.values(errors).flat()
      alert(Array.isArray(first) ? first[0] : msg)
    } else {
      alert(msg)
    }
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
      bank_name: '',
      account_number: '',
      account_holder_name: '',
      keterangan: '',
      urutan: 0,
      is_active: true,
    }
  }
})
</script>
