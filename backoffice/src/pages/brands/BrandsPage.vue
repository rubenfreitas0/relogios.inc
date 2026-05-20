<template>
  <div>
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
      <h1 class="page-title font-bold">Marcas</h1>
      <VaButton icon="add" @click="openModal()">
        Nova Marca
      </VaButton>
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
          <VaDataTable :items="store.brands" :columns="columns" hoverable>
            <!-- Logo -->
            <template #cell(logo)="{ value }">
              <div class="w-10 h-10 rounded-lg overflow-hidden bg-[var(--va-background-element)] flex items-center justify-center">
                <img v-if="value" :src="value" alt="Logo" class="w-full h-full object-contain p-1" />
                <VaIcon v-else name="image" size="small" color="secondary" />
              </div>
            </template>

            <!-- Nome -->
            <template #cell(name)="{ value, rowData }">
              <div>
                <span class="font-semibold">{{ value }}</span>
                <div class="text-xs text-[var(--va-secondary)] font-mono">{{ rowData.slug }}</div>
              </div>
            </template>

            <!-- Produtos -->
            <template #cell(products_count)="{ value }">
              <span class="font-mono">{{ value ?? '—' }}</span>
            </template>

            <!-- Estado -->
            <template #cell(is_active)="{ value }">
              <VaBadge :text="value ? 'Ativa' : 'Inativa'" :color="value ? 'success' : 'secondary'" />
            </template>

            <!-- Ações -->
            <template #cell(actions)="{ rowData }">
              <div class="flex gap-1">
                <VaButton preset="plain" icon="edit" size="small" color="primary" title="Editar" @click="openModal(rowData)" />
                <VaButton
                  preset="plain"
                  icon="block"
                  size="small"
                  color="warning"
                  title="Desativar"
                  @click="confirmDeactivate(rowData)"
                  v-if="rowData.is_active"
                />
              </div>
            </template>
          </VaDataTable>

          <!-- Paginação -->
          <div class="flex justify-between items-center mt-4" v-if="store.pagination.last_page > 1">
            <span class="text-sm text-[var(--va-secondary)]">{{ store.pagination.total }} marca(s)</span>
            <VaPagination
              v-model="store.pagination.current_page"
              :pages="store.pagination.last_page"
              :visible-pages="5"
              buttons-preset="secondary"
              active-page-color="primary"
              @update:modelValue="changePage"
            />
          </div>

          <div v-if="!store.loading && store.brands.length === 0" class="text-center py-8 text-[var(--va-secondary)]">
            <VaIcon name="sell" size="48px" class="mb-2" />
            <p>Nenhuma marca encontrada.</p>
          </div>
        </template>
      </VaCardContent>
    </VaCard>

    <!-- Modal Criar/Editar -->
    <VaModal v-model="modal.show" :title="modal.brand ? 'Editar Marca' : 'Nova Marca'" hide-default-actions size="small">
      <VaForm ref="brandForm">
        <VaAlert v-if="store.error" color="danger" class="mb-3" dense>
          <pre class="whitespace-pre-wrap text-sm">{{ store.error }}</pre>
        </VaAlert>

        <div class="flex flex-col gap-4">
          <VaInput
            v-model="modal.name"
            label="Nome"
            placeholder="Ex: Casio"
            :rules="[required]"
          />

          <div>
            <label class="text-sm font-semibold text-[var(--va-secondary)] mb-1 block">Logo</label>
            <VaFileUpload
              v-model="modal.logoFile"
              type="single"
              file-types=".png,.jpg,.jpeg,.svg"
              upload-button-text="Escolher logo"
              :limitations="{ maxFileSize: 2 * 1024 * 1024 }"
            />
            <div v-if="modal.brand?.logo && !modal.logoFile?.length" class="mt-2">
              <img :src="modal.brand.logo" alt="Logo atual" class="w-16 h-16 object-contain rounded-lg bg-[var(--va-background-element)] p-1" />
              <span class="text-xs text-[var(--va-secondary)] ml-2">Logo atual</span>
            </div>
          </div>

          <VaSwitch v-model="modal.is_active" label="Marca ativa" size="small" color="success" />
        </div>
      </VaForm>

      <template #footer>
        <div class="flex justify-end gap-2">
          <VaButton preset="secondary" @click="modal.show = false">Cancelar</VaButton>
          <VaButton :loading="store.saving" @click="handleSave">
            {{ modal.brand ? 'Guardar' : 'Criar' }}
          </VaButton>
        </div>
      </template>
    </VaModal>

    <!-- Modal Desativar -->
    <VaModal v-model="deactivateModal.show" title="Desativar Marca" ok-text="Desativar" cancel-text="Cancelar" @ok="executeDeactivate">
      <p>Tens a certeza que queres desativar a marca <strong>{{ deactivateModal.brandName }}</strong>?</p>
      <p class="text-sm text-[var(--va-secondary)] mt-2">A marca ficará inativa mas os produtos associados não serão afetados.</p>
    </VaModal>
  </div>
</template>

<script lang="ts" setup>
import { reactive, onMounted } from 'vue'
import { useForm, useToast } from 'vuestic-ui'
import { useBrandsStore, type Brand } from '../../stores/brands-store'

const store = useBrandsStore()
const { validate } = useForm('brandForm')
const { init: toast } = useToast()

const columns = [
  { key: 'logo', label: '', width: '60px', sortable: false },
  { key: 'name', label: 'Nome', sortable: true },
  { key: 'products_count', label: 'Produtos', width: '100px' },
  { key: 'is_active', label: 'Estado', width: '100px' },
  { key: 'actions', label: '', width: '100px', sortable: false },
]

const statusOptions = [
  { value: true, text: 'Ativas' },
  { value: false, text: 'Inativas' },
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
  store.fetchBrands(params)
}

function changePage(page: number) {
  store.setPage(page)
  applyFilters()
}

// — Modal Criar/Editar —
const modal = reactive({
  show: false,
  brand: null as Brand | null,
  name: '',
  is_active: true,
  logoFile: [] as File[],
})

function openModal(brand?: Brand) {
  store.error = null
  modal.brand = brand ?? null
  modal.name = brand?.name ?? ''
  modal.is_active = brand?.is_active ?? true
  modal.logoFile = []
  modal.show = true
}

async function handleSave() {
  if (!validate()) return

  const fd = new FormData()
  fd.append('name', modal.name)
  fd.append('is_active', modal.is_active ? '1' : '0')
  if (modal.logoFile.length > 0) {
    fd.append('logo', modal.logoFile[0])
  }

  if (modal.brand) {
    const result = await store.updateBrand(modal.brand.id, fd)
    if (result) {
      toast({ message: 'Marca atualizada.', color: 'success' })
      modal.show = false
      applyFilters()
    }
  } else {
    if (!modal.logoFile.length) {
      store.error = 'O logo é obrigatório para novas marcas.'
      return
    }
    const result = await store.createBrand(fd)
    if (result) {
      toast({ message: 'Marca criada.', color: 'success' })
      modal.show = false
      applyFilters()
    }
  }
}

// — Modal Desativar —
const deactivateModal = reactive({ show: false, brandId: 0, brandName: '' })

function confirmDeactivate(brand: Brand) {
  deactivateModal.brandId = brand.id
  deactivateModal.brandName = brand.name
  deactivateModal.show = true
}

async function executeDeactivate() {
  const success = await store.deleteBrand(deactivateModal.brandId)
  if (success) {
    toast({ message: 'Marca desativada.', color: 'success' })
    deactivateModal.show = false
    applyFilters()
  } else {
    toast({ message: store.error || 'Erro.', color: 'danger' })
  }
}

onMounted(() => store.fetchBrands())
</script>
