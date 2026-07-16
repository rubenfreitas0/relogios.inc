<template>
  <div>
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
      <div>
        <h1 class="page-title font-bold">Categorias</h1>
        <p class="text-sm text-[var(--va-secondary)] mt-1">
          As categorias estão organizadas em dois grupos. Cada subcategoria pertence apenas a um deles.
        </p>
      </div>
      <VaButton icon="add" @click="openCreateModal()"> Nova Categoria </VaButton>
    </div>

    <!-- Filtros -->
    <VaCard class="mb-4">
      <VaCardContent>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <VaInput v-model="filters.search" placeholder="Pesquisar por nome..." clearable>
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
          />
        </div>
      </VaCardContent>
    </VaCard>

    <div v-if="store.loading" class="flex justify-center py-12">
      <VaProgressCircle indeterminate size="large" />
    </div>

    <template v-else>
      <!-- Uma secção por categoria-pai (grupo) -->
      <VaCard v-for="group in groups" :key="group.key" class="mb-6">
        <VaCardContent>
          <div class="flex items-center gap-3 mb-4">
            <VaIcon :name="group.icon" color="primary" />
            <h2 class="text-lg font-bold">{{ group.label }}</h2>
            <VaBadge :text="`${group.items.length}`" color="secondary" />
          </div>

          <VaDataTable :items="group.items" :columns="columns" hoverable>
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
            <template #cell(is_active)="{ rowData }">
              <VaBadge
                :text="rowData.is_active ? 'Ativa' : 'Inativa'"
                :color="rowData.is_active ? 'success' : 'secondary'"
              />
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
                  v-if="rowData.is_active"
                  preset="plain"
                  icon="block"
                  size="small"
                  color="warning"
                  title="Desativar"
                  @click="confirmDeactivate(rowData)"
                />
              </div>
            </template>
          </VaDataTable>

          <div v-if="group.items.length === 0" class="text-center py-8 text-[var(--va-secondary)]">
            <VaIcon name="category" size="48px" class="mb-2" />
            <p>Nenhuma subcategoria neste grupo.</p>
          </div>
        </VaCardContent>
      </VaCard>

      <div
        v-if="!store.loading && filteredCategories.length === 0"
        class="text-center py-8 text-[var(--va-secondary)]"
      >
        <VaIcon name="category" size="48px" class="mb-2" />
        <p>Nenhuma categoria encontrada.</p>
      </div>
    </template>

    <!-- Modal Editar Categoria -->
    <VaModal v-model="modal.show" title="Editar Categoria" hide-default-actions size="small">
      <VaForm ref="categoryForm">
        <VaAlert v-if="store.error" color="danger" class="mb-3" dense>
          <pre class="whitespace-pre-wrap text-sm">{{ store.error }}</pre>
        </VaAlert>

        <div class="flex flex-col gap-4">
          <VaInput v-model="modal.name" label="Nome" placeholder="Ex: Clássicos" :rules="[required]" />

          <div class="flex flex-col gap-1">
            <label class="text-xs text-[var(--va-secondary)] font-semibold">Grupo</label>
            <VaBadge :text="groupLabel(modal.category?.group)" color="info" />
          </div>

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

    <!-- Modal Nova Categoria -->
    <VaModal v-model="createModal.show" title="Nova Categoria" hide-default-actions size="small">
      <VaForm ref="createForm">
        <VaAlert v-if="store.error" color="danger" class="mb-3" dense>
          <pre class="whitespace-pre-wrap text-sm">{{ store.error }}</pre>
        </VaAlert>

        <div class="flex flex-col gap-4">
          <VaInput v-model="createModal.name" label="Nome" placeholder="Ex: Skeleton" :rules="[required]" />

          <VaSelect
            v-model="createModal.group"
            :options="groupOptions"
            label="Grupo"
            placeholder="Escolhe o grupo"
            text-by="text"
            value-by="value"
            :rules="[required]"
          />

          <VaSwitch v-model="createModal.is_active" label="Categoria ativa" size="small" color="success" />
        </div>
      </VaForm>

      <template #footer>
        <div class="flex justify-end gap-2">
          <VaButton preset="secondary" @click="createModal.show = false">Cancelar</VaButton>
          <VaButton :loading="store.saving" @click="handleCreate"> Criar </VaButton>
        </div>
      </template>
    </VaModal>

    <!-- Modal Desativar Categoria -->
    <VaModal
      v-model="deactivateModal.show"
      title="Desativar Categoria"
      ok-text="Desativar"
      cancel-text="Cancelar"
      @ok="executeDeactivate"
    >
      <p>
        Tens a certeza que queres desativar a categoria <strong>{{ deactivateModal.name }}</strong
        >?
      </p>
      <p class="text-sm text-[var(--va-secondary)] mt-2">
        A categoria fica inativa e deixa de aparecer nos filtros da loja. Os produtos associados não são afetados.
      </p>
    </VaModal>
  </div>
</template>

<script lang="ts" setup>
import { reactive, ref, computed, onMounted } from 'vue'
import { useForm, useToast } from 'vuestic-ui'
import { useCategoriesStore, type Category, type CategoryGroup } from '../../stores/categories-store'

const store = useCategoriesStore()
const { validate } = useForm('categoryForm')
const { validate: validateCreate } = useForm('createForm')
const { init: toast } = useToast()

const groupOptions = [
  { value: 'tipo', text: 'Tipo de Relógios' },
  { value: 'mecanismo', text: 'Mecanismo' },
]

const columns = [
  { key: 'name', label: 'Nome', sortable: true },
  { key: 'products_count', label: 'Produtos', width: '100px' },
  { key: 'is_active', label: 'Estado', width: '100px' },
  { key: 'actions', label: '', width: '80px', sortable: false },
]

const statusOptions = [
  { value: true, text: 'Ativas' },
  { value: false, text: 'Inativas' },
]

const required = (v: any) => !!v || 'Este campo é obrigatório'

const GROUP_LABELS: Record<string, string> = {
  tipo: 'Tipo de Relógios',
  mecanismo: 'Mecanismo',
}

function groupLabel(group: CategoryGroup): string {
  return group ? GROUP_LABELS[group] : 'Sem grupo'
}

// — Filtros (aplicados no cliente para manter o agrupamento) —
const filters = reactive<{ search: string; is_active: boolean | null }>({
  search: '',
  is_active: null,
})

const filteredCategories = computed<Category[]>(() => {
  const term = filters.search.trim().toLowerCase()
  return store.categories.filter((c) => {
    const matchesSearch = !term || c.name.toLowerCase().includes(term)
    const matchesStatus =
      filters.is_active === null || filters.is_active === undefined
        ? true
        : c.is_active === filters.is_active
    return matchesSearch && matchesStatus
  })
})

// Duas categorias-pai, cada uma com as suas subcategorias únicas
const groups = computed(() => {
  const order: { key: 'tipo' | 'mecanismo'; label: string; icon: string }[] = [
    { key: 'tipo', label: 'Tipo de Relógios', icon: 'watch' },
    { key: 'mecanismo', label: 'Mecanismo', icon: 'settings' },
  ]
  return order.map((g) => ({
    ...g,
    items: filteredCategories.value.filter((c) => c.group === g.key),
  }))
})

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
    fetchAll()
  }
}

// — Modal Nova Categoria —
const createModal = reactive({
  show: false,
  name: '',
  group: null as CategoryGroup,
  is_active: true,
})

function openCreateModal() {
  store.error = null
  createModal.name = ''
  createModal.group = null
  createModal.is_active = true
  createModal.show = true
}

async function handleCreate() {
  if (!validateCreate()) return

  const result = await store.createCategory({
    name: createModal.name,
    group: createModal.group,
    is_active: createModal.is_active,
  })
  if (result) {
    toast({ message: 'Categoria criada.', color: 'success' })
    createModal.show = false
    fetchAll()
  }
}

// — Desativar Categoria —
const deactivateModal = reactive({ show: false, id: 0, name: '' })

function confirmDeactivate(category: Category) {
  deactivateModal.id = category.id
  deactivateModal.name = category.name
  deactivateModal.show = true
}

async function executeDeactivate() {
  const ok = await store.deactivateCategory(deactivateModal.id)
  if (ok) {
    toast({ message: 'Categoria desativada.', color: 'success' })
    deactivateModal.show = false
    fetchAll()
  } else {
    toast({ message: store.error || 'Erro ao desativar.', color: 'danger' })
  }
}

// Carrega todas as categorias de uma vez para poder agrupar sem paginação
function fetchAll() {
  store.fetchCategories({ per_page: 100 })
}

onMounted(() => fetchAll())
</script>
