<script setup lang="ts">
import { onMounted, watch, ref } from 'vue'
import TextInputField from './text-input-field.vue'
import { useFormStore } from '../../../pinia/formStore.ts'
import { useAccountStore, type Address } from '../../../pinia/accountStore.ts'

const formStore = useFormStore()
const accountStore = useAccountStore()

const selectedAddressId = ref<number | null>(null)
const isLoggedIn = ref(false)

onMounted(async () => {
	formStore.fetchShippingForCountry()

	// Se o user está autenticado, buscar moradas guardadas
	const token = localStorage.getItem('auth_token')
	if (token) {
		isLoggedIn.value = true
		await accountStore.fetchAddresses()

		// Auto-selecionar a morada default se existir
		const defaultAddr = accountStore.addresses.find(a => a.is_default)
		if (defaultAddr) {
			applyAddress(defaultAddr)
		}
	}
})

function applyAddress(addr: Address) {
	selectedAddressId.value = addr.id
	formStore.name = `${addr.firstname} ${addr.lastname}`
	formStore.phone = addr.phone || ''
	formStore.address = addr.address_line1 + (addr.address_line2 ? `, ${addr.address_line2}` : '')
	formStore.zip = addr.postal_code
	formStore.city = addr.city
	formStore.country = addr.country
}

function clearSelection() {
	selectedAddressId.value = null
	formStore.name = ''
	formStore.phone = ''
	formStore.address = ''
	formStore.zip = ''
	formStore.city = ''
	formStore.country = 'PT'
}

function onSelectAddress(event: Event) {
	const id = Number((event.target as HTMLSelectElement).value)
	if (id === 0) {
		clearSelection()
		return
	}
	const addr = accountStore.addresses.find(a => a.id === id)
	if (addr) applyAddress(addr)
}

// Recalcular shipping quando o país ou código postal mudam
let debounceTimer: ReturnType<typeof setTimeout> | null = null

watch(
	() => [formStore.country, formStore.zip],
	() => {
		if (debounceTimer) clearTimeout(debounceTimer)
		debounceTimer = setTimeout(() => {
			const country = formStore.country.substring(0, 2).toUpperCase()
			if (country.length === 2) {
				formStore.fetchShippingForCountry(country, formStore.zip)
			}
		}, 500)
	}
)
</script>
<template>
	<form
		class="col-span-2 h-full w-full rounded bg-white px-6 py-12 lg:px-10"
		id="checkoutForm"
	>
		<h1 class="text-3xl font-bold uppercase text-black">Checkout</h1>
		
		<div v-if="formStore.apiError" class="mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded relative" role="alert">
			<strong class="font-bold">Erro: </strong>
			<span class="block sm:inline">{{ formStore.apiError }}</span>
		</div>

		<div class="mt-10">
			<p class="mb-2 font-bold uppercase tracking-wider text-k-main">
				Dados de Faturação
			</p>
			<div
				class="flex w-full flex-col items-center gap-4 lg:grid lg:grid-cols-2"
			>
				<TextInputField
					type="text"
					:validator="formStore.isValidName"
					id="name"
					label="Nome"
					placeholder="João Silva"
					error-message="Apenas caracteres."
					autocomplete="off"
					:required="true"
				/>

				<TextInputField
					type="email"
					:validator="formStore.isValidEmail"
					id="email"
					label="Email"
					placeholder="joao@email.com"
					error-message="Email inválido."
					autocomplete="off"
					:required="true"
				/>

				<TextInputField
					type="tel"
					:validator="formStore.isValidPhone"
					id="phone"
					label="Telefone"
					placeholder="+351 912 345 678"
					error-message="Apenas números e '+-'."
					autocomplete="off"
				/>
			</div>
		</div>

		<div class="mt-10">
			<div class="mb-4 flex items-center justify-between">
				<p class="font-bold uppercase tracking-wider text-k-main">
					Dados de Envio
				</p>
			</div>

			<!-- Seletor de moradas guardadas -->
			<div
				v-if="isLoggedIn && accountStore.addresses.length > 0"
				class="mb-5 rounded-lg border-2 border-dashed border-k-main/30 bg-k-main/5 p-4"
			>
				<div class="flex items-center gap-2 mb-3">
					<svg class="h-4 w-4 text-k-main" fill="none" viewBox="0 0 24 24" stroke="currentColor">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
					</svg>
					<span class="text-xs font-bold uppercase tracking-wider text-k-main">Moradas Guardadas</span>
				</div>
				<select
					:value="selectedAddressId || 0"
					@change="onSelectAddress"
					class="w-full rounded-lg border border-black/20 bg-white px-4 py-3 font-Manrope text-sm font-semibold text-black outline-none transition-colors hover:border-k-main focus:border-k-main cursor-pointer"
				>
					<option :value="0">✏️ Introduzir morada manualmente</option>
					<option
						v-for="addr in accountStore.addresses"
						:key="addr.id"
						:value="addr.id"
					>
						{{ addr.is_default ? '⭐ ' : '' }}{{ addr.firstname }} {{ addr.lastname }} — {{ addr.address_line1 }}, {{ addr.postal_code }} {{ addr.city }}
					</option>
				</select>
			</div>

			<div
				class="flex w-full flex-col items-center gap-4 lg:grid lg:grid-cols-2"
			>
				<TextInputField
					type="text"
					:validator="formStore.isValidAddress"
					id="address"
					label="Morada"
					container-class="col-span-2"
					placeholder="Rua das Flores, 123"
					error-message="Apenas caracteres e ',-/.' permitidos."
					autocomplete="off"
					:required="true"
				/>

				<TextInputField
					type="text"
					:validator="formStore.isValidZip"
					id="zip"
					label="Código Postal"
					placeholder="1000-001"
					error-message="Código postal inválido."
					autocomplete="off"
					max-length="8"
					:required="true"
				/>

				<TextInputField
					type="text"
					:validator="formStore.isValidCity"
					id="city"
					label="Cidade"
					placeholder="Lisboa"
					error-message="Apenas caracteres."
					autocomplete="off"
					:required="true"
				/>

				<TextInputField
					type="text"
					:validator="formStore.isValidCountry"
					id="country"
					label="País (código ISO, ex: PT)"
					placeholder="PT"
					error-message="Código de 2 letras (ex: PT, ES, DE)."
					autocomplete="off"
					max-length="2"
					:required="true"
				/>
			</div>
		</div>

		<div class="mt-10">
			<p class="mb-2 font-bold uppercase tracking-wider text-k-main">
				Método de Envio
			</p>
			<div class="flex w-full flex-col gap-4 lg:grid lg:grid-cols-2">
				<div v-if="formStore.shippingLoading" class="col-span-2 text-gray-500 italic">
					A carregar métodos de envio...
				</div>
				<div v-else-if="formStore.shippingMethods.length === 0" class="col-span-2 text-gray-500 italic">
					Nenhum método de envio disponível para este destino.
				</div>
				<button
					v-for="method in formStore.shippingMethods"
					:key="method.id"
					type="button"
					class="group flex w-full cursor-pointer flex-row items-center gap-4 rounded border border-black border-opacity-60 p-3 transition-all active:translate-y-0.5"
					:class="{ 'bg-k-main border-opacity-100': formStore.shipping_method_id === method.id }"
					@click="formStore.shipping_method_id = method.id"
				>
					<div
						class="aspect-square h-3 rounded-full border border-black border-opacity-60"
						:class="{ 'bg-black': formStore.shipping_method_id === method.id }"
					></div>
					<div class="flex flex-col items-start">
						<span class="font-semibold text-black">{{ method.name }}</span>
						<span class="text-xs text-gray-600">{{ method.carrier }} — €{{ Number(method.price).toFixed(2) }}</span>
						<span v-if="method.estimated_days" class="text-xs text-gray-400">{{ method.estimated_days }}</span>
					</div>
				</button>
			</div>
		</div>

		<div class="mt-10">
			<p class="mb-2 font-bold uppercase tracking-wider text-k-main">
				Pagamento
			</p>
			<p class="mb-1 font-bold text-black">Método de Pagamento</p>
			<div class="flex w-full flex-col gap-4 lg:grid lg:grid-cols-2">
				<button
					type="button"
					class="group flex w-full cursor-pointer flex-row items-center gap-4 rounded border border-black border-opacity-60 p-3 transition-all active:translate-y-0.5"
					:class="{ 'bg-k-main': !formStore.choseCash }"
					@click="formStore.setElectronic($event)"
					data-test="form-button-emoney"
				>
					<div
						class="aspect-square h-3 rounded-full border border-black border-opacity-60"
						:class="{ 'bg-black': !formStore.choseCash }"
					></div>
					<span class="font-semibold text-black"> Cartão de Crédito </span>
				</button>
				<button
					type="button"
					class="group flex w-full cursor-pointer flex-row items-center gap-4 rounded border border-black border-opacity-60 p-3 transition-all active:translate-y-0.5"
					:class="{ 'bg-k-main': formStore.choseCash }"
					@click="formStore.setCash($event)"
					data-test="form-button-cash"
				>
					<div
						class="aspect-square h-3 rounded-full border border-black border-opacity-60"
						:class="{ 'bg-black': formStore.choseCash }"
					></div>
					<span class="font-semibold text-black"> Multibanco </span>
				</button>

				<div class="col-span-2 flex h-40 flex-col">
					<label class="mb-1 mt-4 font-bold text-black" for="comment"
						>Comentário</label
					>
					<textarea
						class="h-full rounded border border-black border-opacity-60 bg-white p-3 font-Manrope font-semibold text-black outline-none hover:border-k-main"
						id="comment"
						placeholder="A sua mensagem"
						v-model="formStore.comment"
						data-test="form-text-area"
					/>
				</div>
			</div>
		</div>
	</form>
</template>

