<script setup lang="ts">
import { ref } from 'vue'
import { useCartStore } from '../../../pinia/cartStore.ts'
import { useFormStore } from '../../../pinia/formStore.ts'
import { useAuthStore } from '../../../pinia/authStore.ts'
import SummaryItem from './checkout-summary-item.vue'
import ButtonSolid from '../../../components/Buttons/button-solid.vue'

const cartStore = useCartStore()
const formStore = useFormStore()
const authStore = useAuthStore()

const resending = ref(false)
const resendSuccess = ref(false)
const resendError = ref('')

async function handleResend() {
	resending.value = true
	resendSuccess.value = false
	resendError.value = ''
	const ok = await authStore.resendVerification()
	resending.value = false
	if (ok) {
		resendSuccess.value = true
	} else {
		resendError.value = authStore.error || 'Erro ao enviar email.'
	}
}
</script>

<template>
	<div
		class="col-span-1 flex h-fit max-h-full w-full flex-col rounded bg-white px-6 py-12 lg:self-start lg:px-10"
		data-test="checkout-summary"
	>
		<h1 class="text-2xl font-bold uppercase text-black">Resumo</h1>
		<div
			class="my-10 flex h-full w-full flex-col gap-5 overflow-x-hidden overflow-y-scroll"
		>
			<SummaryItem
				v-for="(value, key) in cartStore.cart"
				:key="key"
				:cart-item="value.product"
				:item-count="value.amount"
			/>
		</div>
		<div class="flex flex-row justify-between">
			<p class="font-semibold tracking-wide text-black text-opacity-60">
				SUBTOTAL
			</p>
			<p class="text-lg font-bold text-black">
				€{{ cartStore.cartValue.toFixed(2) }}
			</p>
		</div>
		<div class="flex flex-row justify-between">
			<p class="font-semibold tracking-wide text-black text-opacity-60">
				ENVIO
			</p>
			<p class="text-lg font-bold text-black">
				€{{ formStore.selectedShippingPrice.toFixed(2) }}
			</p>
		</div>
		<div class="mt-4 flex flex-row justify-between">
			<p class="font-black tracking-wide text-black">TOTAL</p>
			<p class="text-lg font-bold text-black">
				€{{
					(
						cartStore.cartValue +
						formStore.selectedShippingPrice
					).toFixed(2)
				}}
			</p>
		</div>
		<ButtonSolid
			content="Confirmar e Pagar"
			color="light"
			class="mt-8 self-center font-bold"
			:disabled="formStore.isSubmitting || formStore.shippingLoading || !!(authStore.user && !authStore.user?.email_verified_at)"
			data-test="checkout-button"
			@click="formStore.submit()"
		/>

		<div
			v-if="authStore.user && !authStore.user.email_verified_at"
			class="mt-6 flex flex-col gap-2 rounded border border-red-500/30 bg-red-500/10 p-4 text-center"
		>
			<p class="text-xs font-semibold text-red-500">
				Deves verificar o teu email antes de efetuar a primeira compra.
			</p>
			<button
				type="button"
				:disabled="resending"
				class="text-xs font-bold text-black underline hover:text-black/80 disabled:opacity-50"
				@click="handleResend"
			>
				{{ resending ? 'A enviar...' : 'Reenviar email de verificação' }}
			</button>
			<p v-if="resendSuccess" class="text-[10px] font-semibold text-green-600">
				Email de verificação enviado!
			</p>
			<p v-if="resendError" class="text-[10px] font-semibold text-red-500">
				{{ resendError }}
			</p>
		</div>
	</div>
</template>
