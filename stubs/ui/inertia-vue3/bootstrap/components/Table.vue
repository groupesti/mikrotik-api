<template>
  <div class="table-responsive">
    <table class="table table-striped table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th v-for="h in headers" :key="h.key">{{ h.label }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(row, idx) in rows" :key="idx">
          <td v-for="h in headers" :key="h.key">
            <slot :name="`cell:${h.key}`" :row="row">
              {{ row[h.key] ?? '-' }}
            </slot>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup lang="ts">
export type TableHeader = { key: string; label: string }

defineProps<{
  headers: TableHeader[]
  rows: Record<string, any>[]
}>()
</script>
