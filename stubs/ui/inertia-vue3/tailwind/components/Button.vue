<template>
  <button
    :type="type"
    class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-medium shadow-sm border border-gray-200 dark:border-gray-800
           bg-gray-900 text-white hover:bg-gray-800
           disabled:opacity-60 disabled:cursor-not-allowed
           dark:bg-gray-100 dark:text-gray-900 dark:hover:bg-white"
    :disabled="disabled"
  >
    <slot />
  </button>
</template>

<script setup lang="ts">
withDefaults(defineProps<{
  type?: 'button' | 'submit' | 'reset'
  disabled?: boolean
}>(), {
  type: 'button',
  disabled: false,
})
</script>
