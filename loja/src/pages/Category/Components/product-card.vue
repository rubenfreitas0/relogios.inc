<script setup lang="ts">
import { product } from '../../../data/product-types.ts'
import { useCartStore } from '../../../pinia/cartStore.ts'

import ButtonSolid from '../../../components/Buttons/button-solid.vue'

const cartStore = useCartStore()

const props = defineProps<{
	category: string
	item: product
}>()

const formatPrice = (price?: number) => {
	if (price !== undefined) {
		return `€${price.toFixed(2).replace('.', ',')}`
	}
	return '€199,00'
}
</script>

<template>
	<div class="group relative flex flex-col transition-all duration-300">
		<!-- Image Container -->
		<router-link
			:to="{ name: props.category, params: { id: props.item.id } }"
			class="relative block aspect-[4/5] w-full overflow-hidden rounded-xl bg-k-dark-grey transition-all duration-500 group-hover:shadow-lg group-hover:shadow-[#FFC700]/10"
		>
			<div
				v-if="props.item.nu"
				class="absolute left-3 top-3 z-10 rounded bg-[#FFC700] px-3 py-1.5 text-xs font-bold uppercase tracking-widest text-black shadow-sm"
			>
				New
			</div>
			<img
				loading="lazy"
				class="h-full w-full object-cover opacity-90 transition-opacity duration-300 group-hover:opacity-100"
				:src="props.item.src"
				:alt="props.item.header"
			/>
		</router-link>

		<!-- Content Container -->
		<div class="flex flex-1 flex-col pt-5">
			<!-- Tags -->
			<div v-if="props.item.tags && props.item.tags.length > 0" class="mb-3 flex flex-wrap gap-1.5">
				<span 
					v-for="tag in props.item.tags" 
					:key="tag"
					class="px-2 py-0.5 rounded-sm bg-white/5 text-[0.65rem] font-bold uppercase tracking-wider text-white/50"
				>
					{{ tag }}
				</span>
			</div>
            
			<router-link :to="{ name: props.category, params: { id: props.item.id } }" class="mb-2 block">
				<h2 class="text-xs font-bold uppercase tracking-widest text-[#FFC700]">
					{{ props.item.header }}
				</h2>
				<h3 class="mt-1 text-[1.1rem] font-bold text-white leading-tight transition-colors duration-300 group-hover:text-[#FFC700]">
					{{ props.item.subheader }}
				</h3>
			</router-link>
            
            <!-- Truncated description -->
            <p class="mt-2 text-[0.9rem] text-white/50 line-clamp-2 mb-6 flex-1 font-normal leading-relaxed">
                {{ props.item.text }}
            </p>

            <div class="mt-auto flex items-center justify-between pt-2">
				<div class="flex flex-col">
					<span v-if="props.item.oldPrice" class="text-xs font-semibold text-white/40 line-through decoration-white/20">
						{{ formatPrice(props.item.oldPrice) }}
					</span>
                	<span class="text-xl font-bold tracking-tight transition-colors duration-300 group-hover:text-[#FFC700]" :class="props.item.oldPrice ? 'text-red-500' : 'text-white'">
						{{ formatPrice(props.item.price) }}
					</span>
				</div>
				
				<!-- Add to Cart (Desktop & Mobile) -->
				<button 
					@click.prevent="cartStore.addToCart(props.item)"
					class="flex h-11 w-11 items-center justify-center rounded-full bg-white/5 text-white hover:bg-[#FFC700] hover:text-black hover:scale-110 active:scale-95 transition-all duration-300"
					title="Adicionar ao carrinho"
				>
					<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
					  <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
					</svg>
				</button>
            </div>
            
            <!-- Mobile View Product -->
            <div class="mt-4 md:hidden">
                <ButtonSolid
					:to="{ name: props.category, params: { id: props.item.id } }"
					color="dark"
                    content="VER RELÓGIO"
                    size="small"
                    class="w-full flex justify-center"
                    add="w-full text-center text-xs"
				/>
            </div>
		</div>
	</div>
</template>
