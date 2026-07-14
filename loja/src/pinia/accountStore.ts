import { defineStore } from 'pinia'
import { ref } from 'vue'

const API_BASE = '/api'

// ── Interfaces ──────────────────────────────────────────────

export interface OrderItem {
	id: number
	product_id: number
	product_name: string
	product_image: string | null
	unit_price: number
	quantity: number
	item_total: number
}

export interface OrderPayment {
	id: number
	method: { value: string; label: string }
	status: { value: string; label: string }
	amount: number
	payment_data: Record<string, any> | null
	transaction_id: string | null
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
		estimated_days: string | null
	} | null
	subtotal: number
	shipping_cost: number
	total: number
	tracking_number: string | null
	tracking_url: string | null
	paid_at: string | null
	created_at: string
	updated_at: string
	payments: OrderPayment[]
	items: OrderItem[]
}

export interface Address {
	id: number
	firstname: string
	lastname: string
	phone: string | null
	address_line1: string
	address_line2: string | null
	city: string
	postal_code: string
	country: string
	is_default: boolean
}

export interface PaginationMeta {
	current_page: number
	last_page: number
	per_page: number
	total: number
}

// ── Helper seguro para chamadas autenticadas ────────────────

function getAuthHeaders(): Record<string, string> {
	const token = localStorage.getItem('auth_token')
	if (!token) throw new Error('NOT_AUTHENTICATED')
	return {
		Accept: 'application/json',
		'Content-Type': 'application/json',
		Authorization: `Bearer ${token}`,
	}
}

async function handleResponse(res: Response) {
	if (res.status === 401 || res.status === 403) {
		// Token expirado ou sem permissão — limpar sessão
		localStorage.removeItem('auth_token')
		window.location.href = '/login'
		throw new Error('UNAUTHORIZED')
	}
	return res
}

// ── Store ───────────────────────────────────────────────────

export const useAccountStore = defineStore('account', () => {
	// State
	const orders = ref<Order[]>([])
	const ordersPagination = ref<PaginationMeta>({
		current_page: 1,
		last_page: 1,
		per_page: 10,
		total: 0,
	})
	const currentOrder = ref<Order | null>(null)
	const addresses = ref<Address[]>([])
	const isLoading = ref(false)
	const error = ref<string | null>(null)

	// ── Encomendas ──────────────────────────────────────────

	async function fetchOrders(page = 1): Promise<void> {
		isLoading.value = true
		error.value = null
		try {
			const res = await handleResponse(
				await fetch(`${API_BASE}/orders?page=${page}&per_page=10`, {
					headers: getAuthHeaders(),
				}),
			)
			const data = await res.json()
			orders.value = data.data ?? []
			ordersPagination.value = {
				current_page: data.meta?.current_page ?? 1,
				last_page: data.meta?.last_page ?? 1,
				per_page: data.meta?.per_page ?? 10,
				total: data.meta?.total ?? 0,
			}
		} catch (e: any) {
			if (e.message !== 'UNAUTHORIZED' && e.message !== 'NOT_AUTHENTICATED') {
				error.value = 'Erro ao carregar encomendas.'
				console.error(e)
			}
		} finally {
			isLoading.value = false
		}
	}

	async function fetchOrderDetail(orderNumber: string): Promise<void> {
		isLoading.value = true
		error.value = null
		currentOrder.value = null
		try {
			const res = await handleResponse(
				await fetch(`${API_BASE}/orders/${encodeURIComponent(orderNumber)}`, {
					headers: getAuthHeaders(),
				}),
			)
			if (res.ok) {
				const data = await res.json()
				currentOrder.value = data.data ?? data
			} else {
				error.value = 'Encomenda não encontrada.'
			}
		} catch (e: any) {
			if (e.message !== 'UNAUTHORIZED' && e.message !== 'NOT_AUTHENTICATED') {
				error.value = 'Erro ao carregar detalhes da encomenda.'
				console.error(e)
			}
		} finally {
			isLoading.value = false
		}
	}

	// ── Moradas ─────────────────────────────────────────────

	async function fetchAddresses(): Promise<void> {
		isLoading.value = true
		error.value = null
		try {
			const res = await handleResponse(
				await fetch(`${API_BASE}/addresses`, {
					headers: getAuthHeaders(),
				}),
			)
			const data = await res.json()
			addresses.value = data.data ?? data
		} catch (e: any) {
			if (e.message !== 'UNAUTHORIZED' && e.message !== 'NOT_AUTHENTICATED') {
				error.value = 'Erro ao carregar moradas.'
				console.error(e)
			}
		} finally {
			isLoading.value = false
		}
	}

	async function createAddress(
		payload: Omit<Address, 'id' | 'is_default'>,
	): Promise<boolean> {
		error.value = null
		try {
			const res = await handleResponse(
				await fetch(`${API_BASE}/addresses`, {
					method: 'POST',
					headers: getAuthHeaders(),
					body: JSON.stringify(payload),
				}),
			)
			const data = await res.json()
			if (res.ok) {
				await fetchAddresses() // Refetch para atualizar defaults
				return true
			}
			error.value = data.message ?? 'Erro ao criar morada.'
			return false
		} catch (e: any) {
			if (e.message !== 'UNAUTHORIZED' && e.message !== 'NOT_AUTHENTICATED') {
				error.value = 'Erro de ligação.'
			}
			return false
		}
	}

	async function updateAddress(
		id: number,
		payload: Partial<Address>,
	): Promise<boolean> {
		error.value = null
		try {
			const res = await handleResponse(
				await fetch(`${API_BASE}/addresses/${id}`, {
					method: 'PUT',
					headers: getAuthHeaders(),
					body: JSON.stringify(payload),
				}),
			)
			const data = await res.json()
			if (res.ok) {
				await fetchAddresses()
				return true
			}
			error.value = data.message ?? 'Erro ao atualizar morada.'
			return false
		} catch (e: any) {
			if (e.message !== 'UNAUTHORIZED' && e.message !== 'NOT_AUTHENTICATED') {
				error.value = 'Erro de ligação.'
			}
			return false
		}
	}

	async function deleteAddress(id: number): Promise<boolean> {
		error.value = null
		try {
			const res = await handleResponse(
				await fetch(`${API_BASE}/addresses/${id}`, {
					method: 'DELETE',
					headers: getAuthHeaders(),
				}),
			)
			if (res.ok || res.status === 204) {
				await fetchAddresses()
				return true
			}
			const data = await res.json()
			error.value = data.message ?? 'Erro ao eliminar morada.'
			return false
		} catch (e: any) {
			if (e.message !== 'UNAUTHORIZED' && e.message !== 'NOT_AUTHENTICATED') {
				error.value = 'Erro de ligação.'
			}
			return false
		}
	}

	async function setDefaultAddress(id: number): Promise<boolean> {
		error.value = null
		try {
			const res = await handleResponse(
				await fetch(`${API_BASE}/addresses/${id}/default`, {
					method: 'PATCH',
					headers: getAuthHeaders(),
				}),
			)
			if (res.ok) {
				await fetchAddresses()
				return true
			}
			return false
		} catch {
			return false
		}
	}

	async function updateProfile(payload: {
		firstname?: string
		lastname?: string
		phone?: string
	}): Promise<boolean> {
		error.value = null
		try {
			const res = await handleResponse(
				await fetch(`${API_BASE}/user/profile`, {
					method: 'PATCH',
					headers: getAuthHeaders(),
					body: JSON.stringify(payload),
				}),
			)
			const data = await res.json()
			if (res.ok) {
				return true
			}
			error.value = data.message ?? 'Erro ao atualizar perfil.'
			return false
		} catch (e: any) {
			if (e.message !== 'UNAUTHORIZED' && e.message !== 'NOT_AUTHENTICATED') {
				error.value = 'Erro de ligação.'
			}
			return false
		}
	}

	return {
		orders,
		ordersPagination,
		currentOrder,
		addresses,
		isLoading,
		error,
		fetchOrders,
		fetchOrderDetail,
		fetchAddresses,
		createAddress,
		updateAddress,
		deleteAddress,
		setDefaultAddress,
		updateProfile,
	}
})
