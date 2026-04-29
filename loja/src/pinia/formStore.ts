import { defineStore } from 'pinia'
import { useCartStore } from './cartStore'

interface ShippingMethod {
	id: number
	name: string
	carrier: string
	price: string | number
	estimated_days: string | null
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
		country: '',
		payment: 'electronic',
		comment: '',
		showErrors: false,
		isSubmitting: false,
		apiError: null,
		shippingMethods: [],
		shipping_method_id: null,
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
		async fetchShippingMethods() {
			try {
				const res = await fetch('/api/shipping')
				if (res.ok) {
					this.shippingMethods = await res.json()
					if (this.shippingMethods.length > 0 && !this.shipping_method_id) {
						this.shipping_method_id = this.shippingMethods[0].id
					}
				}
			} catch (e) {
				console.error('Erro ao buscar métodos de envio:', e)
			}
		},
		async submit() {
			const cartStore = useCartStore()

			if (cartStore.cartLength === 0) {
				alert('Shopping cart is empty!')
				return
			}

			if (!this.shipping_method_id) {
				alert('Please select a shipping method.')
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
				country: this.country.substring(0, 2).toUpperCase() || 'PT', // country deve ser 2 chars max segundo validação
				notes: this.comment
			}

			try {
				const token = localStorage.getItem('auth_token')
				if (!token) {
					alert('Please login to complete the checkout.')
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
					this.apiError = data.message || 'Error processing your order.'
					alert(this.apiError)
				}
			} catch (error) {
				console.error('Network error:', error)
				this.apiError = 'Could not connect to the server.'
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
			return /[\w',-\\/.\s]/.test(state.address) === true ? 'true' : 'false'
		},
		isValidZip(state: FormState) {
			if (state.zip === '') return 'empty'
			return /^[0-9]{4,5}(?:-[0-9]{3,4})?$/.test(state.zip) === true
				? 'true'
				: 'false'
		},
		isValidCity(state: FormState) {
			if (state.city === '') return 'empty'
			return /[a-zA-Z\s]+/.test(state.city) === true ? 'true' : 'false'
		},
		isValidCountry(state: FormState) {
			if (state.country === '') return 'empty'
			return /[a-zA-Z\s]+/.test(state.country) === true ? 'true' : 'false'
		},
		selectedShippingPrice(state: FormState): number {
			const method = state.shippingMethods.find((m) => m.id === state.shipping_method_id)
			return method ? Number(method.price) : 0
		},
	},
})
