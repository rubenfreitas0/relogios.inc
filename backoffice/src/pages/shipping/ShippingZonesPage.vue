<template>
  <div>
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
      <h1 class="page-title font-bold">Zonas de Envio</h1>
      <VaButton icon="add" @click="openModal()"> Nova Zona </VaButton>
    </div>

    <!-- Filtros -->
    <VaCard class="mb-4">
      <VaCardContent>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <VaInput
            v-model="filters.search"
            placeholder="Pesquisar por nome..."
            clearable
            @update:modelValue="debouncedFetch"
          >
            <template #prependInner>
              <VaIcon name="search" size="small" color="secondary" />
            </template>
          </VaInput>

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
          <VaDataTable :items="store.zones" :columns="columns" hoverable>
            <!-- Nome -->
            <template #cell(name)="{ value }">
              <span class="font-semibold">{{ value }}</span>
            </template>

            <!-- Países -->
            <template #cell(countries)="{ rowData }">
              <div class="flex flex-wrap gap-1">
                <VaBadge
                  v-for="c in rowData.countries"
                  :key="c.id"
                  :text="c.country_code"
                  color="secondary"
                  class="text-xs"
                />
                <span
                  v-if="!rowData.countries || rowData.countries.length === 0"
                  class="text-xs text-[var(--va-secondary)]"
                  >—</span
                >
              </div>
            </template>

            <!-- Métodos -->
            <template #cell(shipping_methods_count)="{ value }">
              <span class="font-mono">{{ value ?? 0 }}</span>
            </template>

            <!-- Estado -->
            <template #cell(is_active)="{ value }">
              <VaBadge :text="value ? 'Ativa' : 'Inativa'" :color="value ? 'success' : 'secondary'" />
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
            <span class="text-sm text-[var(--va-secondary)]">{{ store.pagination.total }} zona(s)</span>
            <VaPagination
              v-model="store.pagination.current_page"
              :pages="store.pagination.last_page"
              :visible-pages="5"
              buttons-preset="secondary"
              active-page-color="primary"
              @update:modelValue="changePage"
            />
          </div>

          <div v-if="!store.loading && store.zones.length === 0" class="text-center py-8 text-[var(--va-secondary)]">
            <VaIcon name="public" size="48px" class="mb-2" />
            <p>Nenhuma zona de envio encontrada.</p>
          </div>
        </template>
      </VaCardContent>
    </VaCard>

    <!-- Modal Criar/Editar -->
    <VaModal v-model="modal.show" :title="modal.zone ? 'Editar Zona' : 'Nova Zona'" hide-default-actions size="small">
      <VaForm ref="zoneForm">
        <VaAlert v-if="store.error" color="danger" class="mb-3" dense>
          <pre class="whitespace-pre-wrap text-sm">{{ store.error }}</pre>
        </VaAlert>

        <div class="flex flex-col gap-4">
          <VaInput v-model="modal.name" label="Nome da Zona" placeholder="Ex: Europa Central" :rules="[required]" />

          <VaSelect
            v-model="modal.countries"
            :options="countriesOptions"
            label="Países"
            placeholder="Selecione os países"
            multiple
            text-by="text"
            value-by="value"
            searchable
          />

          <VaSwitch v-model="modal.is_active" label="Zona ativa" size="small" color="success" />
        </div>
      </VaForm>

      <template #footer>
        <div class="flex justify-end gap-2">
          <VaButton preset="secondary" @click="modal.show = false">Cancelar</VaButton>
          <VaButton :loading="store.saving" @click="handleSave">
            {{ modal.zone ? 'Guardar' : 'Criar' }}
          </VaButton>
        </div>
      </template>
    </VaModal>

    <!-- Modal Eliminar -->
    <VaModal
      v-model="deleteModal.show"
      title="Eliminar Zona de Envio"
      ok-text="Eliminar"
      cancel-text="Cancelar"
      @ok="executeDelete"
    >
      <p>
        Tens a certeza que queres eliminar a zona <strong>{{ deleteModal.zoneName }}</strong
        >?
      </p>
      <p class="text-sm text-[var(--va-secondary)] mt-2">
        Esta ação não pode ser desfeita se a zona não contiver métodos associados.
      </p>
    </VaModal>
  </div>
</template>

<script lang="ts" setup>
import { reactive, onMounted, onBeforeUnmount } from 'vue'
import { useForm, useToast } from 'vuestic-ui'
import { useShippingZonesStore, type ShippingZone } from '../../stores/shipping-zones-store'

const store = useShippingZonesStore()
const { validate } = useForm('zoneForm')
const { init: toast } = useToast()

const columns = [
  { key: 'name', label: 'Nome da Zona', sortable: true },
  { key: 'countries', label: 'Países', sortable: false },
  { key: 'shipping_methods_count', label: 'Métodos', width: '100px' },
  { key: 'is_active', label: 'Estado', width: '100px' },
  { key: 'actions', label: '', width: '100px', sortable: false },
]

const statusOptions = [
  { value: true, text: 'Ativas' },
  { value: false, text: 'Inativas' },
]

const countriesOptions = [
  { value: 'PT', text: 'Portugal (PT)' },
  { value: 'ES', text: 'Espanha (ES)' },
  { value: 'FR', text: 'França (FR)' },
  { value: 'DE', text: 'Alemanha (DE)' },
  { value: 'IT', text: 'Itália (IT)' },
  { value: 'NL', text: 'Países Baixos (NL)' },
  { value: 'BE', text: 'Bélgica (BE)' },
  { value: 'AT', text: 'Áustria (AT)' },
  { value: 'IE', text: 'Irlanda (IE)' },
  { value: 'LU', text: 'Luxemburgo (LU)' },
  { value: 'FI', text: 'Finlândia (FI)' },
  { value: 'GR', text: 'Grécia (GR)' },
  { value: 'SE', text: 'Suécia (SE)' },
  { value: 'DK', text: 'Dinamarca (DK)' },
  { value: 'PL', text: 'Polónia (PL)' },
  { value: 'GB', text: 'Reino Unido (GB)' },
  { value: 'US', text: 'Estados Unidos (US)' },
]

const required = (v: any) => !!v || 'Este campo é obrigatório'

// — Filtros —
const filters = reactive<Record<string, any>>({ search: '', is_active: null })

let debounceTimer: ReturnType<typeof setTimeout>
function debouncedFetch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => applyFilters(), 400)
}

function applyFilters() {
  store.setPage(1)
  const params: Record<string, unknown> = {}
  if (filters.search) params.search = filters.search
  if (filters.is_active !== null && filters.is_active !== undefined) params.is_active = filters.is_active
  store.fetchZones(params)
}

function changePage(page: number) {
  store.setPage(page)
  applyFilters()
}

// — Modal Criar/Editar —
const modal = reactive({
  show: false,
  zone: null as ShippingZone | null,
  name: '',
  is_active: true,
  countries: [] as string[],
})

function openModal(zone?: ShippingZone) {
  store.error = null
  modal.zone = zone ?? null
  modal.name = zone?.name ?? ''
  modal.is_active = zone?.is_active ?? true
  modal.countries = zone?.countries?.map((c) => c.country_code) ?? []
  modal.show = true
}

async function handleSave() {
  if (!validate()) return

  const data = {
    name: modal.name,
    is_active: modal.is_active,
    countries: modal.countries,
  }

  if (modal.zone) {
    const result = await store.updateZone(modal.zone.id, data)
    if (result) {
      toast({ message: 'Zona de envio atualizada.', color: 'success' })
      modal.show = false
      applyFilters()
    }
  } else {
    const result = await store.createZone(data)
    if (result) {
      toast({ message: 'Zona de envio criada.', color: 'success' })
      modal.show = false
      applyFilters()
    }
  }
}

// — Modal Eliminar —
const deleteModal = reactive({ show: false, zoneId: 0, zoneName: '' })

function confirmDelete(zone: ShippingZone) {
  deleteModal.zoneId = zone.id
  deleteModal.zoneName = zone.name
  deleteModal.show = true
}

async function executeDelete() {
  const success = await store.deleteZone(deleteModal.zoneId)
  if (success) {
    toast({ message: 'Zona de envio eliminada.', color: 'success' })
    deleteModal.show = false
    applyFilters()
  } else {
    toast({ message: store.error || 'Erro ao eliminar zona.', color: 'danger' })
  }
}

onMounted(() => store.fetchZones())

onBeforeUnmount(() => {
  clearTimeout(debounceTimer)
})
</script>
