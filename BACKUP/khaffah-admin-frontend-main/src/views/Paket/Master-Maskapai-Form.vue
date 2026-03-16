<template>
  <div class="columns">
    <Card class="column is-12" elevated radius="smooth" padding="md">
      <div class="flex items-center gap-4 mb-6">
        <RouterLink
          :to="'/Paket/Master-Maskapai'"
          class="text-gray-600 hover:text-[#007b6f] flex items-center gap-1"
        >
          <i class="fas fa-arrow-left"></i>
          <span>Kembali</span>
        </RouterLink>
        <h1 class="text-xl font-medium">
          {{ isEdit ? 'Edit Maskapai' : 'Tambah Maskapai' }}
        </h1>
      </div>

      <form @submit.prevent="saveData" class="space-y-6 max-w-2xl">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <Field label="Nama Maskapai" id="nama_maskapai" required>
            <input
              v-model="formData.nama_maskapai"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
              placeholder="Contoh: Garuda Indonesia"
            />
          </Field>

          <Field label="Kode IATA" id="kode_iata">
            <input
              v-model="formData.kode_iata"
              type="text"
              maxlength="10"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
              placeholder="Contoh: GA"
            />
          </Field>

          <Field label="Negara Asal" id="negara_asal">
            <input
              v-model="formData.negara_asal"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
              placeholder="Contoh: Indonesia"
            />
          </Field>

          <Field label="URL Logo (opsional)" id="logo_url">
            <input
              v-model="formData.logo_url"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
              placeholder="https://..."
            />
          </Field>
        </div>

        <div class="border-t pt-6 mt-6">
          <h2 class="text-base font-medium text-gray-800 mb-4">Jadwal & Tiket (opsional)</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <Field label="Jam Keberangkatan" id="jam_keberangkatan">
              <input
                v-model="formData.jam_keberangkatan"
                type="time"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
              />
            </Field>
            <Field label="Jam Sampai" id="jam_sampai">
              <input
                v-model="formData.jam_sampai"
                type="time"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
              />
            </Field>
            <Field label="Kelas Penerbangan" id="kelas_penerbangan_id">
              <select
                v-model="formData.kelas_penerbangan_id"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
              >
                <option :value="null">-- Pilih kelas --</option>
                <option
                  v-for="opt in kelasPenerbanganOptions"
                  :key="opt.value"
                  :value="opt.value"
                >
                  {{ opt.label }}
                </option>
              </select>
            </Field>
            <Field label="Harga Tiket per Orang (Rp)" id="harga_tiket_per_orang">
              <input
                v-model.number="formData.harga_tiket_per_orang"
                type="number"
                min="0"
                step="1000"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
                placeholder="0"
              />
            </Field>
          </div>
        </div>

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
          <RouterLink
            :to="'/Paket/Master-Maskapai'"
            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
          >
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
  nama_maskapai: '',
  kode_iata: '',
  negara_asal: '',
  logo_url: '',
  jam_keberangkatan: '' as string,
  jam_sampai: '' as string,
  kelas_penerbangan_id: null as number | null,
  harga_tiket_per_orang: null as number | null,
  is_active: true,
})

const kelasPenerbanganOptions = ref<{ value: number; label: string }[]>([])

const saving = ref(false)

async function loadDetail() {
  if (!idParam.value) return
  try {
    const res = await api.get(`/sistem-admin/master-maskapai/${idParam.value}`)
    const d = res.data?.data ?? res.data
    if (d) {
      formData.value = {
        id: d.id,
        nama_maskapai: d.nama_maskapai ?? '',
        kode_iata: d.kode_iata ?? '',
        negara_asal: d.negara_asal ?? '',
        logo_url: d.logo_url ?? '',
        jam_keberangkatan: timeToInput(d.jam_keberangkatan) ?? '',
        jam_sampai: timeToInput(d.jam_sampai) ?? '',
        kelas_penerbangan_id: d.kelas_penerbangan_id ?? null,
        harga_tiket_per_orang: d.harga_tiket_per_orang != null ? Number(d.harga_tiket_per_orang) : null,
        is_active: d.is_active !== false,
      }
    }
  } catch (err) {
    console.error('Gagal load detail maskapai', err)
  }
}

async function saveData() {
  if (!formData.value.nama_maskapai.trim()) {
    alert('Nama maskapai tidak boleh kosong')
    return
  }
  saving.value = true
  try {
    const payload: Record<string, unknown> = {
      nama_maskapai: formData.value.nama_maskapai,
      kode_iata: formData.value.kode_iata || undefined,
      negara_asal: formData.value.negara_asal || undefined,
      logo_url: formData.value.logo_url || undefined,
      is_active: formData.value.is_active,
    }
    payload.jam_keberangkatan = formData.value.jam_keberangkatan || null
    payload.jam_sampai = formData.value.jam_sampai || null
    payload.kelas_penerbangan_id = formData.value.kelas_penerbangan_id ?? null
    const raw = formData.value.harga_tiket_per_orang
    payload.harga_tiket_per_orang = raw != null && String(raw) !== '' ? Number(raw) : null
    if (!isEdit.value) {
      await api.post('/sistem-admin/master-maskapai', payload)
    } else {
      await api.put(`/sistem-admin/master-maskapai/${formData.value.id}`, payload)
    }
    router.push('/Paket/Master-Maskapai')
  } catch (err: any) {
    const msg = err.response?.data?.message || err.message || 'Gagal menyimpan'
    alert(msg)
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  loadKelasPenerbanganOptions()
  if (idParam.value) loadDetail()
})

watch(idParam, (newId) => {
  if (newId) loadDetail()
  else {
    formData.value = {
      id: null,
      nama_maskapai: '',
      kode_iata: '',
      negara_asal: '',
      logo_url: '',
      jam_keberangkatan: '',
      jam_sampai: '',
      kelas_penerbangan_id: null,
      harga_tiket_per_orang: null,
      is_active: true,
    }
  }
})

function timeToInput(v: string | null | undefined): string {
  if (v == null || v === '') return ''
  const s = String(v).trim()
  if (s.length >= 5) return s.substring(0, 5)
  return s
}

async function loadKelasPenerbanganOptions() {
  try {
    const res = await api.get('/sistem-admin/master-maskapai/kelas-penerbangan-options')
    const data = res.data?.data ?? res.data ?? []
    kelasPenerbanganOptions.value = Array.isArray(data) ? data : []
  } catch {
    kelasPenerbanganOptions.value = []
  }
}
</script>
