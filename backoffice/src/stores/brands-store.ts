import { defineStore } from 'pinia'
import { ref } from 'vue'
import { brandsApi } from '../services/api'

export interface Brand {
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

export const useBrandsStore = defineStore('brands', () => {
  const brands = ref<Brand[]>([])
  const pagination = ref<PaginationMeta>({ current_page: 1, last_page: 1, per_page: 15, total: 0 })
  const loading = ref(false)
  const saving = ref(false)
  const error = ref<string | null>(null)

  async function fetchBrands(params: Record<string, unknown> = {}) {
    loading.value = true
    error.value = null
    try {
      const response = await brandsApi.list({
        per_page: pagination.value.per_page,
        page: pagination.value.current_page,
        ...params,
      })
      brands.value = response.data.data
      pagination.value = {
        current_page: response.data.meta.current_page,
        last_page: response.data.meta.last_page,
        per_page: response.data.meta.per_page,
        total: response.data.meta.total,
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao carregar marcas.'
    } finally {
      loading.value = false
    }
  }

  async function createBrand(formData: FormData): Promise<Brand | null> {
    saving.value = true
    error.value = null
    try {
      const response = await brandsApi.create(formData)
      return response.data.data
    } catch (err: any) {
      if (err.response?.status === 422) {
        error.value = Object.values(err.response.data.errors).flat().join('\n')
      } else {
        error.value = err.response?.data?.message || 'Erro ao criar marca.'
      }
      return null
    } finally {
      saving.value = false
    }
  }

  async function updateBrand(id: number, formData: FormData): Promise<Brand | null> {
    saving.value = true
    error.value = null
    try {
      const response = await brandsApi.update(id, formData)
      return response.data.data ?? response.data
    } catch (err: any) {
      if (err.response?.status === 422) {
        error.value = Object.values(err.response.data.errors).flat().join('\n')
      } else {
        error.value = err.response?.data?.message || 'Erro ao atualizar marca.'
      }
      return null
    } finally {
      saving.value = false
    }
  }

  async function deleteBrand(id: number): Promise<boolean> {
    try {
      await brandsApi.destroy(id)
      return true
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao desativar marca.'
      return false
    }
  }

  function setPage(page: number) {
    pagination.value.current_page = page
  }

  return { brands, pagination, loading, saving, error, fetchBrands, createBrand, updateBrand, deleteBrand, setPage }
})
