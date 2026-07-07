<script setup lang="ts">
import { onMounted, onBeforeUnmount, watch, ref } from 'vue'
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

    // O fetch é assíncrono, então o utilizador pode começar a preencher (ou
    // limpar) o formulário antes de a resposta chegar. Guardamos aqui os
    // valores para não sobrescrever entrada do utilizador feita entretanto.
    const beforeFetch = {
      name: formStore.name,
      phone: formStore.phone,
      address: formStore.address,
      zip: formStore.zip,
      city: formStore.city,
      country: formStore.country,
    }

    await accountStore.fetchAddresses()

    const formUntouchedSinceFetch =
      formStore.name === beforeFetch.name &&
      formStore.phone === beforeFetch.phone &&
      formStore.address === beforeFetch.address &&
      formStore.zip === beforeFetch.zip &&
      formStore.city === beforeFetch.city &&
      formStore.country === beforeFetch.country

    // Auto-selecionar a morada default se existir
    const defaultAddr = accountStore.addresses.find((a) => a.is_default)
    if (defaultAddr && formUntouchedSinceFetch) {
      applyAddress(defaultAddr)
    }
  }
})

function applyAddress(addr: Address) {
  selectedAddressId.value = addr.id
  formStore.name = `${addr.firstname} ${addr.lastname}`
  formStore.phone = addr.phone || ''
  formStore.address =
    addr.address_line1 + (addr.address_line2 ? `, ${addr.address_line2}` : '')
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
  const addr = accountStore.addresses.find((a) => a.id === id)
  if (addr) applyAddress(addr)
}

// Recalcular shipping quando o país ou código postal mudam
let debounceTimer: ReturnType<typeof setTimeout> | null = null

watch(
  () => [formStore.country, formStore.zip],
  () => {
    formStore.shippingLoading = true
    if (debounceTimer) clearTimeout(debounceTimer)
    debounceTimer = setTimeout(() => {
      const country = formStore.country.substring(0, 2).toUpperCase()
      if (country.length === 2) {
        formStore.fetchShippingForCountry(country, formStore.zip)
      } else {
        formStore.shippingLoading = false
      }
    }, 300)
  },
)

onBeforeUnmount(() => {
  if (debounceTimer) clearTimeout(debounceTimer)
})
</script>
<template>
	<form
		id="checkoutForm"
		class="col-span-2 h-full w-full rounded bg-white px-6 py-12 lg:px-10"
	>
		<h1 class="text-3xl font-bold uppercase text-black">Checkout</h1>

		<div
			v-if="formStore.apiError"
			class="relative mt-4 rounded border border-red-400 bg-red-100 p-4 text-red-700"
			role="alert"
		>
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
					id="name"
					type="text"
					:validator="formStore.isValidName"
					label="Nome"
					placeholder="João Silva"
					error-message="Apenas caracteres."
					autocomplete="off"
					:required="true"
				/>

				<TextInputField
					id="email"
					type="email"
					:validator="formStore.isValidEmail"
					label="Email"
					placeholder="joao@email.com"
					error-message="Email inválido."
					autocomplete="off"
					:required="true"
				/>

				<TextInputField
					id="phone"
					type="tel"
					:validator="formStore.isValidPhone"
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
				<div class="mb-3 flex items-center gap-2">
					<svg
						class="h-4 w-4 text-k-main"
						fill="none"
						viewBox="0 0 24 24"
						stroke="currentColor"
					>
						<path
							stroke-linecap="round"
							stroke-linejoin="round"
							stroke-width="2"
							d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
						/>
						<path
							stroke-linecap="round"
							stroke-linejoin="round"
							stroke-width="2"
							d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
						/>
					</svg>
					<span class="text-xs font-bold uppercase tracking-wider text-k-main"
						>Moradas Guardadas</span
					>
				</div>
				<select
					:value="selectedAddressId || 0"
					class="w-full cursor-pointer rounded-lg border border-black/20 bg-white px-4 py-3 font-Manrope text-sm font-semibold text-black outline-none transition-colors hover:border-k-main focus:border-k-main"
					@change="onSelectAddress"
				>
					<option :value="0">✏️ Introduzir morada manualmente</option>
					<option
						v-for="addr in accountStore.addresses"
						:key="addr.id"
						:value="addr.id"
					>
						{{ addr.is_default ? '⭐ ' : '' }}{{ addr.firstname }}
						{{ addr.lastname }} — {{ addr.address_line1 }},
						{{ addr.postal_code }} {{ addr.city }}
					</option>
				</select>
			</div>

			<div
				class="flex w-full flex-col items-center gap-4 lg:grid lg:grid-cols-2"
			>
				<TextInputField
					id="address"
					type="text"
					:validator="formStore.isValidAddress"
					label="Morada"
					container-class="col-span-2"
					placeholder="Rua das Flores, 123"
					error-message="Apenas caracteres e ',-/.' permitidos."
					autocomplete="off"
					:required="true"
				/>

				<TextInputField
					id="zip"
					type="text"
					:validator="formStore.isValidZip"
					label="Código Postal"
					placeholder="1000-001"
					error-message="Código postal inválido."
					autocomplete="off"
					max-length="8"
					:required="true"
				/>

				<TextInputField
					id="city"
					type="text"
					:validator="formStore.isValidCity"
					label="Cidade"
					placeholder="Lisboa"
					error-message="Apenas caracteres."
					autocomplete="off"
					:required="true"
				/>

				<TextInputField
					id="country"
					type="text"
					:validator="formStore.isValidCountry"
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
				<div
					v-if="formStore.shippingLoading"
					class="col-span-2 italic text-gray-500"
				>
					A carregar métodos de envio...
				</div>
				<div
					v-else-if="formStore.shippingMethods.length === 0"
					class="col-span-2 italic text-gray-500"
				>
					Nenhum método de envio disponível para este destino.
				</div>
				<button
					v-for="method in formStore.shippingMethods"
					:key="method.id"
					type="button"
					class="group flex w-full cursor-pointer flex-row items-center gap-4 rounded border border-black border-opacity-60 p-3 transition-all active:translate-y-0.5"
					:class="{
						'border-opacity-100 bg-k-main':
							formStore.shipping_method_id === method.id,
					}"
					@click="formStore.shipping_method_id = method.id"
				>
					<div
						class="aspect-square h-3 rounded-full border border-black border-opacity-60"
						:class="{ 'bg-black': formStore.shipping_method_id === method.id }"
					></div>
					<div class="flex flex-col items-start">
						<span class="font-semibold text-black">{{ method.name }}</span>
						<span class="text-xs text-gray-600"
							>{{ method.carrier }} — €{{
								Number(method.price).toFixed(2)
							}}</span
						>
						<span v-if="method.estimated_days" class="text-xs text-gray-400">{{
							method.estimated_days
						}}</span>
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
				<!-- Cartão de Crédito -->
				<button
					type="button"
					class="group flex w-full cursor-pointer flex-row items-center gap-4 rounded border border-black border-opacity-60 p-3 transition-all active:translate-y-0.5"
					:class="{ 'bg-k-main': formStore.payment === 'credit_card' }"
					data-test="form-button-emoney"
					@click="formStore.payment = 'credit_card'"
				>
					<div
						class="aspect-square h-3 rounded-full border border-black border-opacity-60"
						:class="{ 'bg-black': formStore.payment === 'credit_card' }"
					></div>
					<span class="font-semibold text-black"> Cartão de Crédito </span>
				</button>

				<!-- Multibanco -->
				<button
					type="button"
					class="group flex w-full cursor-pointer flex-row items-center gap-4 rounded border border-black border-opacity-60 p-3 transition-all active:translate-y-0.5"
					:class="{ 'bg-k-main': formStore.payment === 'multibanco' }"
					data-test="form-button-cash"
					@click="formStore.payment = 'multibanco'"
				>
					<div
						class="aspect-square h-3 rounded-full border border-black border-opacity-60"
						:class="{ 'bg-black': formStore.payment === 'multibanco' }"
					></div>
					<span class="font-semibold text-black"> Multibanco </span>
				</button>

				<!-- MB Way -->
				<button
					type="button"
					class="group flex w-full cursor-pointer flex-row items-center gap-4 rounded border border-black border-opacity-60 p-3 transition-all active:translate-y-0.5"
					:class="{ 'bg-k-main': formStore.payment === 'mbway' }"
					data-test="form-button-mbway"
					@click="formStore.payment = 'mbway'"
				>
					<div
						class="aspect-square h-3 rounded-full border border-black border-opacity-60"
						:class="{ 'bg-black': formStore.payment === 'mbway' }"
					></div>
					<span class="font-semibold text-black"> MB Way </span>
				</button>

				<!-- Apple Pay -->
				<button
					type="button"
					class="group flex w-full cursor-pointer flex-row items-center gap-4 rounded border border-black border-opacity-60 p-3 transition-all active:translate-y-0.5"
					:class="{ 'bg-k-main': formStore.payment === 'apple_pay' }"
					data-test="form-button-apple-pay"
					@click="formStore.payment = 'apple_pay'"
				>
					<div
						class="aspect-square h-3 rounded-full border border-black border-opacity-60"
						:class="{ 'bg-black': formStore.payment === 'apple_pay' }"
					></div>
					<span class="font-semibold text-black"> Apple Pay </span>
				</button>

				<!-- Google Pay -->
				<button
					type="button"
					class="group flex w-full cursor-pointer flex-row items-center gap-4 rounded border border-black border-opacity-60 p-3 transition-all active:translate-y-0.5 lg:col-span-2"
					:class="{ 'bg-k-main': formStore.payment === 'google_pay' }"
					data-test="form-button-google-pay"
					@click="formStore.payment = 'google_pay'"
				>
					<div
						class="aspect-square h-3 rounded-full border border-black border-opacity-60"
						:class="{ 'bg-black': formStore.payment === 'google_pay' }"
					></div>
					<span class="font-semibold text-black"> Google Pay </span>
				</button>

				<!-- Dynamic MB Way Phone field -->
				<div v-if="formStore.payment === 'mbway'" class="col-span-2 mt-2">
					<TextInputField
						id="paymentPhone"
						type="text"
						:validator="formStore.isValidPaymentPhone"
						label="Número de Telemóvel MB Way"
						placeholder="912345678"
						error-message="Por favor introduza um número de telemóvel válido."
						autocomplete="off"
						:required="true"
					/>
				</div>

				<div class="col-span-2 flex h-40 flex-col">
					<label class="mb-1 mt-4 font-bold text-black" for="comment"
						>Comentário</label
					>
					<textarea
						id="comment"
						v-model="formStore.comment"
						class="h-full rounded border border-black border-opacity-60 bg-white p-3 font-Manrope font-semibold text-black outline-none hover:border-k-main"
						placeholder="A sua mensagem"
						data-test="form-text-area"
					/>
				</div>
			</div>
		</div>
	</form>
</template>
