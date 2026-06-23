<template>
  <div>
    <h1 class="page-title font-bold">Dashboard</h1>
    <p class="text-[var(--va-secondary)] mb-6">Bem-vindo ao painel de administração.</p>

    <section class="flex flex-col gap-4">
      <!-- KPI Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <VaCard v-for="kpi in kpis" :key="kpi.title">
          <VaCardContent class="flex items-center gap-4">
            <VaIcon :name="kpi.icon" size="32px" :color="kpi.color" />
            <div>
              <p class="text-xs uppercase text-[var(--va-secondary)] font-semibold tracking-wide">{{ kpi.title }}</p>
              <p v-if="!loading" class="text-2xl font-bold">{{ kpi.value }}</p>
              <VaSkeletonGroup v-else>
                <VaSkeleton variant="text" :lines="1" />
              </VaSkeletonGroup>
            </div>
          </VaCardContent>
        </VaCard>
      </div>

      <!-- Últimas encomendas -->
      <VaCard>
        <VaCardTitle class="font-semibold">Últimas Encomendas</VaCardTitle>
        <VaCardContent>
          <div v-if="loading" class="flex justify-center py-8">
            <VaProgressCircle indeterminate />
          </div>
          <VaDataTable
            v-else
            :items="latestOrders"
            :columns="orderColumns"
            :per-page="10"
            no-data-html="Sem encomendas"
          >
            <template #cell(status)="{ value }">
              <VaBadge :text="value.label" :color="statusColor(value.value)" />
            </template>
            <template #cell(total)="{ value }">
              {{ formatCurrency(value) }}
            </template>
            <template #cell(created_at)="{ value }">
              {{ formatDate(value) }}
            </template>
          </VaDataTable>
        </VaCardContent>
      </VaCard>
    </section>
  </div>
</template>

<script lang="ts" setup>
import { ref, onMounted, computed } from 'vue'
import { dashboardApi } from '../../../services/api'

const loading = ref(true)
const stats = ref<any>(null)

const orderColumns = [
  { key: 'order_number', label: 'Nº Encomenda' },
  { key: 'customer_name', label: 'Cliente' },
  { key: 'status', label: 'Estado' },
  { key: 'total', label: 'Total' },
  { key: 'created_at', label: 'Data' },
]

const kpis = computed(() => [
  {
    title: 'Revenue (Mês)',
    value: stats.value ? formatCurrency(stats.value.revenue.this_month) : '—',
    icon: 'trending_up',
    color: 'success',
  },
  {
    title: 'Encomendas (Mês)',
    value: stats.value?.orders.this_month ?? '—',
    icon: 'shopping_cart',
    color: 'primary',
  },
  {
    title: 'Produtos Ativos',
    value: stats.value?.products.active ?? '—',
    icon: 'watch',
    color: 'info',
  },
  {
    title: 'Sem Stock',
    value: stats.value?.products.out_of_stock ?? '—',
    icon: 'warning',
    color: 'danger',
  },
])

const latestOrders = computed(() => stats.value?.latest_orders ?? [])

function formatCurrency(value: number): string {
  return new Intl.NumberFormat('pt-PT', { style: 'currency', currency: 'EUR' }).format(value)
}

function formatDate(iso: string): string {
  return new Date(iso).toLocaleDateString('pt-PT', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  })
}

function statusColor(status: string): string {
  const colors: Record<string, string> = {
    pending: 'warning',
    processing: 'info',
    shipped: '#7c3aed',
    delivered: 'success',
    cancelled: 'danger',
    refunded: 'secondary',
  }
  return colors[status] ?? 'secondary'
}

onMounted(async () => {
  try {
    const response = await dashboardApi.stats()
    stats.value = response.data
  } catch (err) {
    console.error('Erro ao carregar dashboard:', err)
  } finally {
    loading.value = false
  }
})
</script>
