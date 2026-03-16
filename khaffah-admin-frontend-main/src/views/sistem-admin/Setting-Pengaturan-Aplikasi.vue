<template>
  <div class="columns">
    <Card class="column is-12" elevated radius="smooth" padding="md">
      <h1 class="text-xl font-medium mb-6 flex items-center gap-2">
        <i class="fab fa-whatsapp text-[#25D366]"></i>
        Pengaturan Aplikasi
      </h1>

      <div v-if="loading" class="flex justify-center py-12">
        <i class="fas fa-spinner fa-spin text-3xl text-[#007b6f]"></i>
      </div>

      <form v-else @submit.prevent="simpan" class="space-y-6">
        <Field label="Nomor WhatsApp Admin (untuk tombol Hubungi Admin)" id="whatsapp_admin">
          <input
            v-model="form.whatsapp_admin"
            type="text"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
            placeholder="6289677771070 atau +62 896-7777-1070"
          />
          <p class="text-sm text-gray-500 mt-1">
            Nomor ini akan dipakai di tombol "Hubungi Admin" pada halaman detail pesanan (user & mitra). Format: 62xxx tanpa + atau spasi.
          </p>
        </Field>

        <div class="border-t border-gray-200 pt-6">
          <h2 class="text-base font-semibold text-gray-800 mb-1 flex items-center gap-2">
            <i class="fas fa-file-invoice-dollar text-[#007b6f]"></i>
            Harga Komponen Visa (Mitra)
          </h2>
          <p class="text-sm text-gray-500 mb-4">
            Harga default yang tampil di halaman komponen Visa untuk mitra. Bisa diedit oleh mitra saat pemesanan.
          </p>
          <div class="grid gap-4 sm:grid-cols-2">
            <Field label="Harga Visa per pax (IDR)" id="harga_visa">
              <input
                v-model.number="form.harga_visa"
                type="number"
                min="0"
                step="1000"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
                placeholder="2000000"
              />
            </Field>
            <Field label="Harga Visa & Transportasi per pax (IDR)" id="harga_visa_transportasi">
              <input
                v-model.number="form.harga_visa_transportasi"
                type="number"
                min="0"
                step="1000"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
                placeholder="2500000"
              />
            </Field>
          </div>
        </div>

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
  whatsapp_admin: '6289677771070',
  harga_visa: 2000000,
  harga_visa_transportasi: 2500000,
})

const form = reactive(defaultForm())

async function fetchData() {
  loading.value = true
  pesan.value = ''
  try {
    const res = await api.get('sistem-admin/setting-page/app-settings')
    const body = res?.data ?? res
    const data = body?.data ?? body
    if (data && typeof data === 'object') {
      if (data.whatsapp_admin != null) form.whatsapp_admin = data.whatsapp_admin
      if (data.harga_visa != null) form.harga_visa = Number(data.harga_visa)
      if (data.harga_visa_transportasi != null) form.harga_visa_transportasi = Number(data.harga_visa_transportasi)
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
    await api.put('sistem-admin/setting-page/app-settings', {
      whatsapp_admin: form.whatsapp_admin || undefined,
      harga_visa: form.harga_visa != null && form.harga_visa !== '' ? Number(form.harga_visa) : undefined,
      harga_visa_transportasi: form.harga_visa_transportasi != null && form.harga_visa_transportasi !== '' ? Number(form.harga_visa_transportasi) : undefined,
    })
    pesan.value = 'Pengaturan aplikasi berhasil disimpan.'
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
