import { defineStore } from 'pinia'
import { ref } from 'vue'
import { categoriesApi } from '../services/api'

export interface Category {
  id: number
  name: string
  slug: string
  is_active: boolean
  products_count?: number
  created_at: string
  updated_at: string
}

export interface PaginationMeta {
  current_page: number
  last_page: number
  per_page: number
  total: number
}

const PROTECTED_SLUGS = ['gama-de-preco', 'cor', 'sexo', 'genero']

export const useCategoriesStore = defineStore('categories', () => {
  const categories = ref<Category[]>([])
  const pagination = ref<PaginationMeta>({ current_page: 1, last_page: 1, per_page: 15, total: 0 })
  const loading = ref(false)
  const saving = ref(false)
  const error = ref<string | null>(null)

  function isProtected(category: Category): boolean {
    return PROTECTED_SLUGS.includes(category.slug)
  }

  async function fetchCategories(params: Record<string, unknown> = {}) {
    loading.value = true
    error.value = null
    try {
      const response = await categoriesApi.list({
        per_page: pagination.value.per_page,
        page: pagination.value.current_page,
        ...params,
      })
      categories.value = response.data.data
      pagination.value = {
        current_page: response.data.meta.current_page,
        last_page: response.data.meta.last_page,
        per_page: response.data.meta.per_page,
        total: response.data.meta.total,
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao carregar categorias.'
    } finally {
      loading.value = false
    }
  }

  async function createCategory(data: Record<string, unknown>): Promise<Category | null> {
    saving.value = true
    error.value = null
    try {
      const response = await categoriesApi.create(data)
      return response.data.data
    } catch (err: any) {
      if (err.response?.status === 422) {
        error.value = Object.values(err.response.data.errors).flat().join('\n')
      } else {
        error.value = err.response?.data?.message || 'Erro ao criar categoria.'
      }
      return null
    } finally {
      saving.value = false
    }
  }

  async function updateCategory(id: number, data: Record<string, unknown>): Promise<Category | null> {
    saving.value = true
    error.value = null
    try {
      const response = await categoriesApi.update(id, data)
      return response.data.data ?? response.data
    } catch (err: any) {
      if (err.response?.status === 422) {
        error.value = Object.values(err.response.data.errors).flat().join('\n')
      } else {
        error.value = err.response?.data?.message || 'Erro ao atualizar categoria.'
      }
      return null
    } finally {
      saving.value = false
    }
  }

  async function deleteCategory(id: number): Promise<boolean> {
    try {
      await categoriesApi.destroy(id)
      return true
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao eliminar categoria.'
      return false
    }
  }

  function setPage(page: number) {
    pagination.value.current_page = page
  }

  return {
    categories,
    pagination,
    loading,
    saving,
    error,
    isProtected,
    fetchCategories,
    createCategory,
    updateCategory,
    deleteCategory,
    setPage,
  }
})
