import { defineStore } from 'pinia'
import { ref } from 'vue'
import { productsApi, brandsApi, categoriesApi } from '../services/api'

export interface ProductImage {
  id: number
  url: string
  is_primary: boolean
  sort_order: number
}

export interface Product {
  id: number
  name: string
  slug: string
  short_description: string | null
  description: string | null
  price: string
  discount_price: string | null
  stock: number
  weight?: string | null
  is_active: boolean
  is_featured: boolean
  gender: string
  features: string | null
  in_the_box: string[] | null
  brand: { id: number; name: string; slug: string } | null
  categories: { id: number; name: string; slug: string; group: string | null }[]
  // Compatibilidade: primeira categoria (pode vir do backend)
  category: { id: number; name: string; slug: string } | null
  images: ProductImage[]
  created_at: string
  updated_at: string
  deleted_at: string | null
}

export interface PaginationMeta {
  current_page: number
  last_page: number
  per_page: number
  total: number
}

export interface SelectOption {
  value: number
  text: string
  group?: string
}

const GROUP_LABELS: Record<string, string> = {
  tipo: 'Tipo de Relógios',
  mecanismo: 'Mecanismo',
}

export const useProductsStore = defineStore('products', () => {
  // — State —
  const products = ref<Product[]>([])
  const pagination = ref<PaginationMeta>({ current_page: 1, last_page: 1, per_page: 10, total: 0 })
  const loading = ref(false)
  const saving = ref(false)
  const error = ref<string | null>(null)

  // Options para selects
  const brandOptions = ref<SelectOption[]>([])
  const categoryOptions = ref<SelectOption[]>([])

  // — Actions —

  async function fetchProducts(params: Record<string, unknown> = {}) {
    loading.value = true
    error.value = null
    try {
      const response = await productsApi.list({
        per_page: pagination.value.per_page,
        page: pagination.value.current_page,
        ...params,
      })
      products.value = response.data.data
      pagination.value = {
        current_page: response.data.meta.current_page,
        last_page: response.data.meta.last_page,
        per_page: response.data.meta.per_page,
        total: response.data.meta.total,
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao carregar produtos.'
    } finally {
      loading.value = false
    }
  }

  async function fetchProduct(id: number): Promise<Product | null> {
    loading.value = true
    error.value = null
    try {
      const response = await productsApi.show(id)
      return response.data.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao carregar produto.'
      return null
    } finally {
      loading.value = false
    }
  }

  async function createProduct(formData: FormData): Promise<Product | null> {
    saving.value = true
    error.value = null
    try {
      const response = await productsApi.create(formData)
      return response.data.data
    } catch (err: any) {
      if (err.response?.status === 422) {
        const errors = err.response.data.errors
        error.value = Object.values(errors).flat().join('\n')
      } else {
        error.value = err.response?.data?.message || 'Erro ao criar produto.'
      }
      return null
    } finally {
      saving.value = false
    }
  }

  async function updateProduct(id: number, formData: FormData): Promise<Product | null> {
    saving.value = true
    error.value = null
    try {
      const response = await productsApi.update(id, formData)
      return response.data.data
    } catch (err: any) {
      if (err.response?.status === 422) {
        const errors = err.response.data.errors
        error.value = Object.values(errors).flat().join('\n')
      } else {
        error.value = err.response?.data?.message || 'Erro ao atualizar produto.'
      }
      return null
    } finally {
      saving.value = false
    }
  }

  async function deleteProduct(id: number): Promise<boolean> {
    try {
      await productsApi.destroy(id)
      products.value = products.value.filter((p) => p.id !== id)
      return true
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao eliminar produto.'
      return false
    }
  }

  async function updateStock(id: number, stock: number): Promise<boolean> {
    try {
      await productsApi.updateStock(id, stock)
      const product = products.value.find((p) => p.id === id)
      if (product) product.stock = stock
      return true
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao atualizar stock.'
      return false
    }
  }

  async function restoreProduct(id: number): Promise<boolean> {
    try {
      await productsApi.restore(id)
      return true
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao restaurar produto.'
      return false
    }
  }

  async function fetchBrandOptions() {
    try {
      const response = await brandsApi.list({ per_page: 100, is_active: true })
      brandOptions.value = response.data.data.map((b: any) => ({
        value: b.id,
        text: b.name,
      }))
    } catch {
      brandOptions.value = []
    }
  }

  async function fetchCategoryOptions() {
    try {
      const response = await categoriesApi.list({ per_page: 100, is_active: true })
      categoryOptions.value = response.data.data.map((c: any) => ({
        value: c.id,
        text: c.name,
        group: c.group ? GROUP_LABELS[c.group] ?? c.group : 'Outras',
      }))
    } catch {
      categoryOptions.value = []
    }
  }

  function setPage(page: number) {
    pagination.value.current_page = page
  }

  return {
    products,
    pagination,
    loading,
    saving,
    error,
    brandOptions,
    categoryOptions,
    fetchProducts,
    fetchProduct,
    createProduct,
    updateProduct,
    deleteProduct,
    updateStock,
    restoreProduct,
    fetchBrandOptions,
    fetchCategoryOptions,
    setPage,
  }
})
