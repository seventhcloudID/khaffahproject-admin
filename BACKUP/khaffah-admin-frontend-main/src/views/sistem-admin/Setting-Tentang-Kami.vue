<template>
  <div class="columns">
    <Card class="column is-12" elevated radius="smooth" padding="md">
      <h1 class="text-xl font-medium mb-6 flex items-center gap-2">
        <i class="fas fa-info-circle text-[#007b6f]"></i>
        Pengaturan Halaman Tentang Kami
      </h1>

      <div v-if="loading" class="flex justify-center py-12">
        <i class="fas fa-spinner fa-spin text-3xl text-[#007b6f]"></i>
      </div>

      <form v-else @submit.prevent="simpan" class="space-y-8">
        <!-- Judul & Tagline -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <Field label="Judul Section" id="title">
            <Input v-model="form.title" placeholder="Tentang Kami" />
          </Field>
          <Field label="Tagline" id="tagline">
            <Input v-model="form.tagline" placeholder="Biro Perjalanan Haji & Umrah Terpercaya" />
          </Field>
        </div>
        <Field label="Subtitle / Nama Brand" id="subtitle">
          <Input v-model="form.subtitle" placeholder="Kaffah Khadmat Tour" />
        </Field>

        <!-- Deskripsi -->
        <Field label="Deskripsi Paragraf 1" id="description">
          <Textarea v-model="form.description" rows="3" placeholder="Deskripsi singkat tentang perusahaan..." />
        </Field>
        <Field label="Deskripsi Paragraf 2" id="description_2">
          <Textarea v-model="form.description_2" rows="3" placeholder="Paragraf tambahan..." />
        </Field>

        <!-- URL Gambar -->
        <Field label="URL Gambar Hero" id="image_url">
          <Input v-model="form.image_url" placeholder="/assets/about-us.jpg" />
        </Field>

        <!-- Visi -->
        <Field label="Visi" id="visi">
          <Textarea v-model="form.visi" rows="3" placeholder="Visi perusahaan..." />
        </Field>

        <!-- Misi (list) -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Misi (satu per baris)</label>
          <Textarea
            :model-value="(form.misi || []).join('\n')"
            @update:model-value="form.misi = (($event as string) || '').split('\n').filter(Boolean)"
            rows="5"
            placeholder="Misi 1&#10;Misi 2&#10;Misi 3"
          />
        </div>

        <!-- Nilai-nilai -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-3">Nilai-nilai (judul & deskripsi)</label>
          <div class="space-y-4">
            <div
              v-for="(n, i) in form.nilai"
              :key="i"
              class="p-4 border border-gray-200 rounded-lg space-y-3"
            >
              <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-500">Nilai {{ i + 1 }}</span>
                <button
                  v-if="form.nilai.length > 1"
                  type="button"
                  @click="form.nilai.splice(i, 1)"
                  class="text-red-600 hover:text-red-800 text-sm"
                >
                  <i class="fas fa-trash"></i>
                </button>
              </div>
              <Input v-model="n.judul" placeholder="Judul (contoh: Amanah)" />
              <Textarea v-model="n.deskripsi" rows="2" placeholder="Deskripsi singkat..." />
            </div>
            <button
              type="button"
              @click="form.nilai.push({ judul: '', deskripsi: '' })"
              class="text-[#007b6f] hover:underline text-sm font-medium"
            >
              <i class="fas fa-plus mr-1"></i> Tambah Nilai
            </button>
          </div>
        </div>

        <!-- Statistik (ditampilkan di halaman Tentang Kami) -->
        <div class="border border-gray-200 rounded-lg p-5 bg-gray-50/50">
          <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
            <i class="fas fa-chart-bar text-[#007b6f]"></i>
            Statistik
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <Field label="Angka Statistik 1 (contoh: 500+)" id="stat_jamaah">
              <input
                v-model="form.stat_jamaah"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
                placeholder="500+"
              />
            </Field>
            <Field label="Label Statistik 1" id="stat_label_jamaah">
              <input
                v-model="form.stat_label_jamaah"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
                placeholder="Jamaah Berangkat"
              />
            </Field>
            <Field label="Angka Statistik 2 (contoh: 5+)" id="stat_tahun">
              <input
                v-model="form.stat_tahun"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
                placeholder="5+"
              />
            </Field>
            <Field label="Label Statistik 2" id="stat_label_tahun">
              <input
                v-model="form.stat_label_tahun"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
                placeholder="Tahun Pengalaman"
              />
            </Field>
          </div>
        </div>

        <!-- Submit -->
        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
          <Button type="button" outlined @click="muatUlang" :disabled="saving">
            Muat Ulang
          </Button>
          <Button type="submit" color="success" :disabled="saving" icon="fas fa-save">
            {{ saving ? 'Menyimpan...' : 'Simpan Perubahan' }}
          </Button>
        </div>
      </form>

      <div v-if="pesan" :class="['mt-4 p-3 rounded-lg text-sm', pesanSukses ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800']">
        {{ pesan }}
      </div>
    </Card>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import Card from '@/components/base/card/Card.vue'
import Button from '@/components/base/button/Button.vue'
import Field from '@/components/base/form/Field.vue'
import Input from '@/components/base/form/Input.vue'
import Textarea from '@/components/base/form/Textarea.vue'
import { useApi } from '@/api/useApi'

const api = useApi()
const loading = ref(true)
const saving = ref(false)
const pesan = ref('')
const pesanSukses = ref(false)

const defaultForm = () => ({
  title: 'Tentang Kami',
  subtitle: 'Kaffah Khadmat Tour',
  tagline: 'Biro Perjalanan Haji & Umrah Terpercaya',
  description: '',
  description_2: '',
  image_url: '/assets/about-us.jpg',
  visi: '',
  misi: [] as string[],
  nilai: [
    { judul: 'Amanah', deskripsi: '' },
    { judul: 'Profesional', deskripsi: '' },
    { judul: 'Nyaman', deskripsi: '' },
  ],
  stat_jamaah: '500+',
  stat_label_jamaah: 'Jamaah Berangkat',
  stat_tahun: '5+',
  stat_label_tahun: 'Tahun Pengalaman',
})

const form = reactive(defaultForm())

async function fetchData() {
  loading.value = true
  pesan.value = ''
  try {
    const res = await api.get('sistem-admin/setting-page/tentang-kami')
    const data = res?.data ?? res
    if (data && typeof data === 'object') {
      form.title = data.title ?? form.title
      form.subtitle = data.subtitle ?? form.subtitle
      form.tagline = data.tagline ?? form.tagline
      form.description = data.description ?? ''
      form.description_2 = data.description_2 ?? ''
      form.image_url = data.image_url ?? form.image_url
      form.visi = data.visi ?? ''
      form.misi = Array.isArray(data.misi) ? data.misi : []
      form.nilai = Array.isArray(data.nilai) && data.nilai.length
        ? data.nilai.map((n: any) => ({ judul: n.judul ?? '', deskripsi: n.deskripsi ?? '' }))
        : defaultForm().nilai
      form.stat_jamaah = data.stat_jamaah ?? form.stat_jamaah
      form.stat_label_jamaah = data.stat_label_jamaah ?? form.stat_label_jamaah
      form.stat_tahun = data.stat_tahun ?? form.stat_tahun
      form.stat_label_tahun = data.stat_label_tahun ?? form.stat_label_tahun
    }
  } catch (e: any) {
    pesan.value = e?.response?.data?.message || 'Gagal memuat data.'
    pesanSukses.value = false
  } finally {
    loading.value = false
  }
}

async function simpan() {
  saving.value = true
  pesan.value = ''
  pesanSukses.value = false
  try {
    const payload = {
      ...form,
      misi: Array.isArray(form.misi) ? form.misi : String(form.misi).split('\n').filter(Boolean),
    }
    await api.put('sistem-admin/setting-page/tentang-kami', payload)
    pesan.value = 'Konten Tentang Kami berhasil disimpan.'
    pesanSukses.value = true
  } catch (e: any) {
    pesan.value = e?.response?.data?.message || 'Gagal menyimpan.'
    pesanSukses.value = false
  } finally {
    saving.value = false
  }
}

function muatUlang() {
  fetchData()
}

onMounted(() => {
  fetchData()
})
</script>
