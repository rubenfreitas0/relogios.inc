<template>
  <div>
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
      <h1 class="page-title font-bold">Encomendas</h1>
      <div v-if="!store.loading" class="text-sm text-[var(--va-secondary)]">
        {{ store.pagination.total }} encomenda(s)
      </div>
    </div>

    <!-- Filtros -->
    <VaCard class="mb-4">
      <VaCardContent>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <VaInput
            v-model="filters.search"
            placeholder="Pesquisar por nº ou email..."
            clearable
            @update:modelValue="debouncedFetch"
          >
            <template #prependInner>
              <VaIcon name="search" size="small" color="secondary" />
            </template>
          </VaInput>

          <VaSelect
            v-model="filters.status"
            :options="statusOptions"
            placeholder="Todos os estados"
            clearable
            text-by="text"
            value-by="value"
            @update:modelValue="applyFilters"
          />

          <VaSelect
            v-model="filters.payment_status"
            :options="paymentStatusOptions"
            placeholder="Todos os pagamentos"
            clearable
            text-by="text"
            value-by="value"
            @update:modelValue="applyFilters"
          />
        </div>
      </VaCardContent>
    </VaCard>

    <!-- Tabela -->
    <VaCard>
      <VaCardContent>
        <div v-if="store.loading" class="flex justify-center py-12">
          <VaProgressCircle indeterminate size="large" />
        </div>

        <template v-else>
          <VaDataTable :items="store.orders" :columns="columns" hoverable clickable @row:click="handleRowClick">
            <!-- Nº Encomenda -->
            <template #cell(order_number)="{ value }">
              <span class="font-mono font-semibold text-[var(--va-primary)]">{{ value }}</span>
            </template>

            <!-- Cliente -->
            <template #cell(customer)="{ rowData }">
              <div>
                <span class="font-semibold"> {{ rowData.customer.firstname }} {{ rowData.customer.lastname }} </span>
                <div class="text-xs text-[var(--va-secondary)]">
                  {{ rowData.customer.email }}
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

          <!-- Paginação -->
          <div v-if="store.pagination.last_page > 1" class="flex justify-between items-center mt-4">
            <span class="text-sm text-[var(--va-secondary)]">
              Página {{ store.pagination.current_page }} de {{ store.pagination.last_page }}
            </span>
            <VaPagination
              v-model="store.pagination.current_page"
              :pages="store.pagination.last_page"
              :visible-pages="5"
              buttons-preset="secondary"
              active-page-color="primary"
              @update:modelValue="changePage"
            />
          </div>

          <!-- Sem resultados -->
          <div v-if="!store.loading && store.orders.length === 0" class="text-center py-8 text-[var(--va-secondary)]">
            <VaIcon name="receipt_long" size="48px" class="mb-2" />
            <p>Nenhuma encomenda encontrada.</p>
          </div>
        </template>
      </VaCardContent>
    </VaCard>
  </div>
</template>

<script lang="ts" setup>
import { reactive, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import { useOrdersStore, type Order } from '../../stores/orders-store'

const store = useOrdersStore()
const router = useRouter()

// — Configuração da tabela —
const columns = [
  { key: 'order_number', label: 'Nº Encomenda', width: '150px' },
  { key: 'customer', label: 'Cliente', sortable: false },
  { key: 'status', label: 'Estado', width: '180px' },
  { key: 'payment_status', label: 'Pagamento', width: '180px' },
  { key: 'total', label: 'Total', width: '120px' },
  { key: 'created_at', label: 'Data', width: '140px' },
  { key: 'actions', label: '', width: '60px', sortable: false },
]

const statusOptions = [
  { value: 'pending', text: 'A Aguardar Confirmação' },
  { value: 'processing', text: 'Em Processamento' },
  { value: 'shipped', text: 'Enviado' },
  { value: 'delivered', text: 'Entregue' },
  { value: 'cancelled', text: 'Cancelado' },
  { value: 'refunded', text: 'Reembolsado' },
]

const paymentStatusOptions = [
  { value: 'pending', text: 'A Aguardar Pagamento' },
  { value: 'paid', text: 'Pago' },
  { value: 'failed', text: 'Falhado' },
  { value: 'refunded', text: 'Reembolsado' },
]

// — Filtros —
const filters = reactive<Record<string, any>>({
  search: '',
  status: null,
  payment_status: null,
})

let debounceTimer: ReturnType<typeof setTimeout>
function debouncedFetch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => applyFilters(), 400)
}

function applyFilters() {
  store.setPage(1)
  const params: Record<string, unknown> = {}
  if (filters.search) params.search = filters.search
  if (filters.status) params.status = filters.status
  if (filters.payment_status) params.payment_status = filters.payment_status
  store.fetchOrders(params)
}

function changePage(page: number) {
  store.setPage(page)
  applyFilters()
}

// — Helpers —
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

function paymentColor(status: string): string {
  const colors: Record<string, string> = {
    pending: 'warning',
    paid: 'success',
    failed: 'danger',
    refunded: 'secondary',
  }
  return colors[status] ?? 'secondary'
}

function handleRowClick(event: { item: Order }) {
  router.push({ name: 'order-detail', params: { orderNumber: event.item.order_number } })
}

// — Init —
onMounted(() => {
  store.fetchOrders()
})

onBeforeUnmount(() => {
  clearTimeout(debounceTimer)
})
</script>
