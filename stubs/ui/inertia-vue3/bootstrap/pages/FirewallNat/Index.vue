<template>
  <div class="vstack gap-3">
    <Card title="NAT" subtitle="RouterOS → /ip/firewall/nat">
      <template #actions>
        <Button @click="refresh" :disabled="loading">Rafraîchir</Button>
      </template>

      <Table :headers="headers" :rows="rows" />
    </Card>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import Card from '../../components/Card.vue'
import Button from '../../components/Button.vue'
import Table, { type TableHeader } from '../../components/Table.vue'

type NatRow = Record<string, any>

const loading = ref(false)
const rows = ref<NatRow[]>([])

const headers: TableHeader[] = [
  { key: '.id', label: 'ID' },
  { key: 'chain', label: 'Chain' },
  { key: 'action', label: 'Action' },
  { key: 'protocol', label: 'Protocol' },
  { key: 'src-address', label: 'Src' },
  { key: 'dst-address', label: 'Dst' },
  { key: 'to-addresses', label: 'To addr' },
  { key: 'to-ports', label: 'To ports' },
  { key: 'disabled', label: 'Disabled' },
]

async function refresh() {
  loading.value = true
  try {
    const res = await fetch('/mikrotik/firewall/nat')
    rows.value = await res.json()
  } finally {
    loading.value = false
  }
}

refresh()
</script>
