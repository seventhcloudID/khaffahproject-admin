<template>
  <div class="columns">
    <Card class="column is-12" elevated radius="smooth" padding="md">
      <h1 class="text-xl font-medium mb-2 flex items-center gap-2">
        <i class="fas fa-chart-area text-[#007b6f]"></i>
        Monitoring Operasional
      </h1>
      <p class="text-gray-600 text-sm mb-6">
        Mengatur akun admin dan role manajemen: <strong>CS</strong> (Customer Service), <strong>Akuntan</strong>, <strong>Owner</strong>.
      </p>

      <!-- Role info badges -->
      <div class="flex flex-wrap gap-2 mb-6">
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
          <i class="fas fa-headset mr-1"></i> CS
        </span>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
          <i class="fas fa-calculator mr-1"></i> Akuntan
        </span>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
          <i class="fas fa-user-shield mr-1"></i> Owner
        </span>
      </div>

      <div class="mb-4 flex flex-wrap items-center gap-3">
        <div class="flex-1 min-w-[200px]">
          <input
            v-model="searchQuery"
            type="text"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
            placeholder="Cari nama atau email..."
          />
        </div>
        <select
          v-model="filterRole"
          class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
        >
          <option value="">Semua role</option>
          <option value="support">CS</option>
          <option value="akutansi">Akuntan</option>
          <option value="admin">Owner</option>
        </select>
        <Button
          color="success"
          icon="fas fa-search"
          :disabled="loading"
          @click="fetchData"
        >
          Cari
        </Button>
        <Button
          outlined
          icon="fas fa-sync-alt"
          :disabled="loading"
          @click="fetchData"
        >
          Refresh
        </Button>
      </div>

      <div v-if="loading" class="flex justify-center py-12">
        <i class="fas fa-spinner fa-spin text-3xl text-[#007b6f]"></i>
      </div>

      <DataTable
        v-else
        :value="filteredAdmins"
        class="datatable"
        :paginator="true"
        :rows="10"
        :rowsPerPageOptions="[5, 10, 20, 50]"
        paginatorTemplate="CurrentPageReport FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
        currentPageReportTemplate="Menampilkan {first} - {last} dari {totalRecords} data"
        responsiveLayout="scroll"
      >
        <template #empty>
          <div class="text-center py-8 text-gray-500">
            <i class="fas fa-user-cog text-4xl mb-3 opacity-50"></i>
            <p>Tidak ada data akun admin.</p>
            <p class="text-sm mt-1">Pastikan ada user dengan role <strong>superadmin</strong> di database (jalankan seeder jika perlu).</p>
          </div>
        </template>
        <Column field="no" header="No" style="width: 60px" />
        <Column field="nama" header="Nama" />
        <Column field="email" header="Email" />
        <Column field="roleLabel" header="Role Admin">
          <template #body="{ data }">
            <span :class="roleBadgeClass(data.roleKey)">
              {{ data.roleLabel || '-' }}
            </span>
          </template>
        </Column>
        <Column field="status" header="Status">
          <template #body="{ data }">
            <span
              :class="[
                'inline-flex px-2 py-0.5 rounded text-xs font-medium',
                data.status === 'aktif' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600',
              ]"
            >
              {{ data.status === 'aktif' ? 'Aktif' : (data.status || '-') }}
            </span>
          </template>
        </Column>
        <Column header="Terakhir login">
          <template #body="{ data }">
            {{ data.last_login ? formatDate(data.last_login) : '-' }}
          </template>
        </Column>
        <Column header="Aksi" :style="{ width: '100px' }">
          <template #body="{ data }">
            <Button
              v-if="data.roleKey !== 'superadmin'"
              label="Ubah role"
              icon="fas fa-user-edit"
              class="p-button-sm p-button-outlined"
              @click="bukaModalRole(data)"
            />
            <span v-else class="text-gray-400 text-sm">—</span>
          </template>
        </Column>
      </DataTable>
    </Card>

    <!-- Modal Ubah Role -->
    <Modal
      :open="showRoleModal"
      size="medium"
      rounded
      :closeOnEsc="true"
      :closeOnOutside="true"
      cancelLabel="Batal"
      actionLabel="Simpan"
      :showAction="true"
      :hideFooter="false"
      @close="tutupModalRole"
      @action="simpanRole"
    >
      <template #header>
        <span class="flex items-center gap-2">
          <i class="fas fa-user-tag text-[#007b6f]"></i>
          Ubah Role Admin
        </span>
      </template>
      <template #content>
        <div v-if="selectedAdmin" class="space-y-4">
          <p class="text-sm text-gray-600">
            Ubah role untuk <strong>{{ selectedAdmin.nama }}</strong> ({{ selectedAdmin.email }}).
          </p>
          <Field label="Role Admin" id="role-admin" required>
            <select
              v-model="editRoleKey"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#007b6f]"
            >
              <option value="support">CS (Customer Service)</option>
              <option value="akutansi">Akuntan</option>
              <option value="admin">Owner</option>
            </select>
          </Field>
          <p v-if="rolePesan" :class="rolePesanSukses ? 'text-green-600 text-sm' : 'text-red-600 text-sm'">
            {{ rolePesan }}
          </p>
        </div>
      </template>
    </Modal>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import Card from '@/components/base/card/Card.vue'
import Button from '@/components/base/button/Button.vue'
import Field from '@/components/base/form/Field.vue'
import Modal from '@/components/base/modal/Modal.vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import { useApi } from '@/api/useApi'

const ROLE_LABELS: Record<string, string> = {
  support: 'CS',
  akutansi: 'Akuntan',
  admin: 'Owner',
  manajemen: 'Manajemen',
  superadmin: 'Superadmin',
}

const api = useApi()
const loading = ref(false)
const searchQuery = ref('')
const filterRole = ref('')
const admins = ref<any[]>([])
const showRoleModal = ref(false)
const selectedAdmin = ref<any>(null)
const editRoleKey = ref('')
const rolePesan = ref('')
const rolePesanSukses = ref(false)
const savingRole = ref(false)

function roleBadgeClass(roleKey: string): string {
  const base = 'inline-flex px-2 py-0.5 rounded text-xs font-medium '
  const map: Record<string, string> = {
    support: 'bg-blue-100 text-blue-800',
    akutansi: 'bg-emerald-100 text-emerald-800',
    admin: 'bg-amber-100 text-amber-800',
    manajemen: 'bg-purple-100 text-purple-800',
    superadmin: 'bg-gray-200 text-gray-800',
  }
  return base + (map[roleKey] || 'bg-gray-100 text-gray-800')
}

const filteredAdmins = computed(() => {
  let list = admins.value
  const q = (searchQuery.value || '').trim().toLowerCase()
  if (q) {
    list = list.filter(
      (u) =>
        (u.nama && u.nama.toLowerCase().includes(q)) ||
        (u.email && u.email.toLowerCase().includes(q)),
    )
  }
  if (filterRole.value) {
    list = list.filter((u) => u.roleKey === filterRole.value)
  }
  return list.map((u, i) => ({ ...u, no: i + 1 }))
})

function formatDate(val: string) {
  if (!val) return '-'
  try {
    const d = new Date(val)
    return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
  } catch {
    return val
  }
}

function mapUserToRow(u: any): any {
  const subroles = Array.isArray(u.subroles) ? u.subroles : []
  const roleKey = subroles[0] || (u.role === 'superadmin' ? 'superadmin' : 'admin')
  return {
    ...u,
    nama: u.nama_lengkap || u.name || u.nama || '-',
    email: u.email || '-',
    roleKey,
    roleLabel: ROLE_LABELS[roleKey] || roleKey,
    status: u.is_active !== false ? 'aktif' : 'nonaktif',
    last_login: u.last_login_at || u.updated_at || null,
  }
}

function bukaModalRole(admin: any) {
  selectedAdmin.value = admin
  editRoleKey.value = admin.roleKey === 'admin' ? 'admin' : admin.roleKey
  if (editRoleKey.value === 'superadmin') editRoleKey.value = 'admin'
  rolePesan.value = ''
  showRoleModal.value = true
}

function tutupModalRole() {
  showRoleModal.value = false
  selectedAdmin.value = null
  editRoleKey.value = ''
  rolePesan.value = ''
}

async function simpanRole() {
  if (!selectedAdmin.value?.id) return
  rolePesan.value = ''
  rolePesanSukses.value = false
  savingRole.value = true
  try {
    await api.put(`/sistem-admin/user/update-subrole/${selectedAdmin.value.id}`, {
      subrole: editRoleKey.value,
    })
    rolePesan.value = 'Role admin berhasil diperbarui.'
    rolePesanSukses.value = true
    fetchData()
    setTimeout(() => tutupModalRole(), 1200)
  } catch (e: any) {
    rolePesan.value = e?.response?.data?.message || e?.message || 'Gagal menyimpan role.'
    rolePesanSukses.value = false
  } finally {
    savingRole.value = false
  }
}

async function fetchData() {
  loading.value = true
  try {
    // useApi mengembalikan response.data, jadi res = { status, message, data: [...] }
    const res = await api.get('/sistem-admin/user/get-list-admin').catch(() => null)
    const list = res && typeof res === 'object' && Array.isArray(res.data) ? res.data : []
    admins.value = list.map((u: any) => mapUserToRow(u))
  } catch (err) {
    console.error('Gagal memuat daftar admin:', err)
    admins.value = []
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchData()
})
</script>
