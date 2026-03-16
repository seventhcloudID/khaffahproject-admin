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
})

const form = reactive(defaultForm())

async function fetchData() {
  loading.value = true
  pesan.value = ''
  try {
    const res = await api.get('sistem-admin/setting-page/app-settings')
    const body = res?.data ?? res
    const data = body?.data ?? body
    if (data && typeof data === 'object' && data.whatsapp_admin != null) {
      form.whatsapp_admin = data.whatsapp_admin
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
