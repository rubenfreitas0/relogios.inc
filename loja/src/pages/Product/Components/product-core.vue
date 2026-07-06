<script setup lang="ts">
import ButtonSolid from '../../../components/Buttons/button-solid.vue'
import ButtonGoBack from '../../../components/Buttons/button-go-back.vue'
import type { Product } from '../../../data/product-types.ts'
import { useCartStore } from '../../../pinia/cartStore.ts'
import {
  resolveProductImageUrl,
  getProductImageStyle,
} from '../../../utils/utilities'
import { computed, ref } from 'vue'

const cartStore = useCartStore()
const selectedQuantity = ref(1)

const props = defineProps<{
  item: Product
}>()

const productImage = computed(() => {
  const item = props.item
  if (!item) return ''
  return (
    item.primary_image?.url ||
    item.images?.find((img) => img.is_primary)?.url ||
    item.images?.[0]?.url ||
    ''
  )
})
</script>

<template>
	<ButtonGoBack />
	<section
		class="flex w-4/5 max-w-6xl flex-col lg:grid lg:grid-cols-2 lg:grid-rows-1"
	>
		<div
			class="flex flex-col items-center pb-6 lg:col-span-1 lg:col-start-2 lg:row-span-full lg:ml-24 lg:items-start lg:justify-center"
		>
			<p
				v-if="props.item.is_featured"
				class="lg:text-md text-sm uppercase tracking-broad text-k-main"
			>
				hot product
			</p>
			<h1
				class="mt-4 text-center text-4xl font-semibold uppercase text-white lg:text-start lg:text-6xl"
			>
				{{ props.item.brand?.name || 'Marca' }} <br class="hidden lg:inline" />
				{{ props.item.name }}
			</h1>
			<p
				class="mt-4 whitespace-pre-line text-center text-white/70 lg:pr-20 lg:text-start"
			>
				{{ props.item.short_description || props.item.description }}
			</p>

			<!-- Preço, IVA e Stock -->
			<div
				class="mt-6 flex w-full flex-row items-center justify-between border-b border-white/10 pb-4 lg:pr-20"
			>
				<div class="flex flex-row items-baseline gap-3">
					<div
						v-if="props.item.discount_price"
						class="flex flex-row items-baseline gap-2"
					>
						<span class="text-3xl font-bold text-[#FFC700]"
							>€{{
								Number(props.item.discount_price).toFixed(2).replace('.', ',')
							}}</span
						>
						<span class="text-lg text-white/40 line-through"
							>€{{
								Number(props.item.price).toFixed(2).replace('.', ',')
							}}</span
						>
					</div>
					<span v-else class="text-3xl font-bold text-white"
						>€{{ Number(props.item.price).toFixed(2).replace('.', ',') }}</span
					>
					<span class="text-xs font-normal text-white/50">Inclui 23% IVA</span>
				</div>
				<div>
					<span
						v-if="props.item.stock > 0"
						class="flex flex-row items-center gap-2 text-sm font-semibold text-green-500"
					>
						<span
							class="h-2 w-2 animate-pulse rounded-full bg-green-500"
						></span>
						Em stock
					</span>
					<span
						v-else
						class="flex flex-row items-center gap-2 text-sm font-semibold text-red-500"
					>
						<span class="h-2 w-2 rounded-full bg-red-500"></span>
						Esgotado
					</span>
				</div>
			</div>

			<!-- Encomendas e Seletor de Quantidade -->
			<div class="mt-6 flex w-full flex-col gap-4 lg:pr-20">
				<p class="flex items-center gap-2 text-sm text-white/70">
					<span>🚚</span> Encomendas antes das 17:00, enviadas hoje!
				</p>

				<div v-if="props.item.stock > 0" class="flex flex-row items-end gap-4">
					<div class="flex flex-col gap-1.5">
						<label
							for="quantity"
							class="text-xs font-bold uppercase tracking-wider text-white/50"
							>Número</label
						>
						<select
							id="quantity"
							v-model="selectedQuantity"
							class="w-20 cursor-pointer rounded border border-white/20 bg-white/5 px-3 py-3 text-sm font-bold text-white focus:border-k-main focus:outline-none"
						>
							<option
								v-for="n in Math.min(10, props.item.stock || 1)"
								:key="n"
								:value="n"
								class="bg-k-black text-white"
							>
								{{ n }}
							</option>
						</select>
					</div>

					<ButtonSolid
						color="light"
						add="font-bold w-full"
						content="adicionar ao carrinho"
						@click="cartStore.addToCart(props.item, selectedQuantity)"
					/>
				</div>
				<div v-else class="text-white/60 font-semibold uppercase tracking-wider mt-2">
					Este produto encontra-se temporariamente indisponível para compra.
				</div>
			</div>

			<!-- Proposições de Valor / Garantias -->
			<div
				class="mt-8 flex w-full flex-col gap-3 border-t border-white/10 pt-6 text-sm lg:pr-20"
			>
				<div class="flex flex-row items-center gap-3 text-white/80">
					<span class="text-base font-bold text-k-main">✓</span>
					<span>Entrega grátis em relógios acima de 500€</span>
				</div>
				<div class="flex flex-row items-center gap-3 text-white/80">
					<span class="text-base font-bold text-k-main">✓</span>
					<span>Prazo de devolução de 30 dias</span>
				</div>
				<div class="flex flex-row items-center gap-3 text-white/80">
					<span class="text-base font-bold text-k-main">✓</span>
					<span
						>Revendedor Oficial de
						{{ props.item.brand?.name || 'Marcas Originais' }}</span
					>
				</div>
			</div>
		</div>
		<div
			class="order-first flex items-center justify-center overflow-hidden rounded pb-6 lg:order-none lg:col-span-1 lg:col-start-1 lg:row-span-full lg:pb-0"
		>
			<img
				loading="lazy"
				class="relative max-h-[420px] w-full object-contain"
				:src="resolveProductImageUrl(productImage, props.item.id)"
				:alt="props.item.name"
				:style="getProductImageStyle(props.item.id)"
			/>
		</div>
	</section>
</template>
