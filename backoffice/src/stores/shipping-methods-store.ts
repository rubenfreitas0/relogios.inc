import { defineStore } from 'pinia'
import { ref } from 'vue'
import { shippingMethodsApi } from '../services/api'

export interface ShippingMethod {
  id: number
  name: string
  carrier: string
  price: string | number
  min_weight: string | number
  max_weight: string | number
  estimated_days: string
  is_active: boolean
  shipping_zone_id: number | null
  shipping_zone?: { id: number; name: string } | null
  created_at: string
  updated_at: string
}

export interface PaginationMeta {
  current_page: number
  last_page: number
  per_page: number
  total: number
}

export const useShippingMethodsStore = defineStore('shipping-methods', () => {
  const methods = ref<ShippingMethod[]>([])
  const pagination = ref<PaginationMeta>({ current_page: 1, last_page: 1, per_page: 15, total: 0 })
  const loading = ref(false)
  const saving = ref(false)
  const error = ref<string | null>(null)

  async function fetchMethods(params: Record<string, unknown> = {}) {
    loading.value = true
    error.value = null
    try {
      const response = await shippingMethodsApi.list({
        per_page: pagination.value.per_page,
        page: pagination.value.current_page,
        ...params,
      })
      methods.value = response.data.data
      pagination.value = {
        current_page: response.data.meta.current_page,
        last_page: response.data.meta.last_page,
        per_page: response.data.meta.per_page,
        total: response.data.meta.total,
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao carregar métodos de envio.'
    } finally {
      loading.value = false
    }
  }

  async function createMethod(data: Record<string, unknown>): Promise<ShippingMethod | null> {
    saving.value = true
    error.value = null
    try {
      const response = await shippingMethodsApi.create(data)
      return response.data.data
    } catch (err: any) {
      if (err.response?.status === 422) {
        error.value = Object.values(err.response.data.errors).flat().join('\n')
      } else {
        error.value = err.response?.data?.message || 'Erro ao criar método de envio.'
      }
      return null
    } finally {
      saving.value = false
    }
  }

  async function updateMethod(id: number, data: Record<string, unknown>): Promise<ShippingMethod | null> {
    saving.value = true
    error.value = null
    try {
      const response = await shippingMethodsApi.update(id, data)
      return response.data.data ?? response.data
    } catch (err: any) {
      if (err.response?.status === 422) {
        error.value = Object.values(err.response.data.errors).flat().join('\n')
      } else {
        error.value = err.response?.data?.message || 'Erro ao atualizar método de envio.'
      }
      return null
    } finally {
      saving.value = false
    }
  }

  async function deleteMethod(id: number): Promise<boolean> {
    try {
      await shippingMethodsApi.destroy(id)
      return true
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao eliminar método de envio.'
      return false
    }
  }

  function setPage(page: number) {
    pagination.value.current_page = page
  }

  return {
    methods,
    pagination,
    loading,
    saving,
    error,
    fetchMethods,
    createMethod,
    updateMethod,
    deleteMethod,
    setPage,
  }
})
