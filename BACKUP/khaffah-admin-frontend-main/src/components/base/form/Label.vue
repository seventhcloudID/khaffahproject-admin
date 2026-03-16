<template>
  <label class="label" :for="for">
    <slot />
  </label>
</template>

<script setup lang="ts">
defineProps({
  for: String,
});
</script>
