<script setup lang="ts">
import { onMounted, watch } from 'vue'
import TextInputField from './text-input-field.vue'
import { useFormStore } from '../../../pinia/formStore.ts'

const formStore = useFormStore()

// Buscar métodos de envio ao montar (com o país default PT)
onMounted(() => {
	formStore.fetchShippingForCountry()
})

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
			<p class="mb-2 font-bold uppercase tracking-wider text-k-main">
				Dados de Envio
			</p>
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
