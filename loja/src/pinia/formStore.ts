import { defineStore } from 'pinia'
import { useCartStore } from './cartStore'

interface ShippingMethod {
	id: number
	name: string
	carrier: string
	price: string | number
	estimated_days: string | null
}

interface ShippingData {
	subtotal: number
	total_weight: number
	tax_rate_name: string
	tax_rate_percent: number
	tax_amount: number
	shipping_methods: ShippingMethod[]
}

interface Order {
	order_number: string
	total: number
	items: {
		product_name: string
		product_image: string
		unit_price: number
		quantity: number
	}[]
}

interface FormState {
	bannerState: string
	name: string
	email: string
	phone: string
	address: string
	zip: string
	city: string
	country: string
	payment: string
	comment: string
	showErrors: boolean
	isSubmitting: boolean
	apiError: string | null
	shippingMethods: ShippingMethod[]
	shipping_method_id: number | null
	shippingData: ShippingData | null
	shippingLoading: boolean
	lastOrder: Order | null
}

export const useFormStore = defineStore('form', {
	state: (): FormState => ({
		bannerState: 'hide',
		name: '',
		email: '',
		phone: '',
		address: '',
		zip: '',
		city: '',
		country: 'PT',
		payment: 'electronic',
		comment: '',
		showErrors: false,
		isSubmitting: false,
		apiError: null,
		shippingMethods: [],
		shipping_method_id: null,
		shippingData: null,
		shippingLoading: false,
		lastOrder: null,
	}),
	actions: {
		bannerOn() {
			this.bannerState = 'show'
		},
		bannerOff() {
			this.bannerState = 'hide'
		},
		setCash(e: Event) {
			e.preventDefault()
			this.payment = 'cash'
		},
		setElectronic(e: Event) {
			e.preventDefault()
			this.payment = 'electronic'
		},

		/**
		 * Busca métodos de envio disponíveis com base no país e código postal.
		 * Usa o endpoint /api/shipping/calculate que filtra por zona + peso.
		 */
		async fetchShippingForCountry(countryCode?: string, postalCode?: string) {
			const token = localStorage.getItem('auth_token')
			if (!token) return

			const country = (countryCode || this.country || 'PT').substring(0, 2).toUpperCase()
			if (country.length !== 2) return

			this.shippingLoading = true

			try {
				const params = new URLSearchParams({ country })
				const postal = postalCode || this.zip
				if (postal) params.append('postal_code', postal)

				const res = await fetch(`/api/shipping/calculate?${params}`, {
					headers: {
						Accept: 'application/json',
						Authorization: `Bearer ${token}`,
					},
				})

				if (res.ok) {
					const data: ShippingData = await res.json()
					this.shippingData = data
					this.shippingMethods = data.shipping_methods

					// Auto-selecionar o mais barato se nenhum está selecionado
					// ou se o selecionado já não existe nos novos métodos
					const currentExists = this.shippingMethods.some(m => m.id === this.shipping_method_id)
					if (this.shippingMethods.length > 0 && (!this.shipping_method_id || !currentExists)) {
						this.shipping_method_id = this.shippingMethods[0].id
					} else if (this.shippingMethods.length === 0) {
						this.shipping_method_id = null
					}
				} else {
					// Carrinho vazio ou país sem métodos
					this.shippingData = null
					this.shippingMethods = []
					this.shipping_method_id = null
				}
			} catch (e) {
				console.error('Erro ao buscar métodos de envio:', e)
			} finally {
				this.shippingLoading = false
			}
		},

		/**
		 * @deprecated Usar fetchShippingForCountry() em vez disto.
		 * Mantido temporariamente para compatibilidade.
		 */
		async fetchShippingMethods() {
			await this.fetchShippingForCountry()
		},

		async submit() {
			const cartStore = useCartStore()

			if (cartStore.cartLength === 0) {
				alert('O carrinho está vazio!')
				return
			}

			if (!this.shipping_method_id) {
				alert('Por favor selecione um método de envio.')
				return
			}

			const allSet =
				this.isValidName === 'true' &&
				this.isValidEmail === 'true' &&
				this.isValidPhone !== 'false' &&
				this.isValidAddress === 'true' &&
				this.isValidZip === 'true' &&
				this.isValidCity === 'true' &&
				this.isValidCountry === 'true'

			if (!allSet) {
				this.showErrors = true
				return
			}

			this.showErrors = false
			this.isSubmitting = true
			this.apiError = null

			// Parse name
			const nameParts = this.name.trim().split(' ')
			const firstname = nameParts[0]
			const lastname = nameParts.slice(1).join(' ') || firstname // Fallback se não tiver apelido

			// Preparar payload
			const payload = {
				shipping_method_id: this.shipping_method_id,
				payment_method: this.payment === 'electronic' ? 'credit_card' : 'multibanco',
				firstname: firstname,
				lastname: lastname,
				phone: this.phone,
				address_line1: this.address,
				city: this.city,
				postal_code: this.zip,
				country: this.country.substring(0, 2).toUpperCase() || 'PT',
				notes: this.comment
			}

			try {
				const token = localStorage.getItem('auth_token')
				if (!token) {
					alert('Por favor faça login para completar a compra.')
					this.isSubmitting = false
					return
				}

				const res = await fetch('/api/orders', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						Accept: 'application/json',
						Authorization: `Bearer ${token}`
					},
					body: JSON.stringify(payload)
				})

				const data = await res.json()

				if (res.ok) {
					// Sucesso!
					this.lastOrder = data.data
					this.bannerOn()
					cartStore.clearCart() // limpar o carrinho localmente pois já foi limpo no backend na order
				} else {
					console.error('Validation errors:', data)
					this.apiError = data.message || 'Erro ao processar a encomenda.'
					alert(this.apiError)
				}
			} catch (error) {
				console.error('Network error:', error)
				this.apiError = 'Não foi possível conectar ao servidor.'
				alert(this.apiError)
			} finally {
				this.isSubmitting = false
			}
		},
	},
	getters: {
		showBanner(state: FormState) {
			return state.bannerState == 'show'
		},
		choseCash(state: FormState) {
			return state.payment == 'cash'
		},
		isValidName(state: FormState) {
			if (state.name === '') return 'empty'
			return /^[a-z ,.'-]+$/i.test(state.name) === true ? 'true' : 'false'
		},
		isValidEmail(state: FormState) {
			if (state.email === '') return 'empty'
			return /^([a-zA-Z0-9_.-]+)@([a-zA-Z0-9_-]+)(\.[a-zA-Z]{2,5}){1,2}$/.test(
				state.email,
			) === true
				? 'true'
				: 'false'
		},
		isValidPhone(state: FormState) {
			if (state.phone === '') return 'empty'
			return /^[0-9()+\-\s]+$/.test(state.phone) === true ? 'true' : 'false'
		},
		isValidAddress(state: FormState) {
			if (state.address === '') return 'empty'
			return new RegExp('^[a-zA-ZÀ-ÿ0-9\\s,.\'/ -]+$').test(state.address) === true ? 'true' : 'false'
		},
		isValidZip(state: FormState) {
			if (state.zip === '') return 'empty'
			return /^[0-9]{4,5}(?:-[0-9]{3,4})?$/.test(state.zip) === true
				? 'true'
				: 'false'
		},
		isValidCity(state: FormState) {
			if (state.city === '') return 'empty'
			return /^[a-zA-ZÀ-ÿ\s]+$/.test(state.city) === true ? 'true' : 'false'
		},
		isValidCountry(state: FormState) {
			if (state.country === '') return 'empty'
			return /^[a-zA-Z]{2}$/.test(state.country) === true ? 'true' : 'false'
		},
		selectedShippingPrice(state: FormState): number {
			const method = state.shippingMethods.find((m) => m.id === state.shipping_method_id)
			return method ? Number(method.price) : 0
		},
		taxAmount(state: FormState): number {
			return state.shippingData?.tax_amount ?? 0
		},
		taxRatePercent(state: FormState): number {
			return state.shippingData?.tax_rate_percent ?? 0
		},
		taxRateName(state: FormState): string {
			return state.shippingData?.tax_rate_name ?? 'IVA'
		},
	},
})
