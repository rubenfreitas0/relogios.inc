<template>
  <div>
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
      <h1 class="page-title font-bold">Categorias</h1>
      <VaButton icon="add" @click="openModal()">
        Nova Categoria
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
          <VaDataTable :items="store.categories" :columns="columns" hoverable>
            <!-- Nome -->
            <template #cell(name)="{ value, rowData }">
              <div class="flex items-center gap-2">
                <span class="font-semibold">{{ value }}</span>
                <VaBadge v-if="store.isProtected(rowData)" text="Sistema" color="secondary" class="text-xs" />
              </div>
              <div class="text-xs text-[var(--va-secondary)] font-mono">{{ rowData.slug }}</div>
            </template>

            <!-- Produtos -->
            <template #cell(products_count)="{ value }">
              <span class="font-mono">{{ value ?? '—' }}</span>
            </template>

            <!-- Estado -->
            <template #cell(is_active)="{ value }">
              <VaBadge :text="value ? 'Ativa' : 'Inativa'" :color="value ? 'success' : 'secondary'" />
            </template>

            <!-- Data -->
            <template #cell(created_at)="{ value }">
              <span class="text-sm">{{ formatDate(value) }}</span>
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
                  :disabled="store.isProtected(rowData)"
                  @click="openModal(rowData)"
                />
                <VaButton
                  preset="plain"
                  icon="delete"
                  size="small"
                  color="danger"
                  title="Eliminar"
                  :disabled="store.isProtected(rowData)"
                  @click="confirmDelete(rowData)"
                />
              </div>
            </template>
          </VaDataTable>

          <!-- Paginação -->
          <div class="flex justify-between items-center mt-4" v-if="store.pagination.last_page > 1">
            <span class="text-sm text-[var(--va-secondary)]">{{ store.pagination.total }} categoria(s)</span>
            <VaPagination
              v-model="store.pagination.current_page"
              :pages="store.pagination.last_page"
              :visible-pages="5"
              buttons-preset="secondary"
              active-page-color="primary"
              @update:modelValue="changePage"
            />
          </div>

          <div v-if="!store.loading && store.categories.length === 0" class="text-center py-8 text-[var(--va-secondary)]">
            <VaIcon name="category" size="48px" class="mb-2" />
            <p>Nenhuma categoria encontrada.</p>
          </div>
        </template>
      </VaCardContent>
    </VaCard>

    <!-- Modal Criar/Editar -->
    <VaModal v-model="modal.show" :title="modal.category ? 'Editar Categoria' : 'Nova Categoria'" hide-default-actions size="small">
      <VaForm ref="categoryForm">
        <VaAlert v-if="store.error" color="danger" class="mb-3" dense>
          <pre class="whitespace-pre-wrap text-sm">{{ store.error }}</pre>
        </VaAlert>

        <div class="flex flex-col gap-4">
          <VaInput
            v-model="modal.name"
            label="Nome"
            placeholder="Ex: Relógios de Pulso"
            :rules="[required]"
          />

          <VaSwitch v-model="modal.is_active" label="Categoria ativa" size="small" color="success" />
        </div>
      </VaForm>

      <template #footer>
        <div class="flex justify-end gap-2">
          <VaButton preset="secondary" @click="modal.show = false">Cancelar</VaButton>
          <VaButton :loading="store.saving" @click="handleSave">
            {{ modal.category ? 'Guardar' : 'Criar' }}
          </VaButton>
        </div>
      </template>
    </VaModal>

    <!-- Modal Eliminar -->
    <VaModal v-model="deleteModal.show" title="Eliminar Categoria" ok-text="Eliminar" cancel-text="Cancelar" @ok="executeDelete">
      <p>Tens a certeza que queres eliminar a categoria <strong>{{ deleteModal.categoryName }}</strong>?</p>
      <p class="text-sm text-[var(--va-secondary)] mt-2">
        Produtos associados a esta categoria deixarão de ter categoria definida.
      </p>
    </VaModal>
  </div>
</template>

<script lang="ts" setup>
import { reactive, onMounted } from 'vue'
import { useForm, useToast } from 'vuestic-ui'
import { useCategoriesStore, type Category } from '../../stores/categories-store'

const store = useCategoriesStore()
const { validate } = useForm('categoryForm')
const { init: toast } = useToast()

const columns = [
  { key: 'name', label: 'Nome', sortable: true },
  { key: 'products_count', label: 'Produtos', width: '100px' },
  { key: 'is_active', label: 'Estado', width: '100px' },
  { key: 'created_at', label: 'Criada em', width: '140px' },
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
  store.fetchCategories(params)
}

function changePage(page: number) {
  store.setPage(page)
  applyFilters()
}

function formatDate(iso: string): string {
  return new Date(iso).toLocaleDateString('pt-PT', { day: '2-digit', month: 'short', year: 'numeric' })
}

// — Modal Criar/Editar —
const modal = reactive({
  show: false,
  category: null as Category | null,
  name: '',
  is_active: true,
})

function openModal(category?: Category) {
  store.error = null
  modal.category = category ?? null
  modal.name = category?.name ?? ''
  modal.is_active = category?.is_active ?? true
  modal.show = true
}

async function handleSave() {
  if (!validate()) return

  const data = { name: modal.name, is_active: modal.is_active }

  if (modal.category) {
    const result = await store.updateCategory(modal.category.id, data)
    if (result) {
      toast({ message: 'Categoria atualizada.', color: 'success' })
      modal.show = false
      applyFilters()
    }
  } else {
    const result = await store.createCategory(data)
    if (result) {
      toast({ message: 'Categoria criada.', color: 'success' })
      modal.show = false
      applyFilters()
    }
  }
}

// — Modal Eliminar —
const deleteModal = reactive({ show: false, categoryId: 0, categoryName: '' })

function confirmDelete(category: Category) {
  deleteModal.categoryId = category.id
  deleteModal.categoryName = category.name
  deleteModal.show = true
}

async function executeDelete() {
  const success = await store.deleteCategory(deleteModal.categoryId)
  if (success) {
    toast({ message: 'Categoria eliminada.', color: 'success' })
    deleteModal.show = false
    applyFilters()
  } else {
    toast({ message: store.error || 'Erro.', color: 'danger' })
  }
}

onMounted(() => store.fetchCategories())
</script>
