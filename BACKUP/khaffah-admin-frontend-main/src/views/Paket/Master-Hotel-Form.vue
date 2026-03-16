<template>
  <div class="columns">
    <Card class="column is-12" elevated radius="smooth" padding="md">
      <div class="flex items-center gap-4 mb-6">
        <RouterLink
          :to="'/Paket/Master-Hotel'"
          class="text-gray-600 hover:text-[#007b6f] flex items-center gap-1"
        >
          <i class="fas fa-arrow-left"></i>
          <span>Kembali</span>
        </RouterLink>
        <h1 class="text-xl font-medium">
          {{ isEdit ? 'Edit Hotel' : 'Tambah Hotel' }}
        </h1>
      </div>

      <form @submit.prevent="saveData" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <Field label="Nama Hotel" id="nama_hotel" required>
            <input
              v-model="formData.nama_hotel"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
              placeholder="Nama hotel"
            />
          </Field>

          <Field label="Kota" id="kota_id" required>
            <select
              v-model="formData.kota_id"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
            >
              <option :value="null">Pilih kota</option>
              <option v-for="k in kotaOptions" :key="k.value" :value="k.value">{{ k.label }}</option>
            </select>
          </Field>

          <Field label="Bintang (0-5)" id="bintang">
            <input
              v-model.number="formData.bintang"
              type="number"
              min="0"
              max="5"
              step="0.5"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
              placeholder="Contoh: 4"
            />
          </Field>

          <Field label="Jarak ke Masjid" id="jarak_ke_masjid">
            <input
              v-model="formData.jarak_ke_masjid"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
              placeholder="Contoh: 500 m"
            />
          </Field>
        </div>

        <Field label="Alamat" id="alamat">
          <textarea
            v-model="formData.alamat"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
            placeholder="Alamat lengkap"
            rows="2"
          ></textarea>
        </Field>

        <Field label="Status" id="is_active">
          <label class="flex items-center gap-2">
            <input v-model="formData.is_active" type="checkbox" class="rounded border-gray-300" />
            <span>Aktif</span>
          </label>
        </Field>

        <!-- Tipe Kamar -->
        <div class="border-t pt-6">
          <div class="flex items-center justify-between mb-3">
            <label class="font-medium text-gray-700">Tipe Kamar</label>
            <button
              type="button"
              @click="addKamar()"
              class="text-sm text-[#007b6f] hover:underline flex items-center gap-1"
            >
              <i class="fas fa-plus"></i> Tambah tipe kamar
            </button>
          </div>
          <div v-if="formData.kamar.length === 0" class="text-gray-500 text-sm py-2">Belum ada tipe kamar.</div>
          <div v-else class="space-y-3">
            <div
              v-for="(row, idx) in formData.kamar"
              :key="idx"
              class="grid grid-cols-12 gap-2 items-end p-3 bg-gray-50 rounded-lg"
            >
              <div class="col-span-4">
                <label class="block text-xs text-gray-500 mb-0.5">Tipe</label>
                <select
                  v-model="row.tipe_kamar_id"
                  class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm"
                  @change="onTipeKamarChange(idx)"
                >
                  <option :value="null">Pilih tipe</option>
                  <option v-for="t in tipeKamarOptions" :key="t.value" :value="t.value">{{ t.label }}</option>
                </select>
              </div>
              <div class="col-span-2">
                <label class="block text-xs text-gray-500 mb-0.5">Jumlah orang</label>
                <input
                  v-model.number="row.kapasitas"
                  type="number"
                  min="1"
                  max="10"
                  class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm"
                />
              </div>
              <div class="col-span-3">
                <label class="block text-xs text-gray-500 mb-0.5">Harga per kamar (Rp)</label>
                <input
                  v-model.number="row.harga_per_malam"
                  type="number"
                  min="0"
                  step="1000"
                  class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm"
                  placeholder="0"
                />
              </div>
              <div class="col-span-2 flex justify-end">
                <button type="button" @click="removeKamar(idx)" class="text-red-500 hover:text-red-700 p-1">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Foto Hotel (hanya saat edit) -->
        <div v-if="formData.id" class="border-t pt-6">
          <div class="flex items-center justify-between mb-3">
            <label class="font-medium text-gray-700">Foto Hotel</label>
            <label class="cursor-pointer text-sm text-[#007b6f] hover:underline flex items-center gap-1">
              <input ref="fotoInputRef" type="file" accept="image/*" class="hidden" @change="onFileFoto" />
              <i class="fas fa-upload"></i> Upload foto
            </label>
          </div>
          <div v-if="formData.fotos.length === 0" class="text-gray-500 text-sm py-2">Belum ada foto.</div>
          <div v-else class="flex flex-wrap gap-3">
            <div
              v-for="(f, idx) in formData.fotos"
              :key="f.id || 'new-' + idx"
              class="relative group"
            >
              <img
                :src="getFotoDisplayUrl(f.url_foto, f.url_foto_display)"
                alt="Foto"
                class="w-24 h-24 object-cover rounded border border-gray-200"
                @error="($event as any).target.src = 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%22100%22%3E%3Crect fill=%22%23eee%22 width=%22100%22 height=%22100%22/%3E%3Ctext x=%2250%22 y=%2250%22 fill=%22%23999%22 text-anchor=%22middle%22 dy=%22.3em%22%3ENo img%3C/text%3E%3C/svg%3E'"
              />
              <button
                type="button"
                @click="removeFoto(idx)"
                class="absolute -top-1 -right-1 w-6 h-6 bg-red-500 text-white rounded-full text-xs opacity-0 group-hover:opacity-100 transition flex items-center justify-center"
              >
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>
        </div>
        <div v-else class="border-t pt-4 text-gray-500 text-sm">
          Simpan hotel terlebih dahulu, lalu buka halaman ini lagi untuk menambah foto.
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
            :to="'/Paket/Master-Hotel'"
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
  nama_hotel: '',
  kota_id: null as number | null,
  bintang: null as number | null,
  jarak_ke_masjid: '',
  alamat: '',
  is_active: true,
  fotos: [] as Array<{ id?: number; url_foto: string; urutan: number; url_foto_display?: string }>,
  kamar: [] as Array<{
    id?: number
    tipe_kamar_id: number | null
    nama_kamar?: string
    kapasitas: number
    harga_per_malam: number | null
  }>,
})

const kotaOptions = ref<{ value: number; label: string }[]>([])
const tipeKamarOptions = ref<{ value: number; label: string; kapasitas?: number }[]>([])
const fotoInputRef = ref<HTMLInputElement | null>(null)
const saving = ref(false)

/** URL gambar harus dari server backend (Laravel /storage), bukan dari origin frontend */
function getFotoDisplayUrl(url_foto: string, url_foto_display?: string): string {
  if (url_foto_display && (url_foto_display.startsWith('http://') || url_foto_display.startsWith('https://'))) {
    return url_foto_display
  }
  if (!url_foto || typeof url_foto !== 'string') return ''
  let baseUrl =
    import.meta.env.VITE_STORAGE_URL ||
    (typeof import.meta.env.VITE_API_BASE_URL === 'string'
      ? import.meta.env.VITE_API_BASE_URL.replace(/\/api\/?$/, '')
      : '') ||
    'http://localhost:8000'
  baseUrl = baseUrl.replace(/\/$/, '')
  const normalizedPath = url_foto.replace(/\\/g, '/').trim()
  const path = normalizedPath.startsWith('storage/') || normalizedPath.startsWith('/storage/')
    ? normalizedPath
    : `storage/${normalizedPath}`
  const cleanPath = path.startsWith('/') ? path : `/${path}`
  return `${baseUrl}${cleanPath}`
}

function addKamar() {
  formData.value.kamar.push({
    id: undefined,
    tipe_kamar_id: null,
    kapasitas: 2,
    harga_per_malam: null,
  })
}

function removeKamar(idx: number) {
  formData.value.kamar.splice(idx, 1)
}

function removeFoto(idx: number) {
  formData.value.fotos.splice(idx, 1)
}

function onTipeKamarChange(idx: number) {
  const row = formData.value.kamar[idx]
  if (row?.tipe_kamar_id) {
    const t = tipeKamarOptions.value.find((x) => x.value === row.tipe_kamar_id)
    if (t?.kapasitas) row.kapasitas = t.kapasitas
  }
}

async function onFileFoto(e: Event) {
  const target = e.target as HTMLInputElement
  const file = target.files?.[0]
  if (!file || !formData.value.id) return
  try {
    const fd = new FormData()
    fd.append('hotel_id', String(formData.value.id))
    fd.append('urutan', String((formData.value.fotos?.length ?? 0) + 1))
    fd.append('file', file)
    const baseURL = import.meta.env.VITE_API_BASE_URL || ''
    const token = (await import('@/stores/userSession')).useUserSession().token
    const res = await fetch(`${baseURL}/sistem-admin/master-hotel/upload-foto`, {
      method: 'POST',
      headers: { Accept: 'application/json', ...(token ? { Authorization: `Bearer ${token}` } : {}) },
      body: fd,
    })
    const json = await res.json()
    if (json.status && json.data) {
      formData.value.fotos.push({
        id: json.data.id,
        url_foto: json.data.url_foto,
        urutan: json.data.urutan,
        url_foto_display: json.data.url_foto_display,
      })
    } else {
      alert(json.message || 'Gagal upload foto')
    }
  } catch (err) {
    console.error(err)
    alert('Gagal upload foto')
  }
  target.value = ''
}

async function loadDetail() {
  if (!idParam.value) return
  try {
    const res = await api.get(`/sistem-admin/master-hotel/${idParam.value}`)
    const d = res.data?.data ?? res.data
    if (d) {
      formData.value.id = d.id
      formData.value.nama_hotel = d.nama_hotel ?? ''
      formData.value.kota_id = d.kota_id ?? d.kota?.id ?? null
      formData.value.bintang = d.bintang ?? null
      formData.value.jarak_ke_masjid = d.jarak_ke_masjid ?? ''
      formData.value.alamat = d.alamat ?? ''
      formData.value.is_active = d.is_active !== false
      formData.value.fotos = (d.foto || d.fotos || []).map((f: any, i: number) => ({
        id: f.id,
        url_foto: f.url_foto,
        urutan: f.urutan ?? i + 1,
        url_foto_display: f.url_foto_display,
      }))
      formData.value.kamar = (d.kamar || []).map((k: any) => ({
        id: k.id,
        tipe_kamar_id: k.tipe_kamar_id ?? k.tipeKamar?.id ?? null,
        nama_kamar: k.nama_kamar,
        kapasitas: k.kapasitas ?? 1,
        harga_per_malam: k.harga_per_malam ?? null,
      }))
    }
  } catch (err) {
    console.error('Gagal load detail hotel', err)
  }
}

async function fetchKota() {
  try {
    const res = await api.get('/sistem-admin/master-hotel/kota-options')
    const raw = res.data?.data ?? res.data ?? []
    kotaOptions.value = Array.isArray(raw) ? raw.map((x: any) => ({ value: x.value ?? x.id, label: x.label ?? x.nama_kota ?? '' })) : []
  } catch (e) {
    console.error('Gagal load kota', e)
    kotaOptions.value = []
  }
}

async function fetchTipeKamar() {
  try {
    const res = await api.get('/sistem-admin/master-hotel/tipe-kamar-options')
    const raw = res.data?.data ?? res.data ?? []
    tipeKamarOptions.value = Array.isArray(raw) ? raw.map((x: any) => ({ value: x.value ?? x.id, label: x.label ?? '', kapasitas: x.kapasitas })) : []
  } catch (e) {
    console.error('Gagal load tipe kamar', e)
    tipeKamarOptions.value = []
  }
}

async function saveData() {
  if (!formData.value.nama_hotel.trim()) {
    alert('Nama hotel tidak boleh kosong')
    return
  }
  if (!formData.value.kota_id) {
    alert('Kota harus dipilih')
    return
  }
  saving.value = true
  try {
    const payload: any = {
      nama_hotel: formData.value.nama_hotel,
      kota_id: formData.value.kota_id,
      bintang: formData.value.bintang ?? undefined,
      jarak_ke_masjid: formData.value.jarak_ke_masjid || undefined,
      alamat: formData.value.alamat || undefined,
      is_active: formData.value.is_active,
      kamar: formData.value.kamar
        .filter((k) => k.tipe_kamar_id)
        .map((k) => ({
          id: k.id,
          tipe_kamar_id: k.tipe_kamar_id,
          nama_kamar: k.nama_kamar,
          kapasitas: k.kapasitas,
          harga_per_malam: k.harga_per_malam ?? undefined,
        })),
    }
    if (!isEdit.value) {
      payload.fotos = []
      await api.post('/sistem-admin/master-hotel', payload)
      router.push('/Paket/Master-Hotel')
    } else {
      payload.fotos = formData.value.fotos.map((f, i) => ({
        id: f.id,
        url_foto: f.url_foto,
        urutan: f.urutan ?? i + 1,
      }))
      await api.put(`/sistem-admin/master-hotel/${formData.value.id}`, payload)
      router.push('/Paket/Master-Hotel')
    }
  } catch (err: any) {
    const msg = err.response?.data?.message || err.message || 'Gagal menyimpan'
    alert(msg)
  } finally {
    saving.value = false
  }
}

onMounted(async () => {
  await fetchKota()
  await fetchTipeKamar()
  if (idParam.value) {
    await loadDetail()
  } else {
    formData.value.kamar = []
    formData.value.fotos = []
  }
})

watch(idParam, (newId) => {
  if (newId) loadDetail()
  else {
    formData.value = {
      id: null,
      nama_hotel: '',
      kota_id: null,
      bintang: null,
      jarak_ke_masjid: '',
      alamat: '',
      is_active: true,
      fotos: [],
      kamar: [],
    }
  }
})
</script>
