<template>
  <div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
      <div>
        <VaButton preset="plain" icon="arrow_back" class="mb-2" :to="{ name: 'orders' }">
          Voltar
        </VaButton>
        <h1 class="page-title font-bold">
          Encomenda
          <span class="font-mono text-[var(--va-primary)]">#{{ orderNumber }}</span>
        </h1>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-12">
      <VaProgressCircle indeterminate size="large" />
    </div>

    <!-- Conteúdo -->
    <template v-else-if="order">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Coluna principal (2/3) -->
        <div class="lg:col-span-2 flex flex-col gap-4">
          <!-- Estado e Timeline -->
          <VaCard>
            <VaCardTitle class="font-semibold">Estado da Encomenda</VaCardTitle>
            <VaCardContent>
              <!-- Status atual -->
              <div class="flex items-center gap-3 mb-6">
                <VaBadge :text="order.status.label" :color="statusColor(order.status.value)" />
                <VaBadge :text="order.payment_status.label" :color="paymentColor(order.payment_status.value)" />
                <span v-if="order.tracking_number" class="text-sm text-[var(--va-secondary)]">
                  Tracking: <span class="font-mono">{{ order.tracking_number }}</span>
                </span>
              </div>

              <!-- Timeline visual -->
              <div class="flex items-center justify-between mb-6">
                <div
                  v-for="(step, i) in statusTimeline"
                  :key="step.value"
                  class="flex flex-col items-center flex-1"
                >
                  <div
                    class="w-10 h-10 rounded-full flex items-center justify-center text-white text-sm font-semibold transition-all"
                    :class="step.reached ? 'bg-[var(--va-primary)]' : 'bg-[var(--va-background-element)]'"
                  >
                    <VaIcon :name="step.icon" size="small" :color="step.reached ? 'white' : 'secondary'" />
                  </div>
                  <span class="text-xs mt-1 text-center" :class="step.reached ? 'font-semibold' : 'text-[var(--va-secondary)]'">
                    {{ step.label }}
                  </span>
                  <!-- Connector line -->
                  <div
                    v-if="i < statusTimeline.length - 1"
                    class="absolute"
                  />
                </div>
              </div>

              <!-- Formulário de atualização de estado -->
              <VaDivider class="mb-4" />
              <p class="text-sm font-semibold mb-3">Atualizar Estado</p>
              <VaAlert v-if="store.error" color="danger" class="mb-3" dense closeable @update:modelValue="store.error = null">
                <pre class="whitespace-pre-wrap text-sm">{{ store.error }}</pre>
              </VaAlert>
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <VaSelect
                  v-model="updateForm.status"
                  :options="statusOptions"
                  label="Novo estado"
                  text-by="text"
                  value-by="value"
                />
                <VaInput
                  v-model="updateForm.tracking_number"
                  label="Nº de rastreio"
                  placeholder="Opcional (obrigatório se Enviado)"
                  :disabled="updateForm.status !== 'shipped'"
                />
                <div class="flex items-end">
                  <VaButton
                    :loading="store.saving"
                    :disabled="store.saving || !updateForm.status"
                    @click="handleUpdateStatus"
                    class="w-full"
                  >
                    Atualizar
                  </VaButton>
                </div>
              </div>
            </VaCardContent>
          </VaCard>

          <!-- Itens da encomenda -->
          <VaCard>
            <VaCardTitle class="font-semibold">Itens ({{ order.items?.length ?? 0 }})</VaCardTitle>
            <VaCardContent>
              <div class="flex flex-col gap-3">
                <div
                  v-for="item in order.items"
                  :key="item.id"
                  class="flex items-center gap-4 p-3 rounded-xl bg-[var(--va-background-element)]"
                >
                  <!-- Imagem -->
                  <div class="w-14 h-14 rounded-lg overflow-hidden bg-white flex-shrink-0 flex items-center justify-center">
                    <img
                      v-if="item.product_image"
                      :src="item.product_image"
                      :alt="item.product_name"
                      class="w-full h-full object-cover"
                    />
                    <VaIcon v-else name="image" size="small" color="secondary" />
                  </div>
                  <!-- Info -->
                  <div class="flex-1 min-w-0">
                    <p class="font-semibold truncate">{{ item.product_name }}</p>
                    <p class="text-sm text-[var(--va-secondary)]">
                      {{ formatCurrency(item.unit_price) }} × {{ item.quantity }}
                    </p>
                  </div>
                  <!-- Total do item -->
                  <span class="font-mono font-semibold whitespace-nowrap">
                    {{ formatCurrency(item.item_total) }}
                  </span>
                </div>
              </div>

              <!-- Sumário de valores -->
              <VaDivider class="my-4" />
              <div class="flex flex-col gap-2 max-w-xs ml-auto">
                <div class="flex justify-between text-sm">
                  <span class="text-[var(--va-secondary)]">Subtotal</span>
                  <span class="font-mono">{{ formatCurrency(order.subtotal) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-[var(--va-secondary)]">Envio</span>
                  <span class="font-mono">{{ formatCurrency(order.shipping_cost) }}</span>
                </div>
                <div class="flex justify-between text-sm" v-if="order.tax_amount > 0">
                  <span class="text-[var(--va-secondary)]">IVA ({{ order.tax_rate }}%)</span>
                  <span class="font-mono">{{ formatCurrency(order.tax_amount) }}</span>
                </div>
                <VaDivider />
                <div class="flex justify-between font-semibold text-lg">
                  <span>Total</span>
                  <span class="font-mono">{{ formatCurrency(order.total) }}</span>
                </div>
              </div>
            </VaCardContent>
          </VaCard>
        </div>

        <!-- Coluna lateral (1/3) -->
        <div class="flex flex-col gap-4">
          <!-- Info do Cliente -->
          <VaCard>
            <VaCardTitle class="font-semibold">Cliente</VaCardTitle>
            <VaCardContent>
              <div class="flex flex-col gap-2 text-sm">
                <div>
                  <p class="font-semibold">{{ order.customer.firstname }} {{ order.customer.lastname }}</p>
                  <p class="text-[var(--va-secondary)]">{{ order.customer.email }}</p>
                </div>
                <div v-if="order.customer.phone">
                  <p class="text-xs text-[var(--va-secondary)] uppercase tracking-wide">Telefone</p>
                  <p>{{ order.customer.phone }}</p>
                </div>
                <div v-if="order.customer.nif">
                  <p class="text-xs text-[var(--va-secondary)] uppercase tracking-wide">NIF</p>
                  <p class="font-mono">{{ order.customer.nif }}</p>
                </div>
              </div>
            </VaCardContent>
          </VaCard>

          <!-- Morada de Envio -->
          <VaCard>
            <VaCardTitle class="font-semibold">Morada de Envio</VaCardTitle>
            <VaCardContent>
              <div class="text-sm">
                <p>{{ order.shipping_address.address_line1 }}</p>
                <p v-if="order.shipping_address.address_line2">{{ order.shipping_address.address_line2 }}</p>
                <p>{{ order.shipping_address.postal_code }} {{ order.shipping_address.city }}</p>
                <p class="font-semibold">{{ order.shipping_address.country }}</p>
              </div>
            </VaCardContent>
          </VaCard>

          <!-- Método de Envio -->
          <VaCard v-if="order.shipping_method">
            <VaCardTitle class="font-semibold">Envio</VaCardTitle>
            <VaCardContent>
              <div class="flex flex-col gap-1 text-sm">
                <p class="font-semibold">{{ order.shipping_method.name }}</p>
                <p class="text-[var(--va-secondary)]">{{ order.shipping_method.carrier }}</p>
                <p class="text-[var(--va-secondary)]">
                  Entrega estimada: {{ order.shipping_method.estimated_days }} dia(s)
                </p>
                <div v-if="order.tracking_number" class="mt-2">
                  <p class="text-xs text-[var(--va-secondary)] uppercase tracking-wide">Rastreio</p>
                  <p class="font-mono">{{ order.tracking_number }}</p>
                  <a
                    v-if="order.tracking_url"
                    :href="order.tracking_url"
                    target="_blank"
                    class="text-[var(--va-primary)] text-xs hover:underline"
                  >
                    Rastrear encomenda →
                  </a>
                </div>
              </div>
            </VaCardContent>
          </VaCard>

          <!-- Datas -->
          <VaCard>
            <VaCardTitle class="font-semibold">Datas</VaCardTitle>
            <VaCardContent>
              <div class="flex flex-col gap-2 text-sm">
                <div class="flex justify-between">
                  <span class="text-[var(--va-secondary)]">Criada</span>
                  <span>{{ formatDateTime(order.created_at) }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-[var(--va-secondary)]">Atualizada</span>
                  <span>{{ formatDateTime(order.updated_at) }}</span>
                </div>
                <div v-if="order.paid_at" class="flex justify-between">
                  <span class="text-[var(--va-secondary)]">Paga</span>
                  <span>{{ formatDateTime(order.paid_at) }}</span>
                </div>
              </div>
            </VaCardContent>
          </VaCard>
        </div>
      </div>
    </template>

    <!-- Não encontrada -->
    <VaCard v-else>
      <VaCardContent class="text-center py-8">
        <VaIcon name="error_outline" size="48px" color="danger" class="mb-2" />
        <p class="text-lg">Encomenda não encontrada.</p>
        <VaButton class="mt-4" :to="{ name: 'orders' }">Voltar às encomendas</VaButton>
      </VaCardContent>
    </VaCard>
  </div>
</template>

<script lang="ts" setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useToast } from 'vuestic-ui'
import { useOrdersStore, type Order } from '../../stores/orders-store'

const props = defineProps<{ orderNumber: string }>()

const store = useOrdersStore()
const { init: toast } = useToast()

const loading = ref(true)
const order = ref<Order | null>(null)

// — Status options e helpers —
const statusOptions = [
  { value: 'pending', text: 'A Aguardar Confirmação' },
  { value: 'processing', text: 'Em Processamento' },
  { value: 'shipped', text: 'Enviado' },
  { value: 'delivered', text: 'Entregue' },
  { value: 'cancelled', text: 'Cancelado' },
  { value: 'refunded', text: 'Reembolsado' },
]

const statusFlow = ['pending', 'processing', 'shipped', 'delivered']

const statusTimeline = computed(() => {
  const currentIndex = statusFlow.indexOf(order.value?.status.value ?? '')
  const isCancelled = order.value?.status.value === 'cancelled'
  const isRefunded = order.value?.status.value === 'refunded'

  const steps = [
    { value: 'pending', label: 'Pendente', icon: 'hourglass_empty' },
    { value: 'processing', label: 'Processamento', icon: 'settings' },
    { value: 'shipped', label: 'Enviado', icon: 'local_shipping' },
    { value: 'delivered', label: 'Entregue', icon: 'check_circle' },
  ]

  return steps.map((step, i) => ({
    ...step,
    reached: !isCancelled && !isRefunded && i <= currentIndex,
  }))
})

// — Update form —
const updateForm = reactive({
  status: '',
  tracking_number: '',
})

async function handleUpdateStatus() {
  if (!updateForm.status) return

  const data: { status: string; tracking_number?: string } = {
    status: updateForm.status,
  }

  if (updateForm.tracking_number) {
    data.tracking_number = updateForm.tracking_number
  }

  const result = await store.updateStatus(props.orderNumber, data)
  if (result) {
    order.value = result
    updateForm.status = ''
    updateForm.tracking_number = ''
    toast({ message: 'Estado atualizado com sucesso.', color: 'success' })
  }
}

// — Helpers —
function formatCurrency(value: number | string): string {
  return new Intl.NumberFormat('pt-PT', { style: 'currency', currency: 'EUR' }).format(Number(value))
}

function formatDateTime(iso: string): string {
  return new Date(iso).toLocaleDateString('pt-PT', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
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

// — Init —
onMounted(async () => {
  const result = await store.fetchOrder(props.orderNumber)
  order.value = result
  loading.value = false
})
</script>
