<template>
  <VaCard>
    <VaCardTitle class="pb-0!">
      <h1 class="card-title text-secondary font-bold uppercase">Estado de Encomendas</h1>
    </VaCardTitle>
    <VaCardContent class="flex flex-row gap-1">
      <section class="w-1/2">
        <div class="text-xl font-bold mb-2">{{ totalOrders }}</div>
        <p :class="['text-xs', ordersChangePct >= 0 ? 'text-success' : 'text-danger', 'whitespace-nowrap']">
          <VaIcon :name="ordersChangePct >= 0 ? 'arrow_upward' : 'arrow_downward'" />
          {{ Math.abs(ordersChangePct).toFixed(1) }}%
          <span class="text-secondary"> vs mês ant.</span>
        </p>
        <div class="my-4 gap-2 flex flex-col text-xs max-h-36 overflow-y-auto">
          <div v-for="status in activeStatuses" :key="status.key" class="flex items-center">
            <span class="inline-block w-2 h-2 mr-2 rounded-full" :style="{ backgroundColor: status.color }"></span>
            <span class="text-secondary ellipsis max-w-[80px]">{{ status.label }} ({{ status.count }})</span>
          </div>
        </div>
      </section>
      <div class="w-1/2 flex items-center h-full flex-1 lg:pl-4 pl-2 -mr-1">
        <VaChart
          v-if="totalOrders > 0"
          :data="chartData"
          class="chart chart--donut h-[90px] w-[90px]"
          type="doughnut"
          :options="options"
        />
        <div v-else class="text-center text-xs text-[var(--va-secondary)] w-full py-6">Sem encomendas</div>
      </div>
    </VaCardContent>
  </VaCard>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { VaCard } from 'vuestic-ui'
import VaChart from '../../../../components/va-charts/VaChart.vue'
import type { DashboardStats } from '../../../../stores/dashboard-store'
import { ChartOptions } from 'chart.js'

const { orders } = defineProps<{
  orders: DashboardStats['orders']
}>()

const statusLabels: Record<string, string> = {
  pending: 'Pendente',
  processing: 'Em proc.',
  shipped: 'Enviado',
  delivered: 'Entregue',
  cancelled: 'Cancelado',
  refunded: 'Reembolsado',
}

const statusColors: Record<string, string> = {
  pending: '#ffd54f',
  processing: '#4fc3f7',
  shipped: '#b39ddb',
  delivered: '#81c784',
  cancelled: '#e57373',
  refunded: '#b0bec5',
}

const totalOrders = computed(() => {
  return Object.values(orders.by_status).reduce((a, b) => Number(a) + Number(b), 0)
})

const ordersChangePct = computed(() => {
  if (orders.last_month === 0) return 0
  return ((orders.this_month - orders.last_month) / orders.last_month) * 100
})

const activeStatuses = computed(() => {
  return Object.entries(orders.by_status)
    .filter((entry) => entry[1] > 0)
    .map(([key, count]) => ({
      key,
      count,
      label: statusLabels[key] || key,
      color: statusColors[key] || '#9e9e9e',
    }))
})

const chartData = computed(() => {
  const statuses = activeStatuses.value
  return {
    labels: statuses.map((s) => s.label),
    datasets: [
      {
        label: 'Encomendas',
        backgroundColor: statuses.map((s) => s.color),
        data: statuses.map((s) => s.count),
      },
    ],
  }
})

const options: ChartOptions<'doughnut'> = {
  plugins: {
    legend: {
      display: false,
    },
    tooltip: {
      enabled: true,
    },
  },
  cutout: '70%',
}
</script>
