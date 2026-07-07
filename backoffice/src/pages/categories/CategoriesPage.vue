<template>
  <div>
    <!-- VISTA PRINCIPAL: LISTA DE CATEGORIAS -->
    <div v-if="!selectedCategory">
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <h1 class="page-title font-bold">Categorias</h1>
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
                    icon="list"
                    size="small"
                    color="info"
                    title="Gerir Itens"
                    @click="selectCategory(rowData)"
                  />
                  <VaButton
                    preset="plain"
                    icon="edit"
                    size="small"
                    color="primary"
                    title="Editar"
                    @click="openModal(rowData)"
                  />
                </div>
              </template>
            </VaDataTable>

            <!-- Paginação -->
            <div v-if="store.pagination.last_page > 1" class="flex justify-between items-center mt-4">
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

            <div
              v-if="!store.loading && store.categories.length === 0"
              class="text-center py-8 text-[var(--va-secondary)]"
            >
              <VaIcon name="category" size="48px" class="mb-2" />
              <p>Nenhuma categoria encontrada.</p>
            </div>
          </template>
        </VaCardContent>
      </VaCard>

      <!-- Modal Editar Categoria -->
      <VaModal v-model="modal.show" title="Editar Categoria" hide-default-actions size="small">
        <VaForm ref="categoryForm">
          <VaAlert v-if="store.error" color="danger" class="mb-3" dense>
            <pre class="whitespace-pre-wrap text-sm">{{ store.error }}</pre>
          </VaAlert>

          <div class="flex flex-col gap-4">
            <VaInput v-model="modal.name" label="Nome" placeholder="Ex: Relógios de Pulso" :rules="[required]" />

            <VaSwitch v-model="modal.is_active" label="Categoria ativa" size="small" color="success" />
          </div>
        </VaForm>

        <template #footer>
          <div class="flex justify-end gap-2">
            <VaButton preset="secondary" @click="modal.show = false">Cancelar</VaButton>
            <VaButton :loading="store.saving" @click="handleSave"> Guardar </VaButton>
          </div>
        </template>
      </VaModal>
    </div>

    <!-- VISTA SECUNDÁRIA: GESTÃO DE SUB-ITENS -->
    <div v-else>
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div class="flex flex-col">
          <VaButton preset="plain" icon="arrow_back" class="self-start mb-2" @click="selectedCategory = null">
            Voltar para Categorias
          </VaButton>
          <h1 class="page-title font-bold">Gerir Itens: {{ selectedCategory.name }}</h1>
        </div>
        <VaButton icon="add" @click="openSubItemModal()"> Novo Item </VaButton>
      </div>

      <!-- Tabela de Sub-itens -->
      <VaCard>
        <VaCardContent>
          <VaDataTable :items="subItems" :columns="subItemColumns" hoverable>
            <!-- Preview da Cor (Apenas para cor) -->
            <template #cell(hex)="{ value }">
              <div class="flex items-center gap-2">
                <span
                  class="w-6 h-6 rounded-full border border-[var(--va-background-border)] inline-block shadow-sm"
                  :style="{ backgroundColor: value }"
                ></span>
                <span class="font-mono text-sm">{{ value }}</span>
              </div>
            </template>

            <!-- Preço Mínimo / Máximo (Apenas para gama-de-preco) -->
            <template #cell(min)="{ value }">
              <span class="font-mono text-sm">{{ formatCurrency(value) }}</span>
            </template>
            <template #cell(max)="{ value }">
              <span class="font-mono text-sm">{{ formatCurrency(value) }}</span>
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
                  @click="openSubItemModal(rowData)"
                />
                <VaButton
                  preset="plain"
                  icon="delete"
                  size="small"
                  color="danger"
                  title="Eliminar"
                  @click="confirmDeleteSubItem(rowData)"
                />
              </div>
            </template>
          </VaDataTable>

          <div v-if="subItems.length === 0" class="text-center py-8 text-[var(--va-secondary)]">
            <VaIcon name="list" size="48px" class="mb-2" />
            <p>Nenhum item configurado para esta categoria.</p>
          </div>
        </VaCardContent>
      </VaCard>

      <!-- Modal Criar/Editar Sub-item -->
      <VaModal
        v-model="subItemModal.show"
        :title="subItemModal.item ? 'Editar Item' : 'Novo Item'"
        hide-default-actions
        size="small"
      >
        <VaForm ref="subItemFormRef">
          <div class="flex flex-col gap-4">
            <!-- Campo Nome/Label -->
            <VaInput
              v-model="subItemModal.name"
              label="Nome / Label"
              placeholder="Ex: Azul, Homens, Até €100"
              :rules="[required]"
            />

            <!-- Campos Específicos para Cor -->
            <template v-if="selectedCategory.slug === 'cor'">
              <div class="flex flex-col gap-1">
                <label class="text-xs text-[var(--va-secondary)] font-semibold">Código Hexadecimal</label>
                <div class="flex gap-2 items-center">
                  <VaInput
                    v-model="subItemModal.hex"
                    placeholder="Ex: #ffffff"
                    :rules="[required, hexRule]"
                    class="flex-grow"
                  />
                  <input
                    v-model="subItemModal.hex"
                    type="color"
                    class="w-10 h-10 border border-[var(--va-background-border)] rounded cursor-pointer p-0"
                  />
                </div>
              </div>
            </template>

            <!-- Campos Específicos para Gama de Preço -->
            <template v-if="selectedCategory.slug === 'gama-de-preco'">
              <VaInput
                v-model.number="subItemModal.min"
                label="Preço Mínimo (€)"
                type="number"
                min="0"
                :rules="[required, minZero]"
              />
              <VaInput
                v-model.number="subItemModal.max"
                label="Preço Máximo (€)"
                type="number"
                min="0"
                :rules="[required, minZero]"
              />
            </template>

            <!-- Campos Específicos para Sexo, Género ou Geral -->
            <template v-if="selectedCategory.slug !== 'cor' && selectedCategory.slug !== 'gama-de-preco'">
              <VaInput
                v-model="subItemModal.slug"
                label="Slug"
                placeholder="Ex: homens, classicos, automaticos"
                :rules="[required]"
              />
            </template>

            <!-- Estado -->
            <VaSwitch v-model="subItemModal.is_active" label="Item ativo" size="small" color="success" />
          </div>
        </VaForm>

        <template #footer>
          <div class="flex justify-end gap-2">
            <VaButton preset="secondary" @click="subItemModal.show = false">Cancelar</VaButton>
            <VaButton @click="handleSaveSubItem">
              {{ subItemModal.item ? 'Guardar' : 'Criar' }}
            </VaButton>
          </div>
        </template>
      </VaModal>

      <!-- Modal Eliminar Sub-item -->
      <VaModal
        v-model="deleteSubItemModal.show"
        title="Eliminar Item"
        ok-text="Eliminar"
        cancel-text="Cancelar"
        @ok="executeDeleteSubItem"
      >
        <p>
          Tens a certeza que queres eliminar o item <strong>{{ deleteSubItemModal.itemName }}</strong
          >?
        </p>
      </VaModal>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { reactive, ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import { useForm, useToast } from 'vuestic-ui'
import { useCategoriesStore, type Category } from '../../stores/categories-store'
import { useCategoryItemsStore, type CategoryItem } from '../../stores/category-items-store'

const store = useCategoriesStore()
const itemsStore = useCategoryItemsStore()
const { validate } = useForm('categoryForm')
const { validate: validateSubItem } = useForm('subItemFormRef')
const { init: toast } = useToast()

const columns = [
  { key: 'name', label: 'Nome', sortable: true },
  { key: 'products_count', label: 'Produtos', width: '100px' },
  { key: 'is_active', label: 'Estado', width: '100px' },
  { key: 'created_at', label: 'Criada em', width: '140px' },
  { key: 'actions', label: '', width: '140px', sortable: false },
]

const statusOptions = [
  { value: true, text: 'Ativas' },
  { value: false, text: 'Inativas' },
]

const required = (v: any) => !!v || 'Este campo é obrigatório'
const hexRule = (v: string) => /^#[0-9A-F]{6}$/i.test(v) || 'Código hexadecimal inválido (ex: #ffffff)'
const minZero = (v: any) => Number(v) >= 0 || 'O valor deve ser maior ou igual a zero'

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

// — Modal Editar Categoria —
const modal = reactive({
  show: false,
  category: null as Category | null,
  name: '',
  is_active: true,
})

function openModal(category: Category) {
  store.error = null
  modal.category = category
  modal.name = category.name
  modal.is_active = category.is_active
  modal.show = true
}

async function handleSave() {
  if (!validate()) return
  if (!modal.category) return

  const data = { name: modal.name, is_active: modal.is_active }

  const result = await store.updateCategory(modal.category.id, data)
  if (result) {
    toast({ message: 'Categoria atualizada.', color: 'success' })
    modal.show = false
    applyFilters()
  }
}

// — Gestão de Sub-itens (Categoria Selecionada) —
const selectedCategory = ref<Category | null>(null)

function selectCategory(category: Category) {
  selectedCategory.value = category
}

const subItems = computed(() => {
  if (!selectedCategory.value) return []
  return itemsStore.getItemsBySlug(selectedCategory.value.slug)
})

const subItemColumns = computed(() => {
  if (!selectedCategory.value) return []
  if (selectedCategory.value.slug === 'cor') {
    return [
      { key: 'name', label: 'Nome', sortable: true },
      { key: 'hex', label: 'Cor / Hex' },
      { key: 'is_active', label: 'Estado', width: '100px' },
      { key: 'actions', label: '', width: '100px', sortable: false },
    ]
  }
  if (selectedCategory.value.slug === 'gama-de-preco') {
    return [
      { key: 'name', label: 'Label', sortable: true },
      { key: 'min', label: 'Preço Mínimo', sortable: true },
      { key: 'max', label: 'Preço Máximo', sortable: true },
      { key: 'is_active', label: 'Estado', width: '100px' },
      { key: 'actions', label: '', width: '100px', sortable: false },
    ]
  }
  return [
    { key: 'name', label: 'Nome', sortable: true },
    { key: 'slug', label: 'Slug', sortable: true },
    { key: 'is_active', label: 'Estado', width: '100px' },
    { key: 'actions', label: '', width: '100px', sortable: false },
  ]
})

function formatCurrency(value: string | number | undefined): string {
  if (value === undefined || value === null || value === '') return '—'
  return new Intl.NumberFormat('pt-PT', { style: 'currency', currency: 'EUR' }).format(Number(value))
}

// — Modal Criar/Editar Sub-item —
const subItemModal = reactive({
  show: false,
  item: null as CategoryItem | null,
  name: '',
  slug: '',
  hex: '#1a1a1a',
  min: 0,
  max: 100,
  is_active: true,
})

function openSubItemModal(item?: CategoryItem) {
  subItemModal.item = item ?? null
  subItemModal.name = item?.name ?? ''
  subItemModal.slug = item?.slug ?? ''
  subItemModal.hex = item?.hex ?? '#1a1a1a'
  subItemModal.min = item?.min ?? 0
  subItemModal.max = item?.max ?? 100
  subItemModal.is_active = item?.is_active ?? true
  subItemModal.show = true
}

// Geração automática de slug a partir do nome
watch(
  () => subItemModal.name,
  (newName) => {
    if (
      !subItemModal.item &&
      selectedCategory.value &&
      !['cor', 'gama-de-preco'].includes(selectedCategory.value.slug)
    ) {
      subItemModal.slug = newName
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/(^-|-$)+/g, '')
    }
  },
)

function handleSaveSubItem() {
  if (!validateSubItem()) return
  if (!selectedCategory.value) return

  const slug = selectedCategory.value.slug

  const data: Omit<CategoryItem, 'id'> = {
    name: subItemModal.name,
    is_active: subItemModal.is_active,
  }

  if (slug === 'cor') {
    data.hex = subItemModal.hex
  } else if (slug === 'gama-de-preco') {
    data.min = subItemModal.min
    data.max = subItemModal.max
  } else {
    data.slug = subItemModal.slug
  }

  if (subItemModal.item) {
    itemsStore.updateItem(slug, subItemModal.item.id, data)
    toast({ message: 'Item atualizado com sucesso.', color: 'success' })
  } else {
    itemsStore.createItem(slug, data)
    toast({ message: 'Item criado com sucesso.', color: 'success' })
  }
  subItemModal.show = false
}

// — Modal Eliminar Sub-item —
const deleteSubItemModal = reactive({
  show: false,
  itemId: 0,
  itemName: '',
})

function confirmDeleteSubItem(item: CategoryItem) {
  deleteSubItemModal.itemId = item.id
  deleteSubItemModal.itemName = item.name
  deleteSubItemModal.show = true
}

function executeDeleteSubItem() {
  if (!selectedCategory.value) return
  const success = itemsStore.deleteItem(selectedCategory.value.slug, deleteSubItemModal.itemId)
  if (success) {
    toast({ message: 'Item eliminado.', color: 'success' })
    deleteSubItemModal.show = false
  } else {
    toast({ message: 'Erro ao eliminar item.', color: 'danger' })
  }
}

onMounted(() => store.fetchCategories())

onBeforeUnmount(() => {
  clearTimeout(debounceTimer)
})
</script>
