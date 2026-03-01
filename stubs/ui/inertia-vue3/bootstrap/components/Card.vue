<template>
  <div class="card shadow-sm">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-start gap-3">
        <div>
          <h5 class="card-title mb-1">{{ title }}</h5>
          <p v-if="subtitle" class="text-muted small mb-0">{{ subtitle }}</p>
        </div>
        <slot name="actions" />
      </div>
      <div class="mt-3">
        <slot />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  title: string
  subtitle?: string
}>()
</script>
