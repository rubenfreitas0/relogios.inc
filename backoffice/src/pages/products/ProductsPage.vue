<template>
  <div>
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
      <h1 class="page-title font-bold">Produtos</h1>
      <VaButton icon="add" :to="{ name: 'product-create' }">
        Novo Produto
      </VaButton>
    </div>

    <!-- Filtros -->
    <VaCard class="mb-4">
      <VaCardContent>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
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
            v-model="filters.brand_id"
            :options="store.brandOptions"
            placeholder="Todas as marcas"
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

          <VaSelect
            v-model="filters.gender"
            :options="genderOptions"
            placeholder="Todos os géneros"
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
          <VaDataTable
            :items="store.products"
            :columns="columns"
            hoverable
            clickable
            @row:click="handleRowClick"
          >
            <!-- Imagem -->
            <template #cell(image)="{ rowData }">
              <div class="w-12 h-12 rounded-lg overflow-hidden bg-[var(--va-background-element)] flex items-center justify-center">
                <img
                  v-if="getPrimaryImage(rowData)"
                  :src="getPrimaryImage(rowData)"
                  :alt="rowData.name"
                  class="w-full h-full object-cover"
                />
                <VaIcon v-else name="image" size="small" color="secondary" />
              </div>
            </template>

            <!-- Nome -->
            <template #cell(name)="{ value, rowData }">
              <div>
                <span class="font-semibold">{{ value }}</span>
                <div v-if="rowData.brand" class="text-xs text-[var(--va-secondary)]">
                  {{ rowData.brand.name }}
                </div>
              </div>
            </template>

            <!-- Preço -->
            <template #cell(price)="{ value }">
              <span class="font-mono">{{ formatCurrency(value) }}</span>
            </template>

            <!-- Stock -->
            <template #cell(stock)="{ value, rowData }">
              <VaBadge
                :text="String(value)"
                :color="stockColor(value)"
                class="cursor-pointer"
                @click.stop="openStockModal(rowData)"
              />
            </template>

            <!-- Estado -->
            <template #cell(is_active)="{ value }">
              <VaBadge
                :text="value ? 'Ativo' : 'Inativo'"
                :color="value ? 'success' : 'secondary'"
              />
            </template>

            <!-- Destaque -->
            <template #cell(is_featured)="{ value }">
              <VaIcon
                :name="value ? 'star' : 'star_outline'"
                :color="value ? 'warning' : 'secondary'"
                size="small"
              />
            </template>

            <!-- Ações -->
            <template #cell(actions)="{ rowData }">
              <div class="flex gap-1" @click.stop>
                <VaButton
                  preset="plain"
                  icon="edit"
                  size="small"
                  color="primary"
                  :to="{ name: 'product-edit', params: { id: rowData.id } }"
                  title="Editar"
                />
                <VaButton
                  preset="plain"
                  :icon="rowData.is_active ? 'visibility_off' : 'visibility'"
                  size="small"
                  :color="rowData.is_active ? 'warning' : 'success'"
                  :title="rowData.is_active ? 'Desativar' : 'Ativar'"
                  @click="toggleActive(rowData)"
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
          <div class="flex justify-between items-center mt-4" v-if="store.pagination.last_page > 1">
            <span class="text-sm text-[var(--va-secondary)]">
              {{ store.pagination.total }} produto(s) encontrado(s)
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
          <div v-if="!store.loading && store.products.length === 0" class="text-center py-8 text-[var(--va-secondary)]">
            <VaIcon name="inventory_2" size="48px" class="mb-2" />
            <p>Nenhum produto encontrado.</p>
          </div>
        </template>
      </VaCardContent>
    </VaCard>

    <!-- Modal Stock -->
    <VaModal
      v-model="stockModal.show"
      title="Atualizar Stock"
      ok-text="Guardar"
      cancel-text="Cancelar"
      @ok="saveStock"
    >
      <p class="mb-4">
        Produto: <strong>{{ stockModal.productName }}</strong>
      </p>
      <VaInput
        v-model.number="stockModal.newStock"
        type="number"
        label="Novo stock"
        :min="0"
      />
    </VaModal>

    <!-- Modal Confirmação Delete -->
    <VaModal
      v-model="deleteModal.show"
      title="Eliminar Produto"
      ok-text="Eliminar"
      cancel-text="Cancelar"
      @ok="executeDelete"
    >
      <p>
        Tens a certeza que queres eliminar o produto
        <strong>{{ deleteModal.productName }}</strong>?
      </p>
      <p class="text-sm text-[var(--va-secondary)] mt-2">
        O produto será desativado (soft delete) e pode ser restaurado mais tarde.
      </p>
    </VaModal>
  </div>
</template>

<script lang="ts" setup>
import { reactive, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from 'vuestic-ui'
import { useProductsStore, type Product } from '../../stores/products-store'

const store = useProductsStore()
const router = useRouter()
const { init: toast } = useToast()

// — Configuração da tabela —
const columns = [
  { key: 'image', label: '', width: '60px', sortable: false },
  { key: 'name', label: 'Nome', sortable: true },
  { key: 'price', label: 'Preço', width: '120px', sortable: true },
  { key: 'stock', label: 'Stock', width: '80px', sortable: true },
  { key: 'is_active', label: 'Estado', width: '100px' },
  { key: 'is_featured', label: '⭐', width: '60px' },
  { key: 'actions', label: '', width: '120px', sortable: false },
]

const statusOptions = [
  { value: true, text: 'Ativos' },
  { value: false, text: 'Inativos' },
]

const genderOptions = [
  { value: 'masculino', text: 'Masculino' },
  { value: 'feminino', text: 'Feminino' },
  { value: 'unisexo', text: 'Unisexo' },
]

// — Filtros —
const filters = reactive<Record<string, any>>({
  search: '',
  brand_id: null,
  is_active: null,
  gender: null,
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
  if (filters.brand_id !== null && filters.brand_id !== undefined) params.brand_id = filters.brand_id
  if (filters.is_active !== null && filters.is_active !== undefined) params.is_active = filters.is_active
  if (filters.gender) params.gender = filters.gender
  store.fetchProducts(params)
}

function changePage(page: number) {
  store.setPage(page)
  applyFilters()
}

// — Helpers —
function getPrimaryImage(product: Product): string | null {
  const primary = product.images?.find((img) => img.is_primary)
  return primary?.url || product.images?.[0]?.url || null
}

function formatCurrency(value: string | number): string {
  return new Intl.NumberFormat('pt-PT', { style: 'currency', currency: 'EUR' }).format(Number(value))
}

function stockColor(stock: number): string {
  if (stock === 0) return 'danger'
  if (stock <= 5) return 'warning'
  return 'success'
}

function handleRowClick(event: { item: Product }) {
  router.push({ name: 'product-edit', params: { id: event.item.id } })
}

// — Stock Modal —
const stockModal = reactive({
  show: false,
  productId: 0,
  productName: '',
  newStock: 0,
})

function openStockModal(product: Product) {
  stockModal.productId = product.id
  stockModal.productName = product.name
  stockModal.newStock = product.stock
  stockModal.show = true
}

async function saveStock() {
  const success = await store.updateStock(stockModal.productId, stockModal.newStock)
  if (success) {
    toast({ message: 'Stock atualizado.', color: 'success' })
    stockModal.show = false
  } else {
    toast({ message: store.error || 'Erro ao atualizar stock.', color: 'danger' })
  }
}

// — Delete Modal —
const deleteModal = reactive({
  show: false,
  productId: 0,
  productName: '',
})

function confirmDelete(product: Product) {
  deleteModal.productId = product.id
  deleteModal.productName = product.name
  deleteModal.show = true
}

async function executeDelete() {
  const success = await store.deleteProduct(deleteModal.productId)
  if (success) {
    toast({ message: 'Produto eliminado.', color: 'success' })
    deleteModal.show = false
  } else {
    toast({ message: store.error || 'Erro ao eliminar.', color: 'danger' })
  }
}

// — Toggle Active —
async function toggleActive(product: Product) {
  const formData = new FormData()
  formData.append('is_active', product.is_active ? '0' : '1')
  const result = await store.updateProduct(product.id, formData)
  if (result) {
    toast({
      message: result.is_active ? 'Produto ativado.' : 'Produto desativado.',
      color: 'success',
    })
    applyFilters()
  } else {
    toast({ message: store.error || 'Erro ao alterar estado.', color: 'danger' })
  }
}

// — Init —
onMounted(() => {
  store.fetchProducts()
  store.fetchBrandOptions()
})

onBeforeUnmount(() => {
  clearTimeout(debounceTimer)
})
</script>
