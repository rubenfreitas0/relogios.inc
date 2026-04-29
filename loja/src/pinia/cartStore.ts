import { defineStore } from 'pinia'
import type { product } from '../data/product-types.ts'
import { useStorage } from '@vueuse/core'

interface CartProduct extends Partial<product> {
	id: number
	category?: string
	src?: string
	header?: string
	subheader?: string
	price: number
}

interface cartItem {
	id?: number
	product: CartProduct
	amount: number
}

interface cart {
	[key: string]: cartItem
}

interface CartState {
	cart: cart
	showCart: boolean
	showQuickAdd: boolean
	shipping: number
	freeShippingThreshold: number
	lastAddedItem: CartProduct | null
	showToast: boolean
	toastTimeout: ReturnType<typeof setTimeout> | null
	isBumping: boolean
	bumpTimeout: ReturnType<typeof setTimeout> | null
}

export const useCartStore = defineStore('cart', {
	state: (): CartState => ({
		cart: useStorage('cart', {} as cart).value,
		showCart: false,
		showQuickAdd: true,
		shipping: 50,
		freeShippingThreshold: 1000,
		
		lastAddedItem: null,
		showToast: false,
		toastTimeout: null,
		isBumping: false,
		bumpTimeout: null,
	}),
	actions: {
		cartOn() {
			this.showCart = true
			document.body.classList.add('overflow-y-hidden')
		},

		cartOff() {
			this.showCart = false
			document.body.classList.remove('overflow-y-hidden')
		},

		async fetchCart() {
			const token = localStorage.getItem('auth_token')
			if (!token) return

			try {
				const res = await fetch('/api/cart', {
					headers: {
						Accept: 'application/json',
						Authorization: `Bearer ${token}`,
					},
				})
				if (res.ok) {
					const data = await res.json()
					const newCart: cart = {}
					for (const item of data.items) {
						// Usamos um prefixo 'api_' para as chaves sincronizadas
						const prodKey = 'api_' + item.product.id
						newCart[prodKey] = {
							id: item.id,
							amount: item.quantity,
							product: {
								id: item.product.id,
								category: 'api',
								src: item.product.image || '',
								header: item.product.name,
								subheader: item.product.slug,
								price: item.product.price,
							},
						}
					}
					this.cart = newCart
				}
			} catch (e) {
				console.error('Erro ao buscar o carrinho da API:', e)
			}
		},

		async syncLocalCartToApi() {
			const token = localStorage.getItem('auth_token')
			if (!token) return

			for (const key of Object.keys(this.cart)) {
				const item = this.cart[key]
				if (!item.id) {
					try {
						await fetch('/api/cart', {
							method: 'POST',
							headers: {
								'Content-Type': 'application/json',
								Accept: 'application/json',
								Authorization: `Bearer ${token}`,
							},
							body: JSON.stringify({ product_id: item.product.id, quantity: item.amount }),
						})
					} catch (e) {
						console.error('Erro ao sincronizar item local:', e)
					}
				}
			}
			await this.fetchCart()
		},

		async addToCart(item: CartProduct) {
			const itemKey = item.category && item.category !== 'api' ? (item.category + item.id + '') : ('api_' + item.id)
			const token = localStorage.getItem('auth_token')

			if (itemKey in this.cart) {
				this.cart[itemKey].amount = this.cart[itemKey].amount + 1
			} else {
				this.cart[itemKey] = { product: item, amount: 1 }
			}

			if (token) {
				try {
					const res = await fetch('/api/cart', {
						method: 'POST',
						headers: {
							'Content-Type': 'application/json',
							Accept: 'application/json',
							Authorization: `Bearer ${token}`,
						},
						body: JSON.stringify({ product_id: item.id, quantity: 1 }),
					})
					if (res.ok) {
						await this.fetchCart()
					}
				} catch (e) {
					console.error('Erro ao adicionar à API:', e)
				}
			}

			this.lastAddedItem = item
			this.showToast = true
			this.isBumping = true

			if (this.toastTimeout) clearTimeout(this.toastTimeout)
			this.toastTimeout = setTimeout(() => {
				this.showToast = false
			}, 3000)

			if (this.bumpTimeout) clearTimeout(this.bumpTimeout)
			this.bumpTimeout = setTimeout(() => {
				this.isBumping = false
			}, 300)
		},

		async removeFromCart(item: CartProduct) {
			const itemKey = item.category && item.category !== 'api' ? (item.category + item.id + '') : ('api_' + item.id)
			const cartItem = this.cart[itemKey]
			const token = localStorage.getItem('auth_token')

			if (!cartItem) return

			if (cartItem.amount > 1) {
				const newAmount = cartItem.amount - 1
				if (token && cartItem.id) {
					try {
						await fetch(`/api/cart/${cartItem.id}`, {
							method: 'PUT',
							headers: {
								'Content-Type': 'application/json',
								Accept: 'application/json',
								Authorization: `Bearer ${token}`,
							},
							body: JSON.stringify({ quantity: newAmount }),
						})
					} catch (e) {
						console.error(e)
					}
				}
				cartItem.amount = newAmount
			} else {
				if (token && cartItem.id) {
					try {
						await fetch(`/api/cart/${cartItem.id}`, {
							method: 'DELETE',
							headers: {
								Accept: 'application/json',
								Authorization: `Bearer ${token}`,
							},
						})
					} catch (e) {
						console.error(e)
					}
				}
				delete this.cart[itemKey]
			}
		},

		async clearCart() {
			const token = localStorage.getItem('auth_token')
			if (token) {
				try {
					await fetch('/api/cart', {
						method: 'DELETE',
						headers: {
							Accept: 'application/json',
							Authorization: `Bearer ${token}`,
						},
					})
				} catch (e) {
					console.error(e)
				}
			}
			this.cart = {}
		},

		getProductAmount(productKey: string): number {
			return this.cart[productKey]?.amount || 0
		},
	},
	getters: {
		cartValue(state: CartState) {
			let total = 0
			for (const id of Object.keys(state.cart)) {
				total += state.cart[id].amount * state.cart[id].product.price
			}
			return total
		},
		cartLength(state: CartState) {
			let ressi = 0
			for (const id of Object.keys(state.cart)) {
				ressi += state.cart[id].amount
			}
			return ressi
		},
		isEmpty(state: CartState) {
			return Object.keys(state.cart).length < 1 ? true : false
		},
		getItemAmountByKey(state: CartState) {
			return (productKey: string) => {
				return state.cart[productKey]?.amount || 0
			}
		},
		getItemByKey(state: CartState) {
			return (productKey: string) => {
				return state.cart[productKey]
			}
		},
		getFirstItem(state: CartState) {
			return state.cart[Object.keys(state.cart)[0]]
		},
		getUniqueItems(state: CartState) {
			return Object.keys(state.cart).length || 0
		},
		getGrandTotal(): number {
			return this.cartValue > this.freeShippingThreshold
				? this.cartValue
				: this.cartValue + this.shipping
		},
		getVat(): string {
			return (this.getGrandTotal * 0.2).toFixed(2)
		},
		isCartShown(state: CartState) {
			return state.showCart === true ? true : false
		},
	},
})
