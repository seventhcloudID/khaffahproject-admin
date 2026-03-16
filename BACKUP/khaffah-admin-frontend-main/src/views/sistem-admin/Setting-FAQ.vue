<template>
  <div class="columns">
    <Card class="column is-12" elevated radius="smooth" padding="md">
      <h1 class="text-xl font-medium mb-6 flex items-center gap-2">
        <i class="fas fa-question-circle text-[#007b6f]"></i>
        Pengaturan Halaman FAQ
      </h1>

      <div v-if="loading" class="flex justify-center py-12">
        <i class="fas fa-spinner fa-spin text-3xl text-[#007b6f]"></i>
      </div>

      <form v-else @submit.prevent="simpan" class="space-y-6">
        <Field label="Judul Halaman" id="title">
          <input
            v-model="form.title"
            type="text"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
            placeholder="Pertanyaan yang Sering Diajukan"
          />
        </Field>
        <Field label="Subtitle (opsional)" id="subtitle">
          <input
            v-model="form.subtitle"
            type="text"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
            placeholder="Temukan jawaban untuk pertanyaan umum..."
          />
        </Field>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-3">Pertanyaan & Jawaban</label>
          <div class="space-y-4">
            <div
              v-for="(item, i) in form.items"
              :key="i"
              class="p-4 border border-gray-200 rounded-lg space-y-3"
            >
              <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-500">FAQ {{ i + 1 }}</span>
                <button
                  v-if="form.items.length > 1"
                  type="button"
                  @click="form.items.splice(i, 1)"
                  class="text-red-600 hover:text-red-800 text-sm"
                >
                  <i class="fas fa-trash"></i>
                </button>
              </div>
              <input
                v-model="item.question"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
                placeholder="Pertanyaan"
              />
              <textarea
                v-model="item.answer"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
                placeholder="Jawaban"
              ></textarea>
            </div>
            <button
              type="button"
              @click="form.items.push({ question: '', answer: '' })"
              class="text-[#007b6f] hover:underline text-sm font-medium"
            >
              <i class="fas fa-plus mr-1"></i> Tambah FAQ
            </button>
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
import { useApi } from '@/api/useApi'

const api = useApi()
const loading = ref(true)
const saving = ref(false)
const pesan = ref('')
const pesanSukses = ref(false)

const defaultForm = () => ({
  title: 'Pertanyaan yang Sering Diajukan',
  subtitle: 'Temukan jawaban untuk pertanyaan umum seputar layanan haji dan umrah kami.',
  items: [
    { question: 'Bagaimana cara mendaftar paket umrah?', answer: 'Anda dapat mendaftar melalui website dengan memilih paket yang tersedia, mengisi formulir, dan melakukan pembayaran sesuai tahapan yang kami informasikan.' },
    { question: 'Apakah pembayaran bisa dicicil?', answer: 'Ya, kami menyediakan skema pembayaran bertahap. Detail jadwal cicilan akan diberikan setelah Anda memilih paket.' },
    { question: 'Dokumen apa saja yang diperlukan?', answer: 'Umumnya paspor yang masih berlaku minimal 6 bulan, foto, dan dokumen pendukung lain sesuai ketentuan yang berlaku. Tim kami akan memandu Anda.' },
  ] as { question: string; answer: string }[],
})

const form = reactive<{ title: string; subtitle: string; items: { question: string; answer: string }[] }>({
  title: defaultForm().title,
  subtitle: defaultForm().subtitle,
  items: defaultForm().items.map((x) => ({ ...x })),
})

async function fetchData() {
  loading.value = true
  pesan.value = ''
  try {
    const res = await api.get('sistem-admin/setting-page/faq')
    const data = res?.data ?? res
    if (data && typeof data === 'object') {
      form.title = data.title ?? defaultForm().title
      form.subtitle = data.subtitle ?? ''
      form.items = Array.isArray(data.items) && data.items.length
        ? data.items.map((x: any) => ({ question: x.question ?? '', answer: x.answer ?? '' }))
        : defaultForm().items.map((x) => ({ ...x }))
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
    await api.put('sistem-admin/setting-page/faq', { title: form.title, subtitle: form.subtitle, items: form.items })
    pesan.value = 'FAQ berhasil disimpan.'
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
