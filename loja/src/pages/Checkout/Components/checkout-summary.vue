<script setup lang="ts">
import { useCartStore } from '../../../pinia/cartStore.ts'
import { useFormStore } from '../../../pinia/formStore.ts'
import SummaryItem from './checkout-summary-item.vue'
import ButtonSolid from '../../../components/Buttons/button-solid.vue'

const cartStore = useCartStore()
const formStore = useFormStore()
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
				v-for="(value, _, index) in cartStore.cart"
				:cart-item="value.product"
				:item-count="value.amount"
				:key="index"
			/>
		</div>
		<div class="flex flex-row justify-between">
			<p class="font-semibold tracking-wide text-black text-opacity-60">
				SUBTOTAL
			</p>
			<p class="text-lg font-bold text-black">€{{ cartStore.cartValue.toFixed(2) }}</p>
		</div>
		<div class="flex flex-row justify-between">
			<p class="font-semibold tracking-wide text-black text-opacity-60">
				ENVIO
			</p>
			<p class="text-lg font-bold text-black">
				€{{ formStore.selectedShippingPrice.toFixed(2) }}
			</p>
		</div>
		<div class="flex flex-row justify-between">
			<p class="font-semibold tracking-wide text-black text-opacity-60">
				{{ formStore.taxRateName }} ({{ formStore.taxRatePercent }}%)
			</p>
			<p class="text-lg font-bold text-black">€{{ formStore.taxAmount.toFixed(2) }}</p>
		</div>
		<div class="mt-4 flex flex-row justify-between">
			<p class="font-black tracking-wide text-black">TOTAL</p>
			<p class="text-lg font-bold text-black">€{{ (cartStore.cartValue + formStore.selectedShippingPrice + formStore.taxAmount).toFixed(2) }}</p>
		</div>
		<ButtonSolid
			content="Confirmar e Pagar"
			color="light"
			class="mt-8 self-center font-bold"
			:disabled="formStore.isSubmitting"
			@click="formStore.submit()"
			data-test="checkout-button"
		/>
	</div>
</template>
