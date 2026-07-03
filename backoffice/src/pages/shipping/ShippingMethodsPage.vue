<template>
  <div>
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
      <h1 class="page-title font-bold">Métodos de Envio</h1>
      <VaButton icon="add" @click="openModal()"> Novo Método </VaButton>
    </div>

    <!-- Filtros -->
    <VaCard class="mb-4">
      <VaCardContent>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <VaInput
            v-model="filters.search"
            placeholder="Pesquisar por nome ou transportadora..."
            clearable
            @update:modelValue="debouncedFetch"
          >
            <template #prependInner>
              <VaIcon name="search" size="small" color="secondary" />
            </template>
          </VaInput>

          <VaSelect
            v-model="filters.shipping_zone_id"
            :options="zoneOptions"
            placeholder="Filtrar por zona"
            clearable
            text-by="text"
            value-by="value"
            @update:modelValue="applyFilters"
          />

          <VaSelect
            v-model="filters.is_active"
            :options="statusOptions"
            placeholder="Todos os estados"
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
          <VaDataTable :items="store.methods" :columns="columns" hoverable>
            <!-- Transportadora -->
            <template #cell(carrier)="{ value }">
              <span class="font-bold text-[var(--va-secondary)]">{{ value }}</span>
            </template>

            <!-- Preço -->
            <template #cell(price)="{ value }">
              <span class="font-mono font-semibold">{{ formatCurrency(Number(value)) }}</span>
            </template>

            <!-- Limites Peso -->
            <template #cell(weight_limits)="{ rowData }">
              <span class="text-xs font-mono">
                {{ formatWeight(rowData.min_weight) }} - {{ formatWeight(rowData.max_weight) }}
              </span>
            </template>

            <!-- Zona de Envio -->
            <template #cell(shipping_zone)="{ rowData }">
              <span>{{ rowData.shipping_zone?.name ?? 'Global / Nenhuma' }}</span>
            </template>

            <!-- Estado -->
            <template #cell(is_active)="{ value }">
              <VaBadge :text="value ? 'Ativo' : 'Inativo'" :color="value ? 'success' : 'secondary'" />
            </template>

            <!-- Ações -->
            <template #cell(actions)="{ rowData }">
              <div class="flex gap-1">
                <VaButton
                  preset="plain"
                  icon="edit"
                  size="small"
                  color="primary"
                  title="Editar"
                  @click="openModal(rowData)"
                />
                <VaButton
                  preset="plain"
                  icon="delete"
                  size="small"
                  color="danger"
                  title="Eliminar"
                  @click="confirmDelete(rowData)"
                />
              </div>
            </template>
          </VaDataTable>

          <!-- Paginação -->
          <div v-if="store.pagination.last_page > 1" class="flex justify-between items-center mt-4">
            <span class="text-sm text-[var(--va-secondary)]">{{ store.pagination.total }} método(s)</span>
            <VaPagination
              v-model="store.pagination.current_page"
              :pages="store.pagination.last_page"
              :visible-pages="5"
              buttons-preset="secondary"
              active-page-color="primary"
              @update:modelValue="changePage"
            />
          </div>

          <div v-if="!store.loading && store.methods.length === 0" class="text-center py-8 text-[var(--va-secondary)]">
            <VaIcon name="local_shipping" size="48px" class="mb-2" />
            <p>Nenhum método de envio encontrado.</p>
          </div>
        </template>
      </VaCardContent>
    </VaCard>

    <!-- Modal Criar/Editar -->
    <VaModal
      v-model="modal.show"
      :title="modal.method ? 'Editar Método' : 'Novo Método'"
      hide-default-actions
      size="small"
    >
      <VaForm ref="methodForm">
        <VaAlert v-if="store.error" color="danger" class="mb-3" dense>
          <pre class="whitespace-pre-wrap text-sm">{{ store.error }}</pre>
        </VaAlert>

        <div class="flex flex-col gap-4">
          <VaInput v-model="modal.name" label="Nome do Serviço" placeholder="Ex: CTT Expresso" :rules="[required]" />
          <VaInput v-model="modal.carrier" label="Transportadora" placeholder="Ex: CTT" :rules="[required]" />

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <VaInput
              v-model.number="modal.price"
              type="number"
              label="Preço (€)"
              step="0.01"
              min="0"
              :rules="[required, minZero]"
            />
            <VaInput
              v-model.number="modal.min_weight"
              type="number"
              label="Peso Mín (kg)"
              step="0.001"
              min="0"
              :rules="[required, minZero]"
            />
            <VaInput
              v-model.number="modal.max_weight"
              type="number"
              label="Peso Máx (kg)"
              step="0.001"
              min="0"
              :rules="[required, minZero]"
            />
          </div>

          <VaInput
            v-model="modal.estimated_days"
            label="Prazo Estimado"
            placeholder="Ex: 24-48 horas"
            :rules="[required]"
          />

          <VaSelect
            v-model="modal.shipping_zone_id"
            :options="zoneOptions"
            label="Zona de Envio"
            placeholder="Selecione a zona"
            text-by="text"
            value-by="value"
            clearable
          />

          <VaSwitch v-model="modal.is_active" label="Método ativo" size="small" color="success" />
        </div>
      </VaForm>

      <template #footer>
        <div class="flex justify-end gap-2">
          <VaButton preset="secondary" @click="modal.show = false">Cancelar</VaButton>
          <VaButton :loading="store.saving" @click="handleSave">
            {{ modal.method ? 'Guardar' : 'Criar' }}
          </VaButton>
        </div>
      </template>
    </VaModal>

    <!-- Modal Eliminar -->
    <VaModal
      v-model="deleteModal.show"
      title="Eliminar Método de Envio"
      ok-text="Eliminar"
      cancel-text="Cancelar"
      @ok="executeDelete"
    >
      <p>
        Tens a certeza que queres eliminar o método <strong>{{ deleteModal.methodName }}</strong
        >?
      </p>
      <p class="text-sm text-[var(--va-secondary)] mt-2">
        Esta ação não pode ser desfeita se o método já tiver encomendas associadas.
      </p>
    </VaModal>
  </div>
</template>

<script lang="ts" setup>
import { reactive, onMounted, onBeforeUnmount, computed } from 'vue'
import { useForm, useToast } from 'vuestic-ui'
import { useShippingMethodsStore, type ShippingMethod } from '../../stores/shipping-methods-store'
import { useShippingZonesStore } from '../../stores/shipping-zones-store'

const store = useShippingMethodsStore()
const zonesStore = useShippingZonesStore()
const { validate } = useForm('methodForm')
const { init: toast } = useToast()

const columns = [
  { key: 'carrier', label: 'Transportadora', width: '120px' },
  { key: 'name', label: 'Nome do Serviço', sortable: true },
  { key: 'price', label: 'Preço', width: '100px', sortable: true },
  { key: 'weight_limits', label: 'Limites Peso', width: '180px' },
  { key: 'estimated_days', label: 'Prazo', width: '140px' },
  { key: 'shipping_zone', label: 'Zona de Envio', sortable: false },
  { key: 'is_active', label: 'Estado', width: '100px' },
  { key: 'actions', label: '', width: '100px', sortable: false },
]

const statusOptions = [
  { value: true, text: 'Ativos' },
  { value: false, text: 'Inativos' },
]

const zoneOptions = computed(() => {
  return zonesStore.zones.map((z) => ({
    value: z.id,
    text: z.name,
  }))
})

const required = (v: any) => !!v || v === 0 || 'Este campo é obrigatório'
const minZero = (v: any) => Number(v) >= 0 || 'O valor deve ser maior ou igual a zero'

// — Filtros —
const filters = reactive<Record<string, any>>({
  search: '',
  shipping_zone_id: null,
  is_active: null,
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
  if (filters.shipping_zone_id) params.shipping_zone_id = filters.shipping_zone_id
  if (filters.is_active !== null && filters.is_active !== undefined) params.is_active = filters.is_active
  store.fetchMethods(params)
}

function changePage(page: number) {
  store.setPage(page)
  applyFilters()
}

// — Helpers —
function formatCurrency(value: number): string {
  return new Intl.NumberFormat('pt-PT', { style: 'currency', currency: 'EUR' }).format(value)
}

function formatWeight(value: string | number): string {
  return `${Number(value).toFixed(3)} kg`
}

// — Modal Criar/Editar —
const modal = reactive({
  show: false,
  method: null as ShippingMethod | null,
  name: '',
  carrier: '',
  price: 0,
  min_weight: 0,
  max_weight: 5,
  estimated_days: '',
  shipping_zone_id: null as number | null,
  is_active: true,
})

function openModal(method?: ShippingMethod) {
  store.error = null
  modal.method = method ?? null
  modal.name = method?.name ?? ''
  modal.carrier = method?.carrier ?? ''
  modal.price = Number(method?.price ?? 0)
  modal.min_weight = Number(method?.min_weight ?? 0)
  modal.max_weight = Number(method?.max_weight ?? 5)
  modal.estimated_days = method?.estimated_days ?? ''
  modal.shipping_zone_id = method?.shipping_zone?.id ?? null
  modal.is_active = method?.is_active ?? true
  modal.show = true
}

async function handleSave() {
  if (!validate()) return

  const data = {
    name: modal.name,
    carrier: modal.carrier,
    price: modal.price,
    min_weight: modal.min_weight,
    max_weight: modal.max_weight,
    estimated_days: modal.estimated_days,
    shipping_zone_id: modal.shipping_zone_id,
    is_active: modal.is_active,
  }

  if (modal.method) {
    const result = await store.updateMethod(modal.method.id, data)
    if (result) {
      toast({ message: 'Método de envio atualizado.', color: 'success' })
      modal.show = false
      applyFilters()
    }
  } else {
    const result = await store.createMethod(data)
    if (result) {
      toast({ message: 'Método de envio criado.', color: 'success' })
      modal.show = false
      applyFilters()
    }
  }
}

// — Modal Eliminar —
const deleteModal = reactive({ show: false, methodId: 0, methodName: '' })

function confirmDelete(method: ShippingMethod) {
  deleteModal.methodId = method.id
  deleteModal.methodName = method.name
  deleteModal.show = true
}

async function executeDelete() {
  const success = await store.deleteMethod(deleteModal.methodId)
  if (success) {
    toast({ message: 'Método de envio eliminado.', color: 'success' })
    deleteModal.show = false
    applyFilters()
  } else {
    toast({ message: store.error || 'Erro ao eliminar método.', color: 'danger' })
  }
}

onMounted(() => {
  store.fetchMethods()
  zonesStore.fetchZones({ per_page: 100 })
})

onBeforeUnmount(() => {
  clearTimeout(debounceTimer)
})
</script>
