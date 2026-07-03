<script setup lang="ts">
import type { Product } from '../data/product-types.ts'
import { useCatalogStore } from '../pinia/catalogStore.ts'
import { onMounted, ref, watch, computed } from 'vue'
import {
	resolveProductImageUrl,
	getProductImageStyle,
} from '../utils/utilities'

const props = defineProps<{
	productSlug: string
}>()

const items = ref<Product[]>([])
const catalogStore = useCatalogStore()

const loadRelated = async () => {
	items.value = await catalogStore.fetchRelatedProducts(props.productSlug)
}

// Filtra apenas produtos em stock
const inStockItems = computed(() =>
	items.value.filter((item) => item.stock > 0),
)

onMounted(() => {
	loadRelated()
})

watch(
	() => props.productSlug,
	() => {
		loadRelated()
	},
)

const getProductCategoryRoute = (gender?: string) => {
	if (gender === 'masculino') return 'homens'
	if (gender === 'feminino') return 'mulheres'
	return 'unisexo'
}

const getProductImage = (item: Product) => {
	if (item.primary_image?.url) {
		return item.primary_image.url
	}
	if (item.images && item.images.length > 0) {
		const primary = item.images.find((img) => img.is_primary)
		return primary?.url || item.images[0].url
	}
	return undefined
}

const formatPrice = (price?: number | string) => {
	const num = Number(price)
	if (isNaN(num)) return '€0,00'
	return `${num.toFixed(2).replace('.', ',')} €`
}
</script>

<template>
	<section
		v-if="inStockItems.length > 0"
		class="mt-16 flex w-4/5 max-w-6xl flex-col lg:mt-24"
	>
		<!-- Header -->
		<div
			class="mb-6 flex items-end justify-between border-b border-white/10 pb-4"
		>
			<h2
				class="font-Manrope text-lg font-bold uppercase tracking-wider text-white"
			>
				Outros também compraram
			</h2>
			<span class="text-xs font-semibold text-white/30"
				>{{ inStockItems.length }} produto{{
					inStockItems.length !== 1 ? 's' : ''
				}}</span
			>
		</div>

		<!-- Scrollable Row -->
		<div class="scrollbar-thin flex gap-4 overflow-x-auto pb-4">
			<router-link
				v-for="item in inStockItems"
				:key="item.id"
				:to="`/${getProductCategoryRoute(item.gender)}/${item.slug}`"
				class="hover:border-white/15 group flex w-44 flex-shrink-0 flex-col rounded-lg border border-white/[0.06] bg-k-dark-grey transition-all duration-300 hover:shadow-lg hover:shadow-black/30"
			>
				<!-- Image -->
				<div
					class="relative flex aspect-square w-full items-center justify-center overflow-hidden rounded-t-lg bg-transparent p-3"
				>
					<img
						class="h-full w-full object-contain transition-transform duration-300 group-hover:scale-105"
						:src="resolveProductImageUrl(getProductImage(item), item.id)"
						:alt="item.name"
						loading="lazy"
						:style="getProductImageStyle(item.id)"
					/>
					<!-- Badge Destaque -->
					<span
						v-if="item.is_featured"
						class="absolute left-2 top-2 rounded bg-[#FFC700] px-1.5 py-0.5 text-[0.55rem] font-bold uppercase tracking-wider text-black"
					>
						Destaque
					</span>
				</div>

				<!-- Info -->
				<div class="flex flex-1 flex-col gap-1 px-3 pb-3 pt-2.5">
					<!-- Brand -->
					<p
						class="text-[0.6rem] font-bold uppercase tracking-widest text-[#FFC700]"
					>
						{{ item.brand?.name || 'Marca' }}
					</p>
					<!-- Name -->
					<p
						class="line-clamp-2 text-xs font-semibold leading-tight text-white transition-colors duration-200 group-hover:text-[#FFC700]"
					>
						{{ item.name }}
					</p>
					<!-- Short description -->
					<p
						v-if="item.short_description"
						class="mt-0.5 line-clamp-2 text-[0.65rem] leading-snug text-white/40"
					>
						{{ item.short_description }}
					</p>

					<!-- Spacer -->
					<div class="mt-auto"></div>

					<!-- Price -->
					<div class="mt-2 flex flex-col leading-tight">
						<span
							v-if="item.discount_price"
							class="text-[0.7rem] text-white/40 line-through"
						>
							{{ formatPrice(item.price) }}
						</span>
						<span
							class="text-sm font-bold tracking-tight text-[#FFC700]"
							v-if="item.discount_price"
						>
							{{ formatPrice(item.discount_price) }}
						</span>
						<span v-else class="text-sm font-bold tracking-tight text-white">
							{{ formatPrice(item.price) }}
						</span>
					</div>
					<!-- Stock -->
					<div class="flex items-center gap-1.5">
						<span class="h-1.5 w-1.5 rounded-full bg-green-400"></span>
						<span class="text-[0.6rem] font-semibold text-green-400/80">
							<template v-if="item.stock <= 3"
								>Apenas {{ item.stock }} em stock</template
							>
							<template v-else>Em stock</template>
						</span>
					</div>
				</div>
			</router-link>
		</div>
	</section>
</template>

<style scoped>
.scrollbar-thin::-webkit-scrollbar {
	height: 4px;
}
.scrollbar-thin::-webkit-scrollbar-track {
	background: transparent;
}
.scrollbar-thin::-webkit-scrollbar-thumb {
	background: rgba(255, 255, 255, 0.1);
	border-radius: 2px;
}
.scrollbar-thin::-webkit-scrollbar-thumb:hover {
	background: rgba(255, 255, 255, 0.2);
}
</style>
