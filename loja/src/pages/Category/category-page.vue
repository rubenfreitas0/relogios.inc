<script setup lang="ts">
import Navigation from '../../components/navigation-global.vue'
import Footer from '../../components/footer-global.vue'
import ProductCard from './Components/product-card.vue'
import { useCatalogStore } from '../../pinia/catalogStore'
import type { Product } from '../../data/product-types'
import { computed, ref, watch, onMounted } from 'vue'

const props = defineProps<{ category: string }>()
const catalogStore = useCatalogStore()

// ── Static filter data (future: fetch from API) ──────────────────
const filterData: Record<string, any> = {
	homens: {
		label: 'Homens',
		subtitle: 'Elegância e precisão para ele.',
		brands: ['Rolex', 'Casio', 'Seiko', 'Omega', 'Tag Heuer'],
		priceRanges: [
			{ label: 'Até €100', min: 0, max: 100 },
			{ label: '€100 – €250', min: 100, max: 250 },
			{ label: '€250 – €500', min: 250, max: 500 },
			{ label: 'Acima de €500', min: 500, max: 999999 },
		],
	},
	mulheres: {
		label: 'Mulheres',
		subtitle: 'Sofisticação em cada detalhe.',
		brands: ['Rolex', 'Casio', 'Seiko', 'Omega', 'Tag Heuer'],
		priceRanges: [
			{ label: 'Até €80', min: 0, max: 80 },
			{ label: '€80 – €200', min: 80, max: 200 },
			{ label: '€200 – €450', min: 200, max: 450 },
			{ label: 'Acima de €450', min: 450, max: 999999 },
		],
	},
	unisexo: {
		label: 'Unisexo',
		subtitle: 'Para quem não segue regras.',
		brands: ['Rolex', 'Casio', 'Seiko', 'Omega', 'Tag Heuer'],
		priceRanges: [
			{ label: 'Até €80', min: 0, max: 80 },
			{ label: '€80 – €200', min: 80, max: 200 },
			{ label: '€200 – €500', min: 200, max: 500 },
			{ label: 'Acima de €500', min: 500, max: 999999 },
		],
	},
}

const meta = computed(() => filterData[props.category] ?? filterData.homens)

// ── Active filters ────────────────────────────────────────────────
const selectedBrands = ref<string[]>([])
const selectedPriceRange = ref<{ label: string; min: number; max: number } | null>(null)

// ── Pagination & Data ──────────────────────────────────────────────
const currentPage = ref(1)
const products = ref<Product[]>([])
const totalProducts = ref(0)
const totalPages = ref(1)

const loadProducts = async () => {
	const params: Record<string, any> = {
		gender: props.category,
		page: currentPage.value,
		per_page: 20
	}

	if (selectedBrands.value.length > 0) {
		params.brand = selectedBrands.value[0].toLowerCase() // A API atual suporta 1 brand por query via slug
	}

	if (selectedPriceRange.value) {
		params.min_price = selectedPriceRange.value.min
		params.max_price = selectedPriceRange.value.max
	}

	const res = await catalogStore.fetchProducts(params)
	if (res) {
		products.value = res.data
		totalProducts.value = res.meta.total
		totalPages.value = res.meta.last_page
	} else {
		products.value = []
		totalProducts.value = 0
		totalPages.value = 1
	}
}

onMounted(() => {
	loadProducts()
})

watch([() => props.category, selectedBrands, selectedPriceRange], () => {
	currentPage.value = 1
	loadProducts()
})

watch(currentPage, () => {
	loadProducts()
})

function toggleBrand(b: string) {
	const i = selectedBrands.value.indexOf(b)
	i === -1 ? selectedBrands.value.push(b) : selectedBrands.value.splice(i, 1)
}
function setPriceRange(r: { label: string; min: number; max: number } | null) {
	selectedPriceRange.value = r
}
function clearAllFilters() {
	selectedBrands.value = []
	selectedPriceRange.value = null
}

const hasActiveFilters = computed(
	() =>
		selectedBrands.value.length > 0 ||
		selectedPriceRange.value !== null,
)
</script>

<template>
	<div class="flex min-h-screen flex-col bg-k-black">
		<Navigation color="k-black" />

		<!-- ── Hero Banner ─────────────────────────────────────────── -->
		<header class="bg-k-black border-b border-white/5">
			<div class="mx-auto flex max-w-6xl items-end gap-6 px-6 py-14">
				<div class="flex items-start gap-5">
					<div class="mt-1.5 h-14 w-[3px] flex-shrink-0 rounded-full bg-[#FFC700]"></div>
					<div>
						<p
							class="mb-2 text-[0.6rem] font-bold uppercase tracking-[0.22em] text-[#FFC700]"
						>
							Coleção
						</p>
						<h1
							class="text-5xl font-black uppercase leading-none tracking-tight text-white"
						>
							{{ meta.label }}
						</h1>
						<p class="mt-3 text-sm text-white/50">{{ meta.subtitle }}</p>
					</div>
				</div>
				<div
					class="ml-auto hidden items-center gap-2 text-xs text-white/40 md:flex"
				>
					<span
						>{{ totalProducts }} relógio{{
							totalProducts !== 1 ? 's' : ''
						}}</span
					>
				</div>
			</div>
		</header>

		<!-- ── Main Layout: Sidebar + Grid ───────────────────────────── -->
		<div class="mx-auto flex w-full max-w-6xl flex-1 gap-8 px-6 py-10">
			<!-- ── Sidebar ─────────────────────────────────────────── -->
			<aside class="hidden w-60 flex-shrink-0 flex-col gap-6 lg:flex">
				<!-- Clear filters -->
				<div class="flex items-center justify-between">
					<p
						class="text-[0.6rem] font-bold uppercase tracking-[0.2em] text-white/40"
					>
						Filtros
					</p>
					<button
						v-if="hasActiveFilters"
						@click="clearAllFilters"
						class="text-[0.6rem] font-bold uppercase tracking-wider text-[#FFC700] transition-colors hover:text-yellow-300"
					>
						Limpar
					</button>
				</div>

				<!-- Marca -->
				<div class="border-white/10 border-t pt-5">
					<p
						class="mb-3 text-[0.6rem] font-bold uppercase tracking-[0.18em] text-[#FFC700]"
					>
						Marca
					</p>
					<ul class="space-y-2">
						<li v-for="brand in meta.brands" :key="brand">
							<label class="group flex cursor-pointer items-center gap-2.5">
								<input
									type="checkbox"
									:checked="selectedBrands.includes(brand)"
									@change="toggleBrand(brand)"
									class="hidden"
								/>
								<span
									class="flex h-4 w-4 flex-shrink-0 items-center justify-center rounded border transition-all duration-300"
									:class="
										selectedBrands.includes(brand)
											? 'border-[#FFC700] bg-[#FFC700]'
											: 'border-white/20 group-hover:border-[#FFC700]'
									"
								>
									<svg
										v-if="selectedBrands.includes(brand)"
										class="h-2.5 w-2.5 text-black"
										fill="none"
										viewBox="0 0 24 24"
										stroke="currentColor"
										stroke-width="3"
									>
										<path
											stroke-linecap="round"
											stroke-linejoin="round"
											d="M5 13l4 4L19 7"
										/>
									</svg>
								</span>
								<span
									class="text-sm transition-colors duration-300"
									:class="
										selectedBrands.includes(brand)
											? 'font-semibold text-white'
											: 'text-white/60 group-hover:text-[#FFC700]'
									"
								>
									{{ brand }}
								</span>
							</label>
						</li>
					</ul>
				</div>

				<!-- Gama de Preço -->
				<div class="border-white/10 border-t pt-5">
					<p
						class="mb-3 text-[0.6rem] font-bold uppercase tracking-[0.18em] text-[#FFC700]"
					>
						Gama de Preço
					</p>
					<ul class="space-y-2">
						<li v-for="range in meta.priceRanges" :key="range.label">
							<button
								@click="
									setPriceRange(
										selectedPriceRange?.label === range.label ? null : range,
									)
								"
								class="group flex w-full items-center gap-2.5 text-left"
							>
								<span
									class="h-4 w-4 flex-shrink-0 rounded-full border transition-all duration-300"
									:class="
										selectedPriceRange?.min === range.min
											? 'border-[#FFC700] bg-[#FFC700]'
											: 'border-white/20 group-hover:border-[#FFC700]'
									"
								></span>
								<span
									class="text-sm transition-colors duration-300"
									:class="
										selectedPriceRange?.min === range.min
											? 'font-semibold text-white'
											: 'text-white/60 group-hover:text-[#FFC700]'
									"
								>
									{{ range.label }}
								</span>
							</button>
						</li>
					</ul>
				</div>

			</aside>

			<!-- ── Product Grid ─────────────────────────────────────── -->
			<div class="min-w-0 flex-1">
				<!-- Active filter chips + count -->
				<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
					<div class="flex flex-wrap items-center gap-2">
						<template v-if="hasActiveFilters">
							<span
								v-for="brand in selectedBrands"
								:key="'b-' + brand"
								class="flex items-center gap-1.5 rounded-full border border-[#FFC700]/30 bg-[#FFC700]/10 px-3 py-1 text-xs font-semibold text-[#FFC700]"
							>
								{{ brand }}
								<button
									@click="toggleBrand(brand)"
									class="transition-colors hover:text-white"
								>
									×
								</button>
							</span>
							<span
								v-if="selectedPriceRange"
								class="flex items-center gap-1.5 rounded-full border border-white/15 bg-white/5 px-3 py-1 text-xs font-semibold text-white/70"
							>
								{{ selectedPriceRange.label }}
								<button
									@click="setPriceRange(null)"
									class="transition-colors hover:text-[#FFC700]"
								>
									×
								</button>
							</span>
						</template>
						<span v-else class="text-xs text-white/25">Sem filtros ativos</span>
					</div>
					<span class="flex-shrink-0 text-xs text-white/40">
						{{ totalProducts }} resultado{{
							totalProducts !== 1 ? 's' : ''
						}}
					</span>
				</div>

				<!-- Grid -->
				<div
					v-if="products.length > 0"
					class="grid grid-cols-2 gap-5 lg:grid-cols-3 xl:grid-cols-4"
				>
					<ProductCard
						v-for="(product, index) in products"
						:item="product"
						:category="props.category"
						:data-test="`product-card-${props.category}-${product.id}`"
						:key="index"
					/>
				</div>

				<!-- Empty state -->
				<div
					v-else
					class="flex flex-col items-center justify-center py-32 text-center"
				>
					<svg
						class="text-white/15 mb-4 h-12 w-12"
						fill="none"
						viewBox="0 0 24 24"
						stroke="currentColor"
					>
						<path
							stroke-linecap="round"
							stroke-linejoin="round"
							stroke-width="1.5"
							d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
						/>
					</svg>
					<p
						class="text-sm font-semibold uppercase tracking-wider text-white/40"
					>
						Sem resultados
					</p>
					<p class="mt-1 text-xs text-white/30">
						Experimenta ajustar os filtros
					</p>
					<button
						@click="clearAllFilters"
						class="mt-5 text-xs font-bold uppercase tracking-wider text-[#FFC700] transition-colors hover:text-yellow-300"
					>
						Limpar Filtros
					</button>
				</div>

				<div
					v-if="totalPages > 1"
					class="mt-12 flex items-center justify-center gap-2"
				>
					<button
						@click="currentPage--"
						:disabled="currentPage === 1"
						class="flex h-9 w-9 items-center justify-center rounded-lg border border-white/15 text-white/40 transition-all duration-300 hover:border-[#FFC700] hover:text-[#FFC700] disabled:cursor-not-allowed disabled:opacity-25 disabled:hover:border-white/15 disabled:hover:text-white/40"
					>
						<svg
							class="h-4 w-4"
							fill="none"
							viewBox="0 0 24 24"
							stroke="currentColor"
						>
							<path
								stroke-linecap="round"
								stroke-linejoin="round"
								stroke-width="2"
								d="M15 19l-7-7 7-7"
							/>
						</svg>
					</button>

					<button
						v-for="page in totalPages"
						:key="page"
						@click="currentPage = page"
						class="h-9 w-9 rounded-lg text-sm font-bold transition-all duration-300"
						:class="
							currentPage === page
								? 'bg-[#FFC700] text-black'
								: 'border border-white/15 text-white/40 hover:border-[#FFC700] hover:text-[#FFC700]'
						"
					>
						{{ page }}
					</button>

					<button
						@click="currentPage++"
						:disabled="currentPage === totalPages"
						class="flex h-9 w-9 items-center justify-center rounded-lg border border-white/15 text-white/40 transition-all duration-300 hover:border-[#FFC700] hover:text-[#FFC700] disabled:cursor-not-allowed disabled:opacity-25 disabled:hover:border-white/15 disabled:hover:text-white/40"
					>
						<svg
							class="h-4 w-4"
							fill="none"
							viewBox="0 0 24 24"
							stroke="currentColor"
						>
							<path
								stroke-linecap="round"
								stroke-linejoin="round"
								stroke-width="2"
								d="M9 5l7 7-7 7"
							/>
						</svg>
					</button>
				</div>
			</div>
		</div>

		<Footer />
	</div>
</template>

