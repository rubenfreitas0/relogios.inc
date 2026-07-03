<template>
  <VaCard>
    <VaCardTitle class="flex items-start justify-between">
      <h1 class="card-title text-secondary font-bold uppercase">Últimas Encomendas</h1>
      <VaButton preset="primary" size="small" to="/encomendas">Ver todas as encomendas</VaButton>
    </VaCardTitle>
    <VaCardContent>
      <div v-if="orders && orders.length > 0">
        <VaDataTable :items="orders" :columns="columns" hoverable clickable @row:click="handleRowClick">
          <!-- Nº Encomenda -->
          <template #cell(order_number)="{ value }">
            <span class="font-mono font-semibold text-[var(--va-primary)]">{{ value }}</span>
          </template>

          <!-- Cliente -->
          <template #cell(customer)="{ rowData }">
            <div>
              <span class="font-semibold">{{ rowData.customer_name }}</span>
              <div v-if="rowData.customer_email" class="text-xs text-[var(--va-secondary)]">
                {{ rowData.customer_email }}
              </div>
            </div>
          </template>

          <!-- Estado -->
          <template #cell(status)="{ rowData }">
            <VaBadge :text="rowData.status.label" :color="statusColor(rowData.status.value)" />
          </template>

          <!-- Pagamento -->
          <template #cell(payment_status)="{ rowData }">
            <VaBadge :text="rowData.payment_status.label" :color="paymentColor(rowData.payment_status.value)" />
          </template>

          <!-- Total -->
          <template #cell(total)="{ value }">
            <span class="font-mono font-semibold">{{ formatCurrency(Number(value)) }}</span>
          </template>

          <!-- Data -->
          <template #cell(created_at)="{ value }">
            <span class="text-sm">{{ formatDate(value) }}</span>
          </template>

          <!-- Ações -->
          <template #cell(actions)="{ rowData }">
            <div @click.stop>
              <VaButton
                preset="plain"
                icon="visibility"
                size="small"
                color="primary"
                title="Ver detalhe"
                :to="{ name: 'order-detail', params: { orderNumber: rowData.order_number } }"
              />
            </div>
          </template>
        </VaDataTable>
      </div>
      <div v-else class="p-4 flex justify-center items-center text-[var(--va-secondary)]">
        Sem encomendas registadas.
      </div>
    </VaCardContent>
  </VaCard>
</template>

<script setup lang="ts">
import { defineVaDataTableColumns } from 'vuestic-ui'
import { useRouter } from 'vue-router'
import type { DashboardStats } from '../../../../stores/dashboard-store'

const { orders } = defineProps<{
  orders: DashboardStats['latest_orders']
}>()

const router = useRouter()

const columns = defineVaDataTableColumns([
  { label: 'Nº Encomenda', key: 'order_number', width: '150px' },
  { label: 'Cliente', key: 'customer', sortable: false },
  { label: 'Estado', key: 'status', width: '180px' },
  { label: 'Pagamento', key: 'payment_status', width: '180px' },
  { label: 'Total', key: 'total', width: '120px' },
  { label: 'Data', key: 'created_at', width: '140px' },
  { label: '', key: 'actions', width: '60px', sortable: false },
])

const formatCurrency = (value: number): string => {
  return new Intl.NumberFormat('pt-PT', { style: 'currency', currency: 'EUR' }).format(value)
}

const formatDate = (iso: string): string => {
  return new Date(iso).toLocaleDateString('pt-PT', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  })
}

const statusColor = (status: string): string => {
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

const paymentColor = (status: string): string => {
  const colors: Record<string, string> = {
    pending: 'warning',
    paid: 'success',
    failed: 'danger',
    refunded: 'secondary',
  }
  return colors[status] ?? 'secondary'
}

const handleRowClick = (event: { item: (typeof orders)[0] }) => {
  router.push({ name: 'order-detail', params: { orderNumber: event.item.order_number } })
}
</script>
