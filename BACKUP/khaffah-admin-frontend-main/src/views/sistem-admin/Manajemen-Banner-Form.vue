<template>
  <div class="p-4 md:p-6">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
      <div class="flex items-center gap-4 mb-6">
        <RouterLink
          to="/sistem-admin/Manajemen-Banner"
          class="text-gray-600 hover:text-[#007b6f] flex items-center gap-1"
        >
          <i class="fas fa-arrow-left"></i>
          <span>Kembali</span>
        </RouterLink>
        <h1 class="text-xl font-medium text-gray-900">
          {{ isEdit ? 'Edit Banner' : 'Tambah Banner' }}
        </h1>
      </div>

      <form @submit.prevent="saveData" class="space-y-6 max-w-2xl">
        <div>
          <label for="judul" class="block text-sm font-medium text-gray-700 mb-1">Judul / Teks di atas banner <span class="text-red-500">*</span></label>
          <input
            v-model="formData.judul"
            type="text"
            id="judul"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
            placeholder="Contoh: Promo Umrah Plus Liburan"
          />
        </div>

        <div>
          <label for="teks" class="block text-sm font-medium text-gray-700 mb-1">Teks tambahan (opsional)</label>
          <input
            v-model="formData.teks"
            type="text"
            id="teks"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
            placeholder="Subteks atau deskripsi singkat"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Gambar banner</label>
          <div class="flex flex-wrap items-start gap-4">
            <div class="flex-shrink-0">
              <img
                v-if="previewUrl"
                :src="imgError && previewUrlFallback ? previewUrlFallback : previewUrl"
                alt="Preview"
                class="h-32 w-48 object-cover rounded-lg border border-gray-200"
                @error="onPreviewImgError"
                @load="onPreviewImgLoad"
              />
              <div
                v-else
                class="h-32 w-48 rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center text-gray-400 text-sm"
              >
                Belum ada gambar
              </div>
            </div>
            <div class="space-y-2">
              <input
                ref="fileInputRef"
                type="file"
                accept="image/*"
                class="hidden"
                @change="onFileSelect"
              />
              <button
                type="button"
                @click="fileInputRef?.click()"
                class="px-3 py-2 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50"
              >
                <i class="fas fa-upload mr-1"></i>
                Pilih gambar
              </button>
              <p v-if="uploading" class="text-sm text-amber-600">Mengunggah...</p>
              <p v-else class="text-xs text-gray-500">JPG, PNG. Maks 5MB. Gambar akan ditampilkan di banner.</p>
            </div>
          </div>
        </div>

        <div>
          <label for="lokasi" class="block text-sm font-medium text-gray-700 mb-1">Lokasi <span class="text-red-500">*</span></label>
          <select
            v-model="formData.lokasi"
            id="lokasi"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
          >
            <option v-for="opt in lokasiOptions" :key="opt.value" :value="opt.value">
              {{ opt.label }}
            </option>
          </select>
          <p class="text-xs text-gray-500 mt-1">Pilih section halaman tempat banner ditampilkan (mis. home_edutrip = section edutrip di homepage).</p>
        </div>

        <div>
          <label for="urutan" class="block text-sm font-medium text-gray-700 mb-1">Urutan</label>
          <input
            v-model.number="formData.urutan"
            type="number"
            id="urutan"
            min="0"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
            placeholder="1"
          />
        </div>

        <div>
          <label for="link" class="block text-sm font-medium text-gray-700 mb-1">Link (opsional)</label>
          <input
            v-model="formData.link"
            type="text"
            id="link"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
            placeholder="https://..."
          />
        </div>

        <div>
          <label class="flex items-center gap-2">
            <input v-model="formData.is_aktif" type="checkbox" class="rounded border-gray-300" />
            <span>Aktif (tampilkan di frontend)</span>
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
            to="/sistem-admin/Manajemen-Banner"
            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
          >
            Batal
          </RouterLink>
        </div>
      </form>

      <div v-if="pesan" :class="['mt-4 p-3 rounded-lg text-sm', pesanSukses ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800']">
        {{ pesan }}
      </div>
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
  judul: '',
  teks: '',
  gambar: '',
  lokasi: 'home_edutrip',
  urutan: 1,
  is_aktif: true,
  link: '',
})

const lokasiOptions = ref<{ value: string; label: string }[]>([
  { value: 'home', label: 'Home (Utama)' },
  { value: 'home_edutrip', label: 'Home - Section Edutrip' },
  { value: 'umrah', label: 'Halaman Program Umrah' },
  { value: 'haji', label: 'Halaman Program Haji' },
  { value: 'edutrip', label: 'Halaman Edutrip' },
  { value: 'request_product', label: 'Request Product' },
  { value: 'land_arrangement', label: 'Land Arrangement' },
  { value: 'join_partner', label: 'Join Partner' },
])

const saving = ref(false)
const uploading = ref(false)
const pesan = ref('')
const pesanSukses = ref(false)
const fileInputRef = ref<HTMLInputElement | null>(null)
/** URL lengkap dari API (detail atau upload) — dipakai untuk preview agar path storage benar */
const displayImageUrl = ref<string>('')

/** Normalisasi URL gambar: jika backend mengembalikan .../storage/banner/... ubah ke .../storage/app/public/banner/... */
function normalizeStorageImageUrl(url: string): string {
  if (!url || typeof url !== 'string') return url
  if (url.includes('/storage/app/public/')) return url
  return url.replace(/\/storage\/banner\//i, '/storage/app/public/banner/')
}

const previewUrl = computed(() => {
  if (displayImageUrl.value) return normalizeStorageImageUrl(displayImageUrl.value)
  if (formData.value.gambar) {
    const base = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api'
    const baseUrl = base.replace(/\/api\/?$/, '')
    // Default storage/app/public agar preview tampil saat document root = root proyek (shared hosting)
    const prefix = import.meta.env.VITE_STORAGE_URL_PREFIX ?? 'storage/app/public'
    const path = formData.value.gambar.replace(/^\/+/, '')
    return `${baseUrl}/${String(prefix).replace(/\/+$/, '')}/${path}`
  }
  return ''
})

/** Saat img error (404), coba URL alternatif storage/app/public */
const previewUrlFallback = computed(() => {
  const u = previewUrl.value
  if (!u) return ''
  if (u.includes('/storage/app/public/')) return ''
  return u.replace(/\/storage\/banner\//i, '/storage/app/public/banner/')
})
const imgError = ref(false)
function onPreviewImgError() {
  imgError.value = true
}
function onPreviewImgLoad() {
  imgError.value = false
}

async function loadLokasiOptions() {
  try {
    const res = await api.get('/sistem-admin/banner/lokasi-options')
    const d = res?.data?.data ?? res?.data ?? []
    if (Array.isArray(d) && d.length) lokasiOptions.value = d
  } catch {
    // keep default
  }
}

async function loadDetail() {
  if (!idParam.value) return
  try {
    const res = await api.get(`/sistem-admin/banner/${idParam.value}`)
    const d = res?.data ?? res
    const item = d?.data ?? d
    if (item && typeof item === 'object') {
      formData.value = {
        judul: item.judul ?? '',
        teks: item.teks ?? '',
        gambar: item.gambar ?? '',
        lokasi: item.lokasi ?? 'home_edutrip',
        urutan: item.urutan ?? 1,
        is_aktif: !!item.is_aktif,
        link: item.link ?? '',
      }
      if (item.gambar_url) {
        displayImageUrl.value = item.gambar_url
      } else if (item.gambar) {
        const base = import.meta.env.VITE_API_BASE_URL || ''
        const baseUrl = base.replace(/\/api\/?$/, '')
        displayImageUrl.value = baseUrl ? `${baseUrl}/storage/app/public/${String(item.gambar).replace(/^\/+/, '')}` : ''
      } else {
        displayImageUrl.value = ''
      }
    }
  } catch (e: any) {
    pesan.value = e?.response?.data?.message || 'Gagal memuat detail banner.'
    pesanSukses.value = false
  }
}

function onFileSelect(event: Event) {
  const input = event.target as HTMLInputElement
  const file = input?.files?.[0]
  if (!file) return
  if (!file.type.startsWith('image/')) {
    alert('Silakan pilih file gambar (JPG, PNG, dll).')
    return
  }
  if (file.size > 5 * 1024 * 1024) {
    alert('Ukuran file maksimal 5MB.')
    return
  }
  uploadFile(file)
  input.value = ''
}

async function uploadFile(file: File) {
  uploading.value = true
  pesan.value = ''
  displayImageUrl.value = ''
  try {
    const fd = new FormData()
    fd.append('file', file)
    const res = await api.post('/sistem-admin/banner/upload-gambar', fd)
    const data = res?.data?.data ?? res?.data
    const path = data?.path ?? data?.gambar
    if (path) {
      formData.value.gambar = path
      if (data?.url) displayImageUrl.value = data.url
    } else {
      pesan.value = 'Upload berhasil tetapi path tidak dikembalikan.'
      pesanSukses.value = false
    }
  } catch (e: any) {
    pesan.value = e?.response?.data?.message || 'Gagal mengunggah gambar.'
    pesanSukses.value = false
  } finally {
    uploading.value = false
  }
}

async function saveData() {
  if (!formData.value.judul?.trim()) {
    pesan.value = 'Judul wajib diisi.'
    pesanSukses.value = false
    return
  }
  saving.value = true
  pesan.value = ''
  pesanSukses.value = false
  try {
    const payload = {
      judul: formData.value.judul.trim(),
      teks: formData.value.teks?.trim() || null,
      gambar: formData.value.gambar || null,
      lokasi: formData.value.lokasi,
      urutan: formData.value.urutan ?? 1,
      is_aktif: formData.value.is_aktif,
      link: formData.value.link?.trim() || null,
    }
    if (isEdit.value && idParam.value) {
      await api.put(`/sistem-admin/banner/${idParam.value}`, payload)
      pesan.value = 'Banner berhasil diubah.'
    } else {
      await api.post('/sistem-admin/banner', payload)
      pesan.value = 'Banner berhasil ditambahkan.'
    }
    pesanSukses.value = true
    setTimeout(() => {
      router.push('/sistem-admin/Manajemen-Banner')
    }, 1500)
  } catch (e: any) {
    const err = e?.response?.data
    pesan.value = err?.message || (err?.errors ? JSON.stringify(err.errors) : 'Gagal menyimpan.')
    pesanSukses.value = false
  } finally {
    saving.value = false
  }
}

watch(previewUrl, () => { imgError.value = false })

onMounted(() => {
  loadLokasiOptions()
  if (isEdit.value) loadDetail()
})
</script>
