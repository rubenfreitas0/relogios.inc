<script lang="ts" setup>
import { onMounted } from 'vue'
import { useDashboardStore } from '../../../stores/dashboard-store'
import RevenueUpdates from './cards/RevenueReport.vue'
import LatestOrdersTable from './cards/LatestOrdersTable.vue'
import DataSection from './DataSection.vue'
import YearlyBreakup from './cards/YearlyBreakup.vue'
import MonthlyEarnings from './cards/MonthlyEarnings.vue'

const store = useDashboardStore()

onMounted(() => {
  store.fetchStats()
})
</script>

<template>
  <h1 class="page-title font-bold">Dashboard</h1>

  <div v-if="store.loading && !store.stats" class="flex justify-center items-center py-24">
    <VaProgressCircle indeterminate size="large" />
  </div>

  <section v-else-if="store.stats" class="flex flex-col gap-4">
    <!-- Bloco Principal: Faturação e Resumos rápidos -->
    <div class="flex flex-col lg:flex-row gap-4">
      <RevenueUpdates :revenue="store.stats.revenue" class="w-full lg:w-[70%]" />
      <div class="flex flex-col gap-4 w-full lg:w-[30%]">
        <YearlyBreakup :orders="store.stats.orders" class="h-full" />
        <MonthlyEarnings :revenue="store.stats.revenue" />
      </div>
    </div>

    <!-- Métricas -->
    <DataSection :stats="store.stats" />

    <!-- Últimas Encomendas -->
    <div class="flex flex-col gap-4">
      <LatestOrdersTable :orders="store.stats.latest_orders" class="w-full" />
    </div>
  </section>

  <div v-else-if="store.error" class="p-4">
    <VaAlert color="danger">
      {{ store.error }}
    </VaAlert>
  </div>
</template>
