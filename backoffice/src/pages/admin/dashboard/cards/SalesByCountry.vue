<template>
  <VaCard class="flex flex-col">
    <VaCardTitle class="flex items-center justify-between">
      <h1 class="card-title text-secondary font-bold uppercase">Vendas por País (nº)</h1>
    </VaCardTitle>
    <VaCardContent class="flex-1">
      <VaDataTable
        v-if="rows.length > 0"
        :columns="[
          { key: 'name', label: 'País' },
          { key: 'count', label: 'Nº de Vendas', align: 'right' },
        ]"
        :items="rows"
      >
        <template #cell(count)="{ rowData }">
          <span class="font-mono font-semibold">{{ rowData.count }}</span>
        </template>
      </VaDataTable>

      <div v-else class="text-center py-10 text-secondary text-sm">Ainda não há vendas registadas.</div>
    </VaCardContent>
  </VaCard>
</template>

<script lang="ts" setup>
import { computed } from 'vue'
import { useDashboardStore } from '../../../../stores/dashboard-store'

const store = useDashboardStore()

// Nomes legíveis para os códigos ISO mais comuns; fallback para o próprio código
const COUNTRY_NAMES: Record<string, string> = {
  PT: 'Portugal',
  ES: 'Espanha',
  FR: 'França',
  DE: 'Alemanha',
  IT: 'Itália',
  NL: 'Países Baixos',
  BE: 'Bélgica',
  GB: 'Reino Unido',
  IE: 'Irlanda',
  CH: 'Suíça',
  LU: 'Luxemburgo',
  AT: 'Áustria',
}

const rows = computed(() => {
  const list = store.stats?.sales_by_country ?? []
  return list.map((r) => ({
    name: COUNTRY_NAMES[r.country] ? `${COUNTRY_NAMES[r.country]} (${r.country})` : r.country,
    count: r.count,
  }))
})
</script>
