import { defineStore } from 'pinia'
import { ref } from 'vue'
import { dashboardApi } from '../services/api'

export interface DashboardStats {
  products: {
    total: number
    active: number
    out_of_stock: number
    low_stock: number
  }
  orders: {
    today: number
    this_month: number
    last_month: number
    pending_count: number
    by_status: Record<string, number>
  }
  revenue: {
    this_month: number
    last_month: number
    by_month: Array<{
      month: string
      label: string
      total: number
      orders: number
    }>
  }
  customers: {
    total: number
  }
  sales_by_country: Array<{
    country: string
    count: number
  }>
  latest_orders: Array<{
    order_number: string
    customer_name: string
    customer_email: string | null
    status: {
      value: string
      label: string
    }
    payment_status: {
      value: string
      label: string
    }
    total: number
    created_at: string
  }>
}

export const useDashboardStore = defineStore('dashboard', () => {
  const stats = ref<DashboardStats | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  async function fetchStats() {
    loading.value = true
    error.value = null
    try {
      const response = await dashboardApi.stats()
      stats.value = response.data
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao carregar estatísticas do dashboard.'
    } finally {
      loading.value = false
    }
  }

  return {
    stats,
    loading,
    error,
    fetchStats,
  }
})
