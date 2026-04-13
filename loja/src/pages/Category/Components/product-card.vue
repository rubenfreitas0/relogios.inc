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
	<div class="group relative flex flex-col overflow-hidden rounded-xl bg-white shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] border border-gray-100 transition-all duration-300 hover:shadow-[0_8px_30px_-4px_rgba(0,0,0,0.1)]">
		<!-- Image Container -->
		<router-link
			:to="{ name: props.category, params: { id: props.item.id } }"
			class="relative block aspect-[4/5] w-full overflow-hidden bg-[#f9f9f9]"
		>
			<div
				v-if="props.item.nu"
				class="absolute left-3 top-3 z-10 rounded bg-[#FFC700] px-3 py-1.5 text-xs font-bold uppercase tracking-widest text-black shadow-sm"
			>
				New
			</div>
			<img
				loading="lazy"
				class="h-full w-full object-cover"
				:src="props.item.src"
				:alt="props.item.header"
			/>
		</router-link>

		<!-- Content Container -->
		<div class="flex flex-1 flex-col p-6">
			<!-- Tags -->
			<div v-if="props.item.tags && props.item.tags.length > 0" class="mb-3 flex flex-wrap gap-1.5">
				<span 
					v-for="tag in props.item.tags" 
					:key="tag"
					class="px-2 py-0.5 rounded-sm bg-gray-100 text-[0.65rem] font-bold uppercase tracking-wider text-gray-500"
				>
					{{ tag }}
				</span>
			</div>
            
			<router-link :to="{ name: props.category, params: { id: props.item.id } }" class="mb-2 block">
				<h2 class="text-xs font-bold uppercase tracking-widest text-[#FFC700]">
					{{ props.item.header }}
				</h2>
				<h3 class="mt-1 text-[1.1rem] font-bold text-black leading-tight transition-colors duration-300 group-hover:text-gray-700">
					{{ props.item.subheader }}
				</h3>
			</router-link>
            
            <!-- Truncated description -->
            <p class="mt-2 text-[0.9rem] text-gray-500 line-clamp-2 mb-6 flex-1 font-normal leading-relaxed">
                {{ props.item.text }}
            </p>

            <div class="mt-auto flex items-center justify-between border-t border-gray-100 pt-5">
				<div class="flex flex-col">
					<span v-if="props.item.oldPrice" class="text-xs font-semibold text-gray-400 line-through decoration-gray-300">
						{{ formatPrice(props.item.oldPrice) }}
					</span>
                	<span class="text-xl font-bold tracking-tight" :class="props.item.oldPrice ? 'text-red-600' : 'text-black'">
						{{ formatPrice(props.item.price) }}
					</span>
				</div>
				
				<!-- Add to Cart (Desktop & Mobile) -->
				<button 
					@click.prevent="cartStore.addToCart(props.item)"
					class="flex h-11 w-11 items-center justify-center rounded-full bg-black text-white hover:bg-[#FFC700] hover:text-black hover:scale-105 active:scale-95 transition-all duration-200 shadow-sm"
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
					color="light"
                    content="VER RELÓGIO"
                    size="small"
                    class="w-full flex justify-center"
                    add="w-full text-center text-xs"
				/>
            </div>
		</div>
	</div>
</template>
