<template>
  <VaCard>
    <VaCardTitle>
      <h1 class="card-title text-tag text-secondary font-bold uppercase">Faturação Mensal</h1>
    </VaCardTitle>
    <VaCardContent>
      <div class="p-1 bg-black rounded absolute right-4 top-4">
        <VaIcon name="mso-attach_money" color="#fff" size="large" />
      </div>
      <section>
        <div class="text-xl font-bold mb-2">{{ formatMoney(revenue.this_month) }}</div>
        <p :class="['text-xs', pctChange >= 0 ? 'text-success' : 'text-danger']">
          <VaIcon :name="pctChange >= 0 ? 'arrow_upward' : 'arrow_downward'" />
          {{ Math.abs(pctChange).toFixed(1) }}%
          <span class="text-secondary"> vs mês ant.</span>
        </p>
      </section>
      <div class="w-full flex items-center mt-2">
        <VaChart :data="chartData" class="h-24 w-full" type="line" :options="options" />
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

const { revenue } = defineProps<{
  revenue: DashboardStats['revenue']
}>()

const formatMoney = (amount: number) => {
  return new Intl.NumberFormat('pt-PT', {
    style: 'currency',
    currency: 'EUR',
  }).format(amount)
}

const pctChange = computed(() => {
  if (revenue.last_month === 0) return 0
  return ((revenue.this_month - revenue.last_month) / revenue.last_month) * 100
})

const chartData = computed(() => {
  return {
    labels: revenue.by_month.map((d: any) => d.label),
    datasets: [
      {
        label: 'Faturação',
        borderColor: '#154EC1',
        backgroundColor: 'rgba(21, 78, 193, 0.1)',
        data: revenue.by_month.map((d: any) => d.total),
        fill: true,
        tension: 0.3,
        borderWidth: 2,
        pointRadius: 0,
      },
    ],
  }
})

const options: ChartOptions<'line'> = {
  scales: {
    x: {
      display: false,
      grid: {
        display: false,
      },
    },
    y: {
      display: false,
      grid: {
        display: false,
      },
      ticks: {
        display: false,
      },
    },
  },
  interaction: {
    intersect: false,
    mode: 'index',
  },
  plugins: {
    legend: {
      display: false,
    },
    tooltip: {
      enabled: true,
    },
  },
}
</script>
