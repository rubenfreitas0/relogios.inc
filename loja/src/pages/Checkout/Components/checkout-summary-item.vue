<script setup lang="ts">
interface CartItemProduct {
	id: number
	name?: string
	primary_image?: { url: string }
	price: number | string
	discount_price?: number | string | null
}

const props = defineProps<{
	cartItem: CartItemProduct
	itemCount: number
}>()

import { resolveProductImageUrl } from '../../../utils/utilities'
</script>

<template>
	<div
		v-if="itemCount > 0"
		class="flex h-full w-full flex-row items-center justify-start gap-3"
	>
		<div class="basis-1/4 justify-self-start overflow-hidden rounded-lg">
			<img
				class="aspect-square h-fit w-fit bg-black object-contain p-2 shadow-md"
				:src="
					resolveProductImageUrl(
						props.cartItem.primary_image?.url,
						props.cartItem.id,
					) || '/images/placeholder.png'
				"
				:alt="props.cartItem.name"
			/>
		</div>
		<div class="flex basis-2/4 flex-col items-start">
			<p class="text-start text-lg font-bold text-k-black">
				{{ props.cartItem.name }}
			</p>
			<div class="flex flex-col leading-tight">
				<span
					v-if="props.cartItem.discount_price"
					class="text-k-black/45 text-[0.7rem] line-through"
				>
					€ {{ Number(props.cartItem.price).toFixed(2).replace('.', ',') }}
				</span>
				<p class="text-md text-start font-bold text-k-black opacity-80">
					€
					{{
						Number(props.cartItem.discount_price || props.cartItem.price)
							.toFixed(2)
							.replace('.', ',')
					}}
				</p>
			</div>
		</div>

		<div
			class="ml-auto justify-self-end text-lg font-bold text-black opacity-50"
		>
			x{{ props.itemCount }}
		</div>
	</div>
</template>
