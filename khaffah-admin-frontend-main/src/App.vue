<template>
  <component :is="layout">
    <router-view />
  </component>
</template>

<script setup lang="ts">
import { computed } from "vue"
import { useRoute } from "vue-router"
import Layout from "@/components/Layout.vue"

const route = useRoute()

const layout = computed(() => {
  return route.meta.layout === 'empty' ? 'div' : Layout
})
</script>
