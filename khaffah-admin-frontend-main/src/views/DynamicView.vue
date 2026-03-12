<template>
  <main class="p-4">
    <h2 class="text-xl font-semibold mb-2">Halaman belum dibuat</h2>
    <p class="text-gray-600">
      Path: <strong>{{ route.path }}</strong>
    </p>
    <p class="mt-4">
      Judul: <strong>{{ route.meta?.title || '—' }}</strong>
    </p>
    <div class="mt-6 p-4 bg-gray-50 border rounded text-sm text-gray-700">
      Konten untuk halaman ini belum diimplementasikan.
    </div>
  </main>
</template>

<script setup lang="ts">
import { useRoute } from 'vue-router'
const route = useRoute()
</script>
