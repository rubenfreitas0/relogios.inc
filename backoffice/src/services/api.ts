import axios from 'axios'
import type { AxiosInstance, InternalAxiosRequestConfig, AxiosResponse } from 'axios'
import router from '../router'

/**
 * Axios client configurado para a API da loja.
 * Injeta automaticamente o token Sanctum e trata 401s.
 */
const api: AxiosInstance = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://relogios.inc.test/api',
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
})

// — Request interceptor: injeta Bearer token —
api.interceptors.request.use(
  (config: InternalAxiosRequestConfig) => {
    const token = localStorage.getItem('backoffice_auth_token')
    if (token && config.headers) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => Promise.reject(error),
)

// — Response interceptor: trata 401 (sessão expirada) —
api.interceptors.response.use(
  (response: AxiosResponse) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('backoffice_auth_token')
      localStorage.removeItem('backoffice_auth_user')
      router.push({ name: 'login' })
    }
    return Promise.reject(error)
  },
)

export default api

// ——————————————————————————————————————
// Endpoints organizados por módulo
// ——————————————————————————————————————

export const authApi = {
  login: (data: { email: string; password: string }) => api.post('/login', data),
  logout: () => api.post('/logout'),
}

export const dashboardApi = {
  stats: () => api.get('/admin/dashboard/stats'),
}

export const productsApi = {
  list: (params?: Record<string, unknown>) => api.get('/admin/products', { params }),
  show: (id: number) => api.get(`/admin/products/${id}`),
  create: (data: FormData) =>
    api.post('/admin/products', data, {
      headers: { 'Content-Type': 'multipart/form-data' },
    }),
  update: (id: number, data: FormData) =>
    api.post(`/admin/products/${id}`, data, {
      headers: { 'Content-Type': 'multipart/form-data' },
      params: { _method: 'PUT' },
    }),
  destroy: (id: number) => api.delete(`/admin/products/${id}`),
  updateStock: (id: number, stock: number) => api.patch(`/admin/products/${id}/stock`, { stock }),
  restore: (id: number) => api.post(`/admin/products/${id}/restore`),
}

export const ordersApi = {
  list: (params?: Record<string, unknown>) => api.get('/admin/orders', { params }),
  show: (orderNumber: string) => api.get(`/admin/orders/${orderNumber}`),
  updateStatus: (orderNumber: string, data: { status: string; tracking_number?: string }) =>
    api.patch(`/admin/orders/${orderNumber}/status`, data),
}

export const brandsApi = {
  list: (params?: Record<string, unknown>) => api.get('/admin/brands', { params }),
  show: (id: number) => api.get(`/admin/brands/${id}`),
  create: (data: FormData) =>
    api.post('/admin/brands', data, {
      headers: { 'Content-Type': 'multipart/form-data' },
    }),
  update: (id: number, data: FormData) =>
    api.post(`/admin/brands/${id}`, data, {
      headers: { 'Content-Type': 'multipart/form-data' },
      params: { _method: 'PUT' },
    }),
  destroy: (id: number) => api.delete(`/admin/brands/${id}`),
}

export const categoriesApi = {
  list: (params?: Record<string, unknown>) => api.get('/admin/categories', { params }),
  show: (id: number) => api.get(`/admin/categories/${id}`),
  create: (data: Record<string, unknown>) => api.post('/admin/categories', data),
  update: (id: number, data: Record<string, unknown>) => api.put(`/admin/categories/${id}`, data),
  destroy: (id: number) => api.delete(`/admin/categories/${id}`),
}

export const shippingMethodsApi = {
  list: (params?: Record<string, unknown>) => api.get('/admin/shipping-methods', { params }),
  show: (id: number) => api.get(`/admin/shipping-methods/${id}`),
  create: (data: Record<string, unknown>) => api.post('/admin/shipping-methods', data),
  update: (id: number, data: Record<string, unknown>) => api.put(`/admin/shipping-methods/${id}`, data),
  destroy: (id: number) => api.delete(`/admin/shipping-methods/${id}`),
}

export const shippingZonesApi = {
  list: (params?: Record<string, unknown>) => api.get('/admin/shipping-zones', { params }),
  show: (id: number) => api.get(`/admin/shipping-zones/${id}`),
  create: (data: Record<string, unknown>) => api.post('/admin/shipping-zones', data),
  update: (id: number, data: Record<string, unknown>) => api.put(`/admin/shipping-zones/${id}`, data),
  destroy: (id: number) => api.delete(`/admin/shipping-zones/${id}`),
}

export const productImagesApi = {
  list: (productId: number) => api.get(`/admin/products/${productId}/images`),
  upload: (productId: number, data: FormData) =>
    api.post(`/admin/products/${productId}/images`, data, {
      headers: { 'Content-Type': 'multipart/form-data' },
    }),
  destroy: (productId: number, imageId: number) =>
    api.delete(`/admin/products/${productId}/images/${imageId}`),
  reorder: (productId: number, imageIds: number[]) =>
    api.patch(`/admin/products/${productId}/images/reorder`, { order: imageIds }),
  setPrimary: (productId: number, imageId: number) =>
    api.patch(`/admin/products/${productId}/images/${imageId}/primary`),
}
