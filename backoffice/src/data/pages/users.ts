import { User } from '../../pages/users/types'
import api from '../../services/api'

export type Pagination = {
  page: number
  perPage: number
  total: number
}

export type Sorting = {
  sortBy: keyof User | undefined
  sortingOrder: 'asc' | 'desc' | null
}

export type Filters = {
  isActive: boolean
  search: string
}

export const getUsers = async (filters: Partial<Filters & Pagination & Sorting>) => {
  const { isActive, search, page = 1, sortBy, sortingOrder } = filters

  const response = await api.get('/admin/users', {
    params: {
      isActive: isActive !== undefined ? isActive : undefined,
      search,
      page,
      sortBy,
      sortingOrder,
    },
  })

  return {
    data: response.data.data,
    pagination: {
      page: response.data.pagination.page,
      perPage: response.data.pagination.perPage,
      total: response.data.pagination.total,
    },
  }
}

export const addUser = async (user: User) => {
  const response = await api.post('/admin/users', user)
  return [response.data]
}

export const updateUser = async (user: User) => {
  const response = await api.put(`/admin/users/${user.id}`, user)
  return [response.data]
}

export const removeUser = async (user: User) => {
  await api.delete(`/admin/users/${user.id}`)
  return true
}

export const uploadAvatar = async (body: FormData) => {
  // Retorna um link simulado para o avatar por enquanto
  return { publicUrl: '' }
}
