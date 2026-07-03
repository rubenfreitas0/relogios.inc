import { defineStore } from 'pinia'
import { ref } from 'vue'
import { shippingZonesApi } from '../services/api'

export interface ShippingZone {
  id: number
  name: string
  is_active: boolean
  countries?: Array<{ id: number; country_code: string }>
  shipping_methods_count?: number
  created_at: string
  updated_at: string
}

export interface PaginationMeta {
  current_page: number
  last_page: number
  per_page: number
  total: number
}

export const useShippingZonesStore = defineStore('shipping-zones', () => {
  const zones = ref<ShippingZone[]>([])
  const pagination = ref<PaginationMeta>({ current_page: 1, last_page: 1, per_page: 15, total: 0 })
  const loading = ref(false)
  const saving = ref(false)
  const error = ref<string | null>(null)

  async function fetchZones(params: Record<string, unknown> = {}) {
    loading.value = true
    error.value = null
    try {
      const response = await shippingZonesApi.list({
        per_page: pagination.value.per_page,
        page: pagination.value.current_page,
        ...params,
      })
      zones.value = response.data.data
      pagination.value = {
        current_page: response.data.meta.current_page,
        last_page: response.data.meta.last_page,
        per_page: response.data.meta.per_page,
        total: response.data.meta.total,
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao carregar zonas de envio.'
    } finally {
      loading.value = false
    }
  }

  async function createZone(data: Record<string, unknown>): Promise<ShippingZone | null> {
    saving.value = true
    error.value = null
    try {
      const response = await shippingZonesApi.create(data)
      return response.data.data
    } catch (err: any) {
      if (err.response?.status === 422) {
        error.value = Object.values(err.response.data.errors).flat().join('\n')
      } else {
        error.value = err.response?.data?.message || 'Erro ao criar zona de envio.'
      }
      return null
    } finally {
      saving.value = false
    }
  }

  async function updateZone(id: number, data: Record<string, unknown>): Promise<ShippingZone | null> {
    saving.value = true
    error.value = null
    try {
      const response = await shippingZonesApi.update(id, data)
      return response.data.data ?? response.data
    } catch (err: any) {
      if (err.response?.status === 422) {
        error.value = Object.values(err.response.data.errors).flat().join('\n')
      } else {
        error.value = err.response?.data?.message || 'Erro ao atualizar zona de envio.'
      }
      return null
    } finally {
      saving.value = false
    }
  }

  async function deleteZone(id: number): Promise<boolean> {
    try {
      await shippingZonesApi.destroy(id)
      return true
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao eliminar zona de envio.'
      return false
    }
  }

  function setPage(page: number) {
    pagination.value.current_page = page
  }

  return {
    zones,
    pagination,
    loading,
    saving,
    error,
    fetchZones,
    createZone,
    updateZone,
    deleteZone,
    setPage,
  }
})
