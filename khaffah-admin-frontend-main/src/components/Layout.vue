<template>
  <div class="flex h-screen max-h-screen w-full bg-gradient-to-br from-gray-50 to-teal-50/30 overflow-hidden">
    <!-- Overlay mobile (tutup sidebar saat tap) -->
    <div
      v-show="sidebarOpen"
      class="fixed inset-0 bg-black/50 z-30 md:hidden transition-opacity duration-300"
      aria-hidden="true"
      @click="sidebarOpen = false"
    />

    <!-- Sidebar: di mobile fixed drawer, di desktop tetap -->
    <aside
      :class="[
        'bg-white border-r border-gray-200/80 flex flex-col shadow-sm transition-all duration-300 ease-in-out',
        'fixed md:relative inset-y-0 left-0 z-40',
        'w-64 transform transition-transform duration-300 ease-in-out',
        sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0',
        isCollapsed ? 'md:w-20' : 'md:w-64',
      ]"
    >
      <!-- Header -->
      <div class="p-5 border-b border-gray-100 relative overflow-visible">
        <div :class="['flex items-center gap-3', sidebarCollapsed ? 'justify-center' : '']">
          <div
            class="w-11 h-11 bg-gradient-to-br rounded-xl flex items-center justify-center flex-shrink-0"
          >
            <img :src="logo" alt="Logo" class="w-11 h-11" />
          </div>
          <div v-if="!sidebarCollapsed">
            <h1 class="text-lg font-bold text-[#007b6f]">Kaffah Admin</h1>
            <p class="text-xs text-gray-500">Manajemen Haji & Umrah</p>
          </div>
        </div>

        <!-- Toggle Button (desktop only) -->
        <button
          @click="isCollapsed = !isCollapsed"
          class="absolute -right-3 top-8 bg-white border border-gray-200 rounded-full p-1 hover:bg-teal-50 hover:border-[#007b6f] transition-all duration-200 shadow-md z-10 hidden md:flex"
        >
          <svg
            v-if="isCollapsed"
            class="w-4 h-4 text-gray-600"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9 5l7 7-7 7"
            />
          </svg>
          <svg
            v-else
            class="w-4 h-4 text-gray-600"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M15 19l-7-7 7-7"
            />
          </svg>
        </button>
      </div>

      <!-- Menu Navigation -->
      <nav class="flex-1 p-4 nav-scrollbar flex flex-col gap-0" style="overflow-y: auto; -ms-overflow-style: none; scrollbar-width: none;">
        <div v-if="isLoading" class="flex items-center justify-center h-32">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-[#007b6f]"></div>
        </div>
        <template v-else>
          <!-- Dashboard (tetap di atas) -->
          <div class="mb-3">
            <RouterLink
              to="/dashboard"
              :class="[
                'w-full flex items-center px-3 py-2.5 rounded-lg transition-all duration-200',
                route.path === '/dashboard'
                  ? 'bg-gradient-to-r from-[#007b6f] to-[#005f56] text-white font-medium shadow-md shadow-[#007b6f]/20'
                  : 'text-gray-700 hover:bg-teal-50/50 hover:text-[#007b6f]',
                sidebarCollapsed ? 'justify-center' : '',
              ]"
            >
              <i class="fa-solid fa-chart-line w-5 h-5 flex-shrink-0"></i>
              <span v-if="!sidebarCollapsed" class="ml-2 font-semibold text-sm">Dashboard</span>
            </RouterLink>
          </div>

          <!-- Modul dari API, dikelompokkan dengan divider -->
          <template v-for="(m, idx) in menus" :key="m.modul_id">
            <!-- Divider sebelum Data Master dan sebelum Sistem Admin -->
            <div
              v-if="showDividerBefore(m, idx)"
              class="my-2 border-t border-gray-200/80"
              aria-hidden="true"
            />
            <div class="py-0.5">
              <!-- If module has exactly one child -->
              <div v-if="(m.child?.length ?? 0) === 1" class="relative group">
                <RouterLink
                  :to="m.child[0].url"
                  :class="[
                    'w-full flex items-center px-3 py-2.5 rounded-lg transition-all duration-200',
                    route.path === m.child[0].url
                      ? 'bg-gradient-to-r from-[#007b6f] to-[#005f56] text-white font-medium shadow-md shadow-[#007b6f]/20'
                      : 'text-gray-700 hover:bg-teal-50/50 hover:text-[#007b6f]',
                    sidebarCollapsed ? 'justify-center' : 'justify-between',
                  ]"
                >
                  <span v-if="isCollapsed" class="text-lg">
                    <template v-if="m.icon_class">
                      <i :class="m.icon_class + ' w-5 h-5'"></i>
                    </template>
                    <template v-else>
                      {{ getModuleInitial(m.nama_modul) }}
                    </template>
                  </span>
                  <span v-else class="font-semibold text-sm flex items-center">
                    <i :class="(m.icon_class || 'fa-solid fa-circle') + ' w-5 h-5'"></i>
                    <span class="ml-2">{{ m.nama_modul }}</span>
                  </span>
                </RouterLink>
              </div>

              <!-- Multiple children - dropdown -->
              <template v-else>
                <div class="relative group">
                  <button
                    :class="[
                      'w-full flex items-center px-3 py-2.5 rounded-lg transition-all duration-200',
                      open[m.modul_id]
                        ? 'bg-teal-50/70 text-[#007b6f]'
                        : 'text-gray-700 hover:bg-teal-50/50 hover:text-[#007b6f]',
                      sidebarCollapsed ? 'justify-center' : 'justify-start',
                    ]"
                    @click="toggle(m.modul_id)"
                  >
                    <i :class="(m.icon_class || 'fa-solid fa-circle') + ' w-5 h-5 flex-shrink-0 mt-0.5'"></i>
                    <span v-if="!sidebarCollapsed" class="font-semibold text-sm ml-2">{{
                      m.nama_modul
                    }}</span>
                    <svg
                      v-if="!sidebarCollapsed"
                      class="w-4 h-4 ml-auto transition-transform duration-200 flex-shrink-0"
                      :class="open[m.modul_id] ? 'rotate-180' : ''"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M19 9l-7 7-7-7"
                      />
                    </svg>
                  </button>
                </div>
                <transition
                  enter-active-class="transition-all duration-200 ease-out"
                  enter-from-class="opacity-0 -translate-y-1"
                  enter-to-class="opacity-100 translate-y-0"
                  leave-active-class="transition-all duration-150 ease-in"
                  leave-from-class="opacity-100 translate-y-0"
                  leave-to-class="opacity-0 -translate-y-1"
                >
                  <ul
                    v-show="!sidebarCollapsed && open[m.modul_id]"
                    class="mt-1 ml-3 pl-3 border-l-2 border-teal-100 space-y-0.5"
                  >
                    <li v-for="child in m.child" :key="child.sub_modul_id">
                      <RouterLink
                        :to="child.url"
                        class="block px-3 py-2 rounded-lg text-sm transition-all duration-200"
                        :class="
                          route.path === child.url
                            ? 'bg-gradient-to-r from-[#007b6f] to-[#005f56] text-white font-medium shadow-sm'
                            : 'text-gray-600 hover:bg-teal-50/50 hover:text-[#007b6f]'
                        "
                      >
                        {{ child.nama_sub_modul }}
                      </RouterLink>
                    </li>
                  </ul>
                </transition>
              </template>
            </div>
          </template>
        </template>
      </nav>

      <!-- User Profile & Logout -->
      <div class="border-t border-gray-100 p-4 bg-gradient-to-br from-teal-50/30 to-transparent">
        <!-- Expanded User Section -->
        <template v-if="!sidebarCollapsed">
          <!-- Profile Info -->
          <div class="flex items-center gap-3 mb-3">
            <div class="relative">
              <img
                v-if="user?.foto_profile"
                :src="getProfilePhotoUrl(user.foto_profile)"
                :alt="user.nama_lengkap"
                class="w-10 h-10 rounded-full object-cover ring-2 ring-[#007b6f]/20"
              />
              <div
                v-else
                class="w-10 h-10 rounded-full bg-gradient-to-br from-[#007b6f] to-[#005f56] flex items-center justify-center ring-2 ring-[#007b6f]/20"
              >
                <span class="text-white font-semibold text-sm">
                  {{ getInitials(user?.nama_lengkap) }}
                </span>
              </div>
              <div
                class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-emerald-500 rounded-full border-2 border-white"
              ></div>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold text-gray-900 truncate">
                {{ user?.nama_lengkap || 'User' }}
              </p>
              <p class="text-xs text-gray-500 truncate capitalize">
                {{ user?.subroles?.[0] || 'No Role' }}
              </p>
            </div>
          </div>

          <!-- Logout Button -->
          <button
            @click="handleLogout"
            class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50 hover:border-[#007b6f]/30 hover:text-[#007b6f] transition-all duration-200 text-sm font-medium shadow-sm group"
          >
            <svg
              class="w-4 h-4 group-hover:scale-110 transition-transform"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
              />
            </svg>
            Logout
          </button>
        </template>

        <!-- Collapsed User Section -->
        <div v-else class="flex flex-col items-center gap-3">
          <div class="relative group">
            <img
              v-if="user?.foto_profile"
              :src="getProfilePhotoUrl(user.foto_profile)"
              :alt="user.nama_lengkap"
              class="w-10 h-10 rounded-full object-cover ring-2 ring-[#007b6f]/20 cursor-pointer"
            />
            <div
              v-else
              class="w-10 h-10 rounded-full bg-gradient-to-br from-[#007b6f] to-[#005f56] flex items-center justify-center ring-2 ring-[#007b6f]/20 cursor-pointer"
            >
              <span class="text-white font-semibold text-sm">
                {{ getInitials(user?.nama_lengkap) }}
              </span>
            </div>
            <div
              class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-emerald-500 rounded-full border-2 border-white"
            ></div>

          </div>

          <button
            @click="handleLogout"
            class="p-2.5 bg-white border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50 hover:border-[#007b6f]/30 hover:text-[#007b6f] transition-all duration-200 shadow-sm group"
          >
            <svg
              class="w-4 h-4 group-hover:scale-110 transition-transform"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
              />
            </svg>
          </button>
        </div>
      </div>
    </aside>

    <!-- Main Content: min-width-0 agar flex child tidak overflow -->
    <main class="flex-1 flex flex-col min-w-0 overflow-hidden">
      <!-- Top Bar -->
      <div class="bg-white border-b border-gray-200/80 px-4 sm:px-6 py-3 sm:py-4 shadow-sm">
        <div class="flex items-center justify-between gap-2">
          <!-- Hamburger mobile -->
          <button
            type="button"
            class="md:hidden p-2 -ml-1 text-gray-500 hover:text-[#007b6f] hover:bg-teal-50/50 rounded-lg transition-all"
            aria-label="Buka menu"
            @click="sidebarOpen = true"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
          <div class="flex-1 min-w-0">
            <!-- Breadcrumb or title here -->
          </div>
          <div class="flex items-center gap-1 sm:gap-3">
            <!-- Notification Bell -->
            <button
              class="relative p-2 text-gray-400 hover:text-[#007b6f] hover:bg-teal-50/50 rounded-lg transition-all duration-200"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                />
              </svg>
              <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
            </button>

            <!-- Settings -->
            <button
              class="p-2 text-gray-400 hover:text-[#007b6f] hover:bg-teal-50/50 rounded-lg transition-all duration-200"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
                />
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Content Area: overflow-x-hidden cegah scroll horizontal -->
      <div class="p-4 sm:p-6 overflow-x-hidden overflow-y-auto flex-1 min-h-0 w-full">
        <RouterView />
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useUserSession } from '@/stores/userSession'
import { useModulesStore } from '@/stores/modules'
import { useAuthStore } from '@/stores/auth'

import logo from '@/assets/Logo/LOGOMARK/Asset-82.png'

const modulesStore = useModulesStore()
const route = useRoute()
const router = useRouter()
const userSession = useUserSession()

// Sertakan menu setting halaman di bawah Sistem Admin (tanpa perlu dari DB)
const extraSistemAdminMenus = [
  { sub_modul_id: 'setting-tentang-kami', nama_sub_modul: 'Setting Tentang Kami', url: '/sistem-admin/Setting-Tentang-Kami', icon_class: 'fa-solid fa-info-circle' },
  { sub_modul_id: 'manajemen-banner', nama_sub_modul: 'Manajemen Banner', url: '/sistem-admin/Manajemen-Banner', icon_class: 'fa-solid fa-image' },
  { sub_modul_id: 'setting-syarat-ketentuan', nama_sub_modul: 'Setting Syarat & Ketentuan', url: '/sistem-admin/Setting-Syarat-Ketentuan', icon_class: 'fa-solid fa-file-contract' },
  { sub_modul_id: 'setting-kebijakan-privasi', nama_sub_modul: 'Setting Kebijakan Privasi', url: '/sistem-admin/Setting-Kebijakan-Privasi', icon_class: 'fa-solid fa-shield-alt' },
  { sub_modul_id: 'setting-faq', nama_sub_modul: 'Setting FAQ', url: '/sistem-admin/Setting-FAQ', icon_class: 'fa-solid fa-question-circle' },
  { sub_modul_id: 'setting-pengaturan-aplikasi', nama_sub_modul: 'Pengaturan Aplikasi', url: '/sistem-admin/Setting-Pengaturan-Aplikasi', icon_class: 'fab fa-whatsapp' },
]
// Urutan modul sidebar agar rapi dan berkelompok
const SIDEBAR_MODUL_ORDER = [
  'Daftar Transaksi',
  'Paket',
  'Pendaftaran',
  'Mitra',
  'Data Master',
  'Akuntansi',
  'Manajemen',
  'Sistem Admin',
]
const menus = computed(() => {
  const list = (modulesStore.modules || []).filter((m: any) => m.nama_modul !== 'Support')
  const mapped = list.map((m: any) => {
    // Sembunyikan submodul "Modul Aplikasi" (halaman tidak dipakai)
    const childFiltered = (m.child || []).filter(
      (c: any) => c.nama_sub_modul !== 'Modul Aplikasi' && c.url !== '/Sistem-Admin/Modul-Aplikasi'
    )
    if (m.nama_modul !== 'Sistem Admin') return { ...m, child: childFiltered }
    const children = [...childFiltered]
    extraSistemAdminMenus.forEach((extra: any) => {
      if (!children.some((c: any) => c.url === extra.url)) {
        children.push(extra)
      }
    })
    return { ...m, child: children }
  })
  // Sort sesuai urutan yang ditentukan
  return [...mapped].sort((a: any, b: any) => {
    const ia = SIDEBAR_MODUL_ORDER.indexOf(a.nama_modul)
    const ib = SIDEBAR_MODUL_ORDER.indexOf(b.nama_modul)
    const ai = ia === -1 ? 999 : ia
    const bi = ib === -1 ? 999 : ib
    return ai - bi
  })
})

/** Tampilkan divider sebelum Data Master dan sebelum Sistem Admin */
function showDividerBefore(m: any, index: number) {
  if (index === 0) return false
  return m.nama_modul === 'Data Master' || m.nama_modul === 'Sistem Admin'
}
const open = ref<{ [k: number]: boolean }>({})
const isLoading = computed(() => modulesStore.isLoading)
const isCollapsed = ref(false)
const sidebarOpen = ref(false)

// Di mobile drawer selalu tampil penuh; di desktop ikuti isCollapsed
const sidebarCollapsed = computed(() => (sidebarOpen.value ? false : isCollapsed.value))

// Tutup drawer saat pindah halaman (mobile)
watch(() => route.path, () => {
  sidebarOpen.value = false
})

const user = computed(() => userSession.user)

const authStore = useAuthStore()

/** URL foto profile: jika sudah full URL dari backend, pakai langsung; kalau relatif, gabung dengan base backend */
function getProfilePhotoUrl(path: string | null | undefined): string {
  if (!path || typeof path !== 'string') return ''
  const trimmed = path.trim()
  if (trimmed.startsWith('http://') || trimmed.startsWith('https://')) return trimmed
  const baseUrl =
    import.meta.env.VITE_STORAGE_URL ||
    (typeof import.meta.env.VITE_API_BASE_URL === 'string'
      ? import.meta.env.VITE_API_BASE_URL.replace(/\/api\/?$/, '')
      : '') ||
    (import.meta.env.DEV ? 'http://localhost:8000' : '')
  const base = (baseUrl as string).replace(/\/$/, '')
  const normalized = path.replace(/\\/g, '/').trim()
  const segment = normalized.startsWith('storage/') || normalized.startsWith('/storage/')
    ? normalized.replace(/^\/+/, '')
    : `storage/${normalized}`
  return `${base}/${segment}`
}

onMounted(async () => {
  try {
    // If modules aren't loaded yet, fetch them. initDynamicRoutes already fetches
    // modules during bootstrap, but in case user navigated here without bootstrap
    // populating modules, fetch from the modules store.
    if (!modulesStore.modules.length && !modulesStore.isLoading) {
      await modulesStore.fetchModules(user.value?.subroles ?? [])
    }

    // mark opened module if current route matches
    menus.value.forEach((m: any) => {
      if (m.child?.some((ch: any) => ch.url === route.path)) {
        open.value[m.modul_id] = true
      }
    })

    // Add CSS for hiding scrollbar in webkit browsers
    const style = document.createElement('style')
    style.textContent = `
      .nav-scrollbar::-webkit-scrollbar {
        display: none;
      }
    `
    document.head.appendChild(style)
  } catch (err) {
    console.error('Failed load modules in layout', err)
  }
})

const toggle = (id: number) => {
  open.value[id] = !open.value[id]
}

const getInitials = (name?: string) => {
  if (!name) return 'U'
  return name
    .split(' ')
    .map((n) => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
}

const getModuleInitial = (name: string) => {
  return name.charAt(0).toUpperCase()
}

const handleLogout = async () => {
  await authStore.logout()
  router.push('/auth/login')
}
</script>
