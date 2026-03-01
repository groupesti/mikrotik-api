<template>
  <div class="overflow-x-auto rounded-2xl border border-gray-200 dark:border-gray-800">
    <table class="min-w-full text-sm">
      <thead class="bg-gray-50 dark:bg-gray-800">
        <tr>
          <th v-for="h in headers" :key="h.key" class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">
            {{ h.label }}
          </th>
        </tr>
      </thead>
      <tbody class="bg-white dark:bg-gray-900">
        <tr v-for="(row, idx) in rows" :key="idx" class="border-t border-gray-200 dark:border-gray-800">
          <td v-for="h in headers" :key="h.key" class="px-4 py-3 text-gray-700 dark:text-gray-200">
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
