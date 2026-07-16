import { defineStore } from 'pinia'
import { ref } from 'vue'
import { ordersApi } from '../services/api'

export interface OrderItem {
  id: number
  product_id: number
  product_name: string
  product_image: string | null
  unit_price: string
  quantity: number
  item_total: string
}

export interface Order {
  order_number: string
  status: { value: string; label: string }
  payment_status: { value: string; label: string }
  customer: {
    firstname: string
    lastname: string
    email: string
    phone: string | null
    nif: string | null
  }
  shipping_address: {
    address_line1: string
    address_line2: string | null
    city: string
    postal_code: string
    country: string
  }
  shipping_method: {
    name: string
    carrier: string
    estimated_days: number
  } | null
  subtotal: number
  shipping_cost: number
  total: number
  tracking_number: string | null
  tracking_url: string | null
  notes: string | null
  paid_at: string | null
  created_at: string
  updated_at: string
  items: OrderItem[]
}

export interface PaginationMeta {
  current_page: number
  last_page: number
  per_page: number
  total: number
}

export const useOrdersStore = defineStore('orders', () => {
  const orders = ref<Order[]>([])
  const pagination = ref<PaginationMeta>({ current_page: 1, last_page: 1, per_page: 15, total: 0 })
  const loading = ref(false)
  const saving = ref(false)
  const error = ref<string | null>(null)

  async function fetchOrders(params: Record<string, unknown> = {}) {
    loading.value = true
    error.value = null
    try {
      const response = await ordersApi.list({
        per_page: pagination.value.per_page,
        page: pagination.value.current_page,
        ...params,
      })
      orders.value = response.data.data
      pagination.value = {
        current_page: response.data.meta.current_page,
        last_page: response.data.meta.last_page,
        per_page: response.data.meta.per_page,
        total: response.data.meta.total,
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao carregar encomendas.'
    } finally {
      loading.value = false
    }
  }

  async function fetchOrder(orderNumber: string): Promise<Order | null> {
    loading.value = true
    error.value = null
    try {
      const response = await ordersApi.show(orderNumber)
      return response.data.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao carregar encomenda.'
      return null
    } finally {
      loading.value = false
    }
  }

  async function updateStatus(
    orderNumber: string,
    data: { status: string; tracking_number?: string },
  ): Promise<Order | null> {
    saving.value = true
    error.value = null
    try {
      const response = await ordersApi.updateStatus(orderNumber, data)
      return response.data.data
    } catch (err: any) {
      if (err.response?.status === 422) {
        const errors = err.response.data.errors
        error.value = Object.values(errors).flat().join('\n')
      } else {
        error.value = err.response?.data?.message || 'Erro ao atualizar estado.'
      }
      return null
    } finally {
      saving.value = false
    }
  }

  function setPage(page: number) {
    pagination.value.current_page = page
  }

  return {
    orders,
    pagination,
    loading,
    saving,
    error,
    fetchOrders,
    fetchOrder,
    updateStatus,
    setPage,
  }
})
