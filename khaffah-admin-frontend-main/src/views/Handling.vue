<template>
  <div class="flex items-center justify-center h-screen bg-gray-100">
    <div class="text-center">
      <h1 class="text-5xl font-bold mb-4 text-red-500">ngapain kamu disini ???</h1>
      <p class="text-gray-600 text-lg">Akses ini cuma buat Superadmin bro 😎</p>
      <button class="bg-red-500 text-white px-4 py-2 rounded mt-4" @click="logout">Logout</button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

const auth = useAuthStore()
const router = useRouter()

function logout() {
  auth.logout()
  router.push('/auth/login')
}
</script>
