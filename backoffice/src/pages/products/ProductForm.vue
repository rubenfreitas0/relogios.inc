<template>
  <div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
      <div>
        <VaButton preset="plain" icon="arrow_back" class="mb-2" :to="{ name: 'products' }"> Voltar </VaButton>
        <h1 class="page-title font-bold">{{ isEdit ? 'Editar Produto' : 'Novo Produto' }}</h1>
      </div>
      <div class="flex gap-2">
        <VaButton preset="secondary" :to="{ name: 'products' }"> Cancelar </VaButton>
        <VaButton :loading="store.saving" :disabled="store.saving" @click="handleSubmit">
          {{ isEdit ? 'Guardar Alterações' : 'Criar Produto' }}
        </VaButton>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loadingProduct" class="flex justify-center py-12">
      <VaProgressCircle indeterminate size="large" />
    </div>

    <!-- Formulário -->
    <VaForm v-else ref="formRef" @submit.prevent="handleSubmit">
      <!-- Erro global -->
      <VaAlert v-if="store.error" color="danger" class="mb-4" dense closeable @update:modelValue="store.error = null">
        <pre class="whitespace-pre-wrap text-sm">{{ store.error }}</pre>
      </VaAlert>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Coluna principal (2/3) -->
        <div class="lg:col-span-2 flex flex-col gap-4">
          <!-- Informações básicas -->
          <VaCard>
            <VaCardTitle class="font-semibold">Informações Gerais</VaCardTitle>
            <VaCardContent>
              <div class="flex flex-col gap-4">
                <VaInput
                  v-model="form.name"
                  label="Nome do produto"
                  placeholder="Ex: Casio MTP-1274D-7ADF"
                  :rules="[required]"
                />

                <VaInput
                  v-model="form.short_description"
                  label="Descrição curta"
                  placeholder="Breve resumo do produto"
                  maxlength="255"
                  counter
                />

                <VaTextarea
                  v-model="form.description"
                  label="Descrição completa"
                  placeholder="Descrição detalhada do produto..."
                  :min-rows="4"
                  :max-rows="10"
                  autosize
                />
              </div>
            </VaCardContent>
          </VaCard>

          <!-- Preço e Stock -->
          <VaCard>
            <VaCardTitle class="font-semibold">Preço e Stock</VaCardTitle>
            <VaCardContent>
              <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <VaInput
                  v-model="form.price"
                  label="Preço (€)"
                  type="number"
                  step="0.01"
                  min="0"
                  :rules="[required, minZero]"
                />

                <VaInput
                  v-model="form.discount_price"
                  label="Preço de Desconto (€)"
                  type="number"
                  step="0.01"
                  min="0"
                  placeholder="Opcional"
                  :rules="[lessThanPrice]"
                />

                <VaInput
                  v-model="form.stock"
                  label="Stock"
                  type="number"
                  min="0"
                  step="1"
                  :rules="[required, minZero]"
                />

                <VaInput
                  v-model="form.weight"
                  label="Peso (kg)"
                  type="number"
                  step="0.001"
                  min="0"
                  placeholder="Opcional"
                />
              </div>
            </VaCardContent>
          </VaCard>

          <!-- Features e In the Box -->
          <VaCard>
            <VaCardTitle class="font-semibold">Detalhes Adicionais</VaCardTitle>
            <VaCardContent>
              <div class="flex flex-col gap-4">
                <VaTextarea
                  v-model="form.features"
                  label="Características (features)"
                  placeholder="Uma característica por linha, ex:&#10;Movimento: Quartzo&#10;Resistência à água: 50m&#10;Diâmetro: 40mm"
                  :min-rows="3"
                  :max-rows="8"
                  autosize
                />
                <p class="text-xs text-[var(--va-secondary)] -mt-2">
                  Escreve uma característica por linha. Será guardado como texto.
                </p>

                <VaTextarea
                  v-model="form.in_the_box"
                  label="Na caixa (in the box)"
                  placeholder="Um item por linha, ex:&#10;Relógio&#10;Manual de instruções&#10;Certificado de garantia"
                  :min-rows="3"
                  :max-rows="6"
                  autosize
                />
                <p class="text-xs text-[var(--va-secondary)] -mt-2">Um item por linha. Será guardado como lista.</p>
              </div>
            </VaCardContent>
          </VaCard>

          <!-- Imagens -->
          <VaCard>
            <VaCardTitle class="font-semibold">Imagens</VaCardTitle>
            <VaCardContent>
              <!-- Imagens existentes (modo edição) -->
              <div v-if="existingImages.length > 0" class="mb-4">
                <p class="text-sm font-semibold mb-2 text-[var(--va-secondary)]">Imagens atuais</p>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                  <div
                    v-for="img in existingImages"
                    :key="img.id"
                    class="relative group rounded-xl overflow-hidden border-2 transition-all"
                    :class="
                      img.is_primary
                        ? 'border-[var(--va-primary)]'
                        : 'border-transparent hover:border-[var(--va-background-border)]'
                    "
                  >
                    <img :src="img.url" :alt="'Imagem ' + img.id" class="w-full aspect-square object-cover" />
                    <div
                      class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2"
                    >
                      <VaButton
                        v-if="!img.is_primary"
                        preset="plain"
                        icon="star"
                        size="small"
                        color="warning"
                        class="bg-white/90 rounded-full"
                        title="Definir como principal"
                        @click="setPrimaryImage(img.id)"
                      />
                      <VaButton
                        preset="plain"
                        icon="delete"
                        size="small"
                        color="danger"
                        class="bg-white/90 rounded-full"
                        title="Remover"
                        @click="markForRemoval(img.id)"
                      />
                    </div>
                    <VaBadge v-if="img.is_primary" text="Principal" color="primary" class="absolute top-2 left-2" />
                    <div
                      v-if="imagesToRemove.includes(img.id)"
                      class="absolute inset-0 bg-red-500/70 flex items-center justify-center"
                    >
                      <VaButton
                        preset="plain"
                        size="small"
                        class="bg-white rounded-full"
                        @click="unmarkRemoval(img.id)"
                      >
                        Anular
                      </VaButton>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Upload de novas imagens -->
              <div>
                <p class="text-sm font-semibold mb-2 text-[var(--va-secondary)]">
                  {{ isEdit ? 'Adicionar novas imagens' : 'Carregar imagens' }}
                </p>
                <VaFileUpload
                  v-model="newImageFiles"
                  type="gallery"
                  file-types=".png,.jpg,.jpeg"
                  upload-button-text="Escolher imagens"
                  :limitations="{ maxFiles: 10, maxFileSize: 2 * 1024 * 1024 }"
                />
                <p class="text-xs text-[var(--va-secondary)] mt-1">
                  PNG, JPG ou JPEG. Máx. 2 MB por imagem. Máx. 10 imagens no total.
                </p>
              </div>
            </VaCardContent>
          </VaCard>
        </div>

        <!-- Coluna lateral (1/3) -->
        <div class="flex flex-col gap-4">
          <!-- Classificação -->
          <VaCard>
            <VaCardTitle class="font-semibold">Classificação</VaCardTitle>
            <VaCardContent>
              <div class="flex flex-col gap-4">
                <VaSelect
                  v-model="form.brand_id"
                  :options="store.brandOptions"
                  label="Marca"
                  placeholder="Seleciona uma marca"
                  text-by="text"
                  value-by="value"
                  :rules="[required]"
                  searchable
                />

                <VaSelect
                  v-model="form.category_id"
                  :options="store.categoryOptions"
                  label="Categoria"
                  placeholder="Seleciona uma categoria"
                  text-by="text"
                  value-by="value"
                  :rules="[required]"
                  searchable
                />

                <VaSelect
                  v-model="form.gender"
                  :options="genderOptions"
                  label="Género"
                  placeholder="Seleciona o género"
                  text-by="text"
                  value-by="value"
                  :rules="[required]"
                />
              </div>
            </VaCardContent>
          </VaCard>

          <!-- Visibilidade -->
          <VaCard>
            <VaCardTitle class="font-semibold">Visibilidade</VaCardTitle>
            <VaCardContent>
              <div class="flex flex-col gap-3">
                <VaSwitch v-model="form.is_active" label="Produto ativo" size="small" color="success" />
                <p class="text-xs text-[var(--va-secondary)] -mt-1">Produtos inativos não aparecem na loja.</p>

                <VaSwitch v-model="form.is_featured" label="Produto em destaque" size="small" color="warning" />
                <p class="text-xs text-[var(--va-secondary)] -mt-1">
                  Aparece na secção de destaques da página inicial.
                </p>
              </div>
            </VaCardContent>
          </VaCard>

          <!-- Info do produto (só em edição) -->
          <VaCard v-if="isEdit && productData">
            <VaCardTitle class="font-semibold">Informações</VaCardTitle>
            <VaCardContent>
              <div class="flex flex-col gap-2 text-sm">
                <div class="flex justify-between">
                  <span class="text-[var(--va-secondary)]">ID</span>
                  <span class="font-mono">{{ productData.id }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-[var(--va-secondary)]">Slug</span>
                  <span class="font-mono text-xs">{{ productData.slug }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-[var(--va-secondary)]">Criado em</span>
                  <span>{{ formatDate(productData.created_at) }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-[var(--va-secondary)]">Atualizado em</span>
                  <span>{{ formatDate(productData.updated_at) }}</span>
                </div>
              </div>
            </VaCardContent>
          </VaCard>
        </div>
      </div>
    </VaForm>
  </div>
</template>

<script lang="ts" setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useForm, useToast } from 'vuestic-ui'
import { useProductsStore, type Product, type ProductImage } from '../../stores/products-store'

const store = useProductsStore()
const route = useRoute()
const router = useRouter()
const { validate } = useForm('formRef')
const { init: toast } = useToast()

const isEdit = computed(() => !!route.params.id)
const productId = computed(() => (isEdit.value ? Number(route.params.id) : null))
const loadingProduct = ref(false)
const productData = ref<Product | null>(null)

// — Formulário —
const form = reactive({
  name: '',
  short_description: '',
  description: '',
  price: '',
  discount_price: '',
  stock: 0,
  weight: '',
  brand_id: null as number | null,
  category_id: null as number | null,
  gender: '' as string,
  is_active: true,
  is_featured: false,
  features: '',
  in_the_box: '',
})

// — Imagens —
const existingImages = ref<ProductImage[]>([])
const imagesToRemove = ref<number[]>([])
const newImageFiles = ref<File[]>([])
const primaryImageId = ref<number | null>(null)

// — Options —
const genderOptions = [
  { value: 'masculino', text: 'Masculino' },
  { value: 'feminino', text: 'Feminino' },
  { value: 'unisexo', text: 'Unisexo' },
]

// — Validators —
const required = (v: any) => !!v || v === 0 || 'Este campo é obrigatório'
const minZero = (v: any) => Number(v) >= 0 || 'O valor não pode ser negativo'
const lessThanPrice = (v: any) =>
  !v || !form.price || Number(v) < Number(form.price) || 'O desconto deve ser menor que o preço original'

// — Helpers —
function formatDate(iso: string): string {
  return new Date(iso).toLocaleDateString('pt-PT', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

function setPrimaryImage(imageId: number) {
  primaryImageId.value = imageId
  existingImages.value = existingImages.value.map((img) => ({
    ...img,
    is_primary: img.id === imageId,
  }))
}

function markForRemoval(imageId: number) {
  if (!imagesToRemove.value.includes(imageId)) {
    imagesToRemove.value.push(imageId)
  }
}

function unmarkRemoval(imageId: number) {
  imagesToRemove.value = imagesToRemove.value.filter((id) => id !== imageId)
}

// — Populate form for edit —
function populateForm(product: Product) {
  form.name = product.name
  form.short_description = product.short_description || ''
  form.description = product.description || ''
  form.price = String(product.price)
  form.discount_price = product.discount_price ? String(product.discount_price) : ''
  form.stock = product.stock
  form.weight = product.weight ? String(product.weight) : ''
  form.brand_id = product.brand?.id ?? null
  form.category_id = product.category?.id ?? null
  form.gender = product.gender || ''
  form.is_active = product.is_active
  form.is_featured = product.is_featured
  form.features = product.features || ''

  // in_the_box é um array — converter para texto (uma linha por item)
  if (Array.isArray(product.in_the_box)) {
    form.in_the_box = product.in_the_box.join('\n')
  } else {
    form.in_the_box = ''
  }

  // Imagens existentes
  existingImages.value = product.images || []
  imagesToRemove.value = []
  primaryImageId.value = product.images?.find((img) => img.is_primary)?.id ?? null
}

// — Build FormData para submit —
function buildFormData(): FormData {
  const fd = new FormData()

  fd.append('name', form.name)
  fd.append('short_description', form.short_description || '')
  fd.append('description', form.description || '')
  fd.append('price', form.price)
  if (form.discount_price !== null && form.discount_price !== '') {
    fd.append('discount_price', form.discount_price)
  } else {
    fd.append('discount_price', '')
  }
  fd.append('stock', String(form.stock))
  if (form.weight) fd.append('weight', form.weight)
  if (form.brand_id) fd.append('brand_id', String(form.brand_id))
  if (form.category_id) fd.append('category_id', String(form.category_id))
  fd.append('gender', form.gender)
  fd.append('is_active', form.is_active ? '1' : '0')
  fd.append('is_featured', form.is_featured ? '1' : '0')

  if (form.features) {
    fd.append('features', form.features)
  }

  // in_the_box: converter linhas para JSON array
  if (form.in_the_box.trim()) {
    const items = form.in_the_box
      .split('\n')
      .map((s) => s.trim())
      .filter(Boolean)
    fd.append('in_the_box', JSON.stringify(items))
  }

  // Novas imagens
  if (newImageFiles.value.length > 0) {
    newImageFiles.value.forEach((file) => {
      fd.append('images[]', file)
    })
  }

  // Imagens a remover (só no update)
  if (isEdit.value && imagesToRemove.value.length > 0) {
    imagesToRemove.value.forEach((id) => {
      fd.append('remove_image_ids[]', String(id))
    })
  }

  // Imagem principal (só no update)
  if (isEdit.value && primaryImageId.value) {
    fd.append('primary_image_id', String(primaryImageId.value))
  }

  return fd
}

// — Submit —
async function handleSubmit() {
  if (!validate()) return

  const fd = buildFormData()

  if (isEdit.value && productId.value) {
    const result = await store.updateProduct(productId.value, fd)
    if (result) {
      toast({ message: 'Produto atualizado com sucesso.', color: 'success' })
      router.push({ name: 'products' })
    }
  } else {
    const result = await store.createProduct(fd)
    if (result) {
      toast({ message: 'Produto criado com sucesso.', color: 'success' })
      router.push({ name: 'products' })
    }
  }
}

// — Init —
onMounted(async () => {
  // Carregar options para selects
  await Promise.all([store.fetchBrandOptions(), store.fetchCategoryOptions()])

  // Modo edição: carregar produto
  if (isEdit.value && productId.value) {
    loadingProduct.value = true
    const product = await store.fetchProduct(productId.value)
    if (product) {
      productData.value = product
      populateForm(product)
    } else {
      toast({ message: 'Produto não encontrado.', color: 'danger' })
      router.push({ name: 'products' })
    }
    loadingProduct.value = false
  }
})
</script>
