<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
    <DataSectionItem
      v-for="metric in dashboardMetrics"
      :key="metric.id"
      :title="metric.title"
      :value="metric.value"
      :change-text="metric.changeText"
      :up="metric.changeDirection === 'up'"
      :icon-background="metric.iconBackground"
      :icon-color="metric.iconColor"
    >
      <template #icon>
        <VaIcon :name="metric.icon" size="large" />
      </template>
    </DataSectionItem>
  </div>
</template>

<script lang="ts" setup>
import { computed } from 'vue'
import { useColors } from 'vuestic-ui'
import DataSectionItem from './DataSectionItem.vue'
import type { DashboardStats } from '../../../stores/dashboard-store'

interface DashboardMetric {
  id: string
  title: string
  value: string
  icon: string
  changeText: string
  changeDirection: 'up' | 'down' | 'neutral'
  iconBackground: string
  iconColor: string
}

const { stats } = defineProps<{
  stats: DashboardStats
}>()

const { getColor } = useColors()

const formatMoney = (amount: number) => {
  return new Intl.NumberFormat('pt-PT', {
    style: 'currency',
    currency: 'EUR',
  }).format(amount)
}

const dashboardMetrics = computed<DashboardMetric[]>(() => {
  // Calcular variação de faturação
  const revenueThisMonth = stats.revenue.this_month
  const revenueLastMonth = stats.revenue.last_month
  let revChangePct = 0
  let revDirection: 'up' | 'down' | 'neutral' = 'neutral'

  if (revenueLastMonth > 0) {
    revChangePct = ((revenueThisMonth - revenueLastMonth) / revenueLastMonth) * 100
    revDirection = revChangePct >= 0 ? 'up' : 'down'
  } else if (revenueThisMonth > 0) {
    revDirection = 'up'
  }

  return [
    {
      id: 'revenue',
      title: 'Faturação (Mês)',
      value: formatMoney(revenueThisMonth),
      icon: 'mso-attach_money',
      changeText:
        revenueLastMonth > 0
          ? `${revChangePct >= 0 ? '+' : ''}${revChangePct.toFixed(1)}% vs mês ant.`
          : 'Primeiras vendas',
      changeDirection: revDirection,
      iconBackground: getColor('primary'),
      iconColor: getColor('on-primary'),
    },
    {
      id: 'products',
      title: 'Produtos Ativos',
      value: String(stats.products.active),
      icon: 'mso-sell',
      changeText: `${stats.products.total} no catálogo total`,
      changeDirection: 'up',
      iconBackground: getColor('success'),
      iconColor: getColor('on-success'),
    },
    {
      id: 'customers',
      title: 'Total de Clientes',
      value: String(stats.customers.total),
      icon: 'mso-account_circle',
      changeText: 'Clientes registados',
      changeDirection: 'up',
      iconBackground: getColor('info'),
      iconColor: getColor('on-info'),
    },
    {
      id: 'pendingOrders',
      title: 'Encomendas Pendentes',
      value: String(stats.orders.pending_count),
      icon: 'mso-pending_actions',
      changeText: 'Pagas, a aguardar envio',
      changeDirection: 'neutral',
      iconBackground: getColor('warning'),
      iconColor: getColor('on-warning'),
    },
  ]
})
</script>
