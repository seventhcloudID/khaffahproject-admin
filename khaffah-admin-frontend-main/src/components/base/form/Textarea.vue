<script setup lang="ts">
import { ref, watch, reactive, computed } from 'vue'
import { useFieldContext } from '@/composable/useFieldContext'

export interface TextareaEmits {
  (event: 'update:modelValue', value?: unknown): void
}
export interface TextareaProps {
  raw?: boolean
  modelValue?: string | number | null
}
const vFieldContext = reactive(
  useFieldContext({}) as { id?: string; required?: boolean }
)

const emits = defineEmits<TextareaEmits>()
const props = withDefaults(defineProps<TextareaProps>(), { modelValue: () => '' })
const value = ref(props.modelValue != null && props.modelValue !== '' ? String(props.modelValue) : '')

watch(value, () => {
  emits('update:modelValue', value.value)
})
watch(
  () => props.modelValue,
  () => {
    value.value = props.modelValue != null && props.modelValue !== '' ? String(props.modelValue) : ''
  }
)

const classes = computed(() => {
  if (props.raw) return []

  return ['textarea']
})

const fieldId = computed(() => vFieldContext.id ?? '')
</script>

<template>
  <textarea
    :id="fieldId"
    v-model="value"
    :class="classes"
    :name="fieldId"
  ></textarea>
</template>

<style>
.textarea.is-rounded{    
  border-radius: 20px;
}
</style>