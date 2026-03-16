<script setup lang="ts">
export interface PlaceloadProps {
  width?: string
  height?: string
  mobileWidth?: string
  mobileHeight?: string
  disabled?: boolean
  centered?: boolean
}

const props = withDefaults(defineProps<PlaceloadProps>(), {
  width: '100%',
  height: '10px',
})
</script>

<template>
  <div
    class="bg-gray-300 rounded animate-pulse"
    :class="[
      props.centered ? 'mx-auto' : '',
      props.disabled ? 'opacity-40 animate-none' : ''
    ]"
    :style="{
      width: props.width,
      height: props.height
    }"
  ></div>
</template>

<style scoped>
</style>
