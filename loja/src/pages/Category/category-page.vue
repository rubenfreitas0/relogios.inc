<script setup lang="ts">
import Navigation from '../../components/navigation-global.vue'
import Footer from '../../components/footer-global.vue'
import ProductCard from './Components/product-card.vue'
import { getProductsOfType } from '../../data/product-utils.ts'
import { computed, ref, watch } from 'vue'

const props = defineProps<{ category: string }>()

// ── Static filter data ────────────────────────────────────────────
const filterData: Record<string, {
	label: string
	subtitle: string
	brands: string[]
	types: string[]
	kinds: string[]
	priceRanges: { label: string; min: number; max: number }[]
	colors: { name: string; hex: string }[]
}> = {
	keyboards: {
		label: 'Homens',
		subtitle: 'Elegância e precisão para ele.',
		brands: ['Casio', 'Seiko', 'Citizen', 'Orient', 'Tissot', 'Festina', 'G-Shock', 'Hugo Boss'],
		types: ['Clássico', 'Desportivo', 'Casual', 'Mergulho', 'Aviador', 'Cronógrafo', 'Militar'],
		kinds: ['Analógico', 'Digital', 'Analógico-Digital', 'Smartwatch'],
		priceRanges: [
			{ label: 'Até €100', min: 0, max: 100 },
			{ label: '€100 – €250', min: 100, max: 250 },
			{ label: '€250 – €500', min: 250, max: 500 },
			{ label: 'Acima de €500', min: 500, max: Infinity },
		],
		colors: [
			{ name: 'Preto', hex: '#1a1a1a' },
			{ name: 'Prata', hex: '#b0b0b0' },
			{ name: 'Dourado', hex: '#c8a44a' },
			{ name: 'Azul', hex: '#1e3a5f' },
			{ name: 'Verde', hex: '#2d5a3d' },
			{ name: 'Branco', hex: '#e8e8e8' },
		],
	},
	keycaps: {
		label: 'Mulheres',
		subtitle: 'Sofisticação em cada detalhe.',
		brands: ['Casio', 'Citizen', 'Michael Kors', 'Anne Klein', 'Festina', 'Tissot', 'Cluse', 'Fossil'],
		types: ['Clássico', 'Elegante', 'Casual', 'Desportivo', 'Minimalista', 'Cronógrafo'],
		kinds: ['Analógico', 'Digital', 'Smartwatch'],
		priceRanges: [
			{ label: 'Até €80', min: 0, max: 80 },
			{ label: '€80 – €200', min: 80, max: 200 },
			{ label: '€200 – €450', min: 200, max: 450 },
			{ label: 'Acima de €450', min: 450, max: Infinity },
		],
		colors: [
			{ name: 'Dourado', hex: '#c8a44a' },
			{ name: 'Rosa Gold', hex: '#b76e79' },
			{ name: 'Prata', hex: '#b0b0b0' },
			{ name: 'Branco', hex: '#e8e8e8' },
			{ name: 'Preto', hex: '#1a1a1a' },
			{ name: 'Rose', hex: '#e8a0a0' },
		],
	},
	deskmats: {
		label: 'Unisexo',
		subtitle: 'Para quem não segue regras.',
		brands: ['Casio', 'Swatch', 'Timex', 'Orient', 'Seiko', 'Garmin', 'Apple', 'Samsung'],
		types: ['Casual', 'Desportivo', 'Smartwatch', 'Minimalista', 'Vintage', 'Outdoor'],
		kinds: ['Analógico', 'Digital', 'Smartwatch', 'Híbrido'],
		priceRanges: [
			{ label: 'Até €80', min: 0, max: 80 },
			{ label: '€80 – €200', min: 80, max: 200 },
			{ label: '€200 – €500', min: 200, max: 500 },
			{ label: 'Acima de €500', min: 500, max: Infinity },
		],
		colors: [
			{ name: 'Preto', hex: '#1a1a1a' },
			{ name: 'Branco', hex: '#e8e8e8' },
			{ name: 'Prata', hex: '#b0b0b0' },
			{ name: 'Laranja', hex: '#d4621a' },
			{ name: 'Verde', hex: '#2d5a3d' },
			{ name: 'Azul', hex: '#1e3a5f' },
		],
	},
}

const meta = computed(() => filterData[props.category] ?? filterData.keyboards)

// ── Active filters ────────────────────────────────────────────────
const selectedBrands = ref<string[]>([])
const selectedTypes = ref<string[]>([])
const selectedKinds = ref<string[]>([])
const selectedPriceRange = ref<{ label: string; min: number; max: number } | null>(null)
const selectedColors = ref<string[]>([])

const PAGE_SIZE = 20
const currentPage = ref(1)

const allProducts = computed(() => getProductsOfType(props.category))

const filteredProducts = computed(() => {
	let list = allProducts.value
	if (selectedPriceRange.value) {
		const { min, max } = selectedPriceRange.value
		list = list.filter(p => (p.price ?? 0) >= min && (p.price ?? 0) <= max)
	}
	return list
})

const totalPages = computed(() => Math.max(1, Math.ceil(filteredProducts.value.length / PAGE_SIZE)))
const paginatedProducts = computed(() => {
	const start = (currentPage.value - 1) * PAGE_SIZE
	return filteredProducts.value.slice(start, start + PAGE_SIZE)
})

watch(filteredProducts, () => { currentPage.value = 1 })

function toggleBrand(b: string) { const i = selectedBrands.value.indexOf(b); i === -1 ? selectedBrands.value.push(b) : selectedBrands.value.splice(i, 1) }
function toggleType(t: string) { const i = selectedTypes.value.indexOf(t); i === -1 ? selectedTypes.value.push(t) : selectedTypes.value.splice(i, 1) }
function toggleKind(k: string) { const i = selectedKinds.value.indexOf(k); i === -1 ? selectedKinds.value.push(k) : selectedKinds.value.splice(i, 1) }
function toggleColor(c: string) { const i = selectedColors.value.indexOf(c); i === -1 ? selectedColors.value.push(c) : selectedColors.value.splice(i, 1) }
function setPriceRange(r: { label: string; min: number; max: number } | null) { selectedPriceRange.value = r }

function clearAllFilters() {
	selectedBrands.value = []
	selectedTypes.value = []
	selectedKinds.value = []
	selectedPriceRange.value = null
	selectedColors.value = []
}

const hasActiveFilters = computed(() =>
	selectedBrands.value.length > 0 || selectedTypes.value.length > 0 ||
	selectedKinds.value.length > 0 || selectedPriceRange.value !== null || selectedColors.value.length > 0
)
</script>

<template>
	<!-- Warm mid-tone background: #f0ede8 — confortável entre preto e branco -->
	<div class="min-h-screen flex flex-col" style="background:#f0ede8">
		<Navigation color="k-black" />

		<!-- ── Hero ───────────────────────────────────────────────── -->
		<header style="background:#1a1a1a">
			<div class="max-w-6xl mx-auto px-6 py-12 flex items-end justify-between gap-4">
				<div>
					<p class="text-[0.6rem] font-bold tracking-[0.25em] uppercase text-[#FFC700] mb-2">Coleção</p>
					<h1 class="text-5xl md:text-6xl font-black uppercase tracking-tight text-white leading-none">
						{{ meta.label }}
					</h1>
					<p class="mt-3 text-zinc-400 text-sm">{{ meta.subtitle }}</p>
				</div>
				<span class="hidden md:block text-zinc-600 text-xs font-medium tracking-wider self-end">
					{{ filteredProducts.length }} relógio{{ filteredProducts.length !== 1 ? 's' : '' }}
				</span>
			</div>
		</header>

		<!-- ── Body ──────────────────────────────────────────────── -->
		<div class="flex-1 max-w-6xl mx-auto w-full px-6 py-10 flex gap-10">

			<!-- ── Sidebar ──────────────────────────────────────── -->
			<aside class="hidden lg:block w-56 flex-shrink-0 self-start sticky top-6">

				<div class="flex items-center justify-between mb-5">
					<p class="text-[0.6rem] font-black tracking-[0.22em] uppercase text-zinc-500">Filtros</p>
					<button v-if="hasActiveFilters" @click="clearAllFilters"
						class="text-[0.6rem] font-bold uppercase tracking-wider text-[#FFC700] hover:underline">
						Limpar
					</button>
				</div>

				<!-- ── Marca ── -->
				<div class="mb-5 pb-5 border-b border-zinc-300/50">
					<p class="text-[0.6rem] font-black tracking-[0.18em] uppercase text-zinc-700 mb-3">Marca</p>
					<ul class="space-y-2.5">
						<li v-for="brand in meta.brands" :key="brand">
							<label class="flex items-center gap-2.5 cursor-pointer group">
								<input type="checkbox" :checked="selectedBrands.includes(brand)" @change="toggleBrand(brand)" class="hidden" />
								<span class="w-4 h-4 rounded border-2 flex items-center justify-center flex-shrink-0 transition-all"
									:class="selectedBrands.includes(brand) ? 'bg-[#FFC700] border-[#FFC700]' : 'border-zinc-400 group-hover:border-zinc-700'">
									<svg v-if="selectedBrands.includes(brand)" class="w-2.5 h-2.5 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
										<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
									</svg>
								</span>
								<span class="text-sm transition-colors"
									:class="selectedBrands.includes(brand) ? 'font-bold text-zinc-900' : 'text-zinc-500 group-hover:text-zinc-800'">
									{{ brand }}
								</span>
							</label>
						</li>
					</ul>
				</div>

				<!-- ── Gama de Preço ── -->
				<div class="mb-5 pb-5 border-b border-zinc-300/50">
					<p class="text-[0.6rem] font-black tracking-[0.18em] uppercase text-zinc-700 mb-3">Gama de Preço</p>
					<ul class="space-y-2.5">
						<li v-for="range in meta.priceRanges" :key="range.label">
							<button @click="setPriceRange(selectedPriceRange?.min === range.min ? null : range)"
								class="flex items-center gap-2.5 w-full group">
								<span class="w-4 h-4 rounded-full border-2 flex-shrink-0 transition-all"
									:class="selectedPriceRange?.min === range.min ? 'bg-zinc-900 border-zinc-900' : 'border-zinc-400 group-hover:border-zinc-700'">
								</span>
								<span class="text-sm transition-colors"
									:class="selectedPriceRange?.min === range.min ? 'font-bold text-zinc-900' : 'text-zinc-500 group-hover:text-zinc-800'">
									{{ range.label }}
								</span>
							</button>
						</li>
					</ul>
				</div>

				<!-- ── Cores ── -->
				<div class="mb-5 pb-5 border-b border-zinc-300/50">
					<p class="text-[0.6rem] font-black tracking-[0.18em] uppercase text-zinc-700 mb-3">Cores</p>
					<div class="flex flex-wrap gap-2.5">
						<button v-for="color in meta.colors" :key="color.name" :title="color.name"
							@click="toggleColor(color.name)"
							class="w-7 h-7 rounded-full transition-all hover:scale-110"
							:class="selectedColors.includes(color.name) ? 'ring-2 ring-[#FFC700] ring-offset-2 scale-110' : 'ring-1 ring-zinc-300'"
							:style="{ backgroundColor: color.hex }">
						</button>
					</div>
				</div>

				<!-- ── Tipo de Relógio ── -->
				<div class="mb-5 pb-5 border-b border-zinc-300/50">
					<p class="text-[0.6rem] font-black tracking-[0.18em] uppercase text-zinc-700 mb-3">Tipo de Relógio</p>
					<ul class="space-y-2.5">
						<li v-for="type in meta.types" :key="type">
							<label class="flex items-center gap-2.5 cursor-pointer group">
								<input type="checkbox" :checked="selectedTypes.includes(type)" @change="toggleType(type)" class="hidden" />
								<span class="w-4 h-4 rounded border-2 flex items-center justify-center flex-shrink-0 transition-all"
									:class="selectedTypes.includes(type) ? 'bg-[#FFC700] border-[#FFC700]' : 'border-zinc-400 group-hover:border-zinc-700'">
									<svg v-if="selectedTypes.includes(type)" class="w-2.5 h-2.5 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
										<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
									</svg>
								</span>
								<span class="text-sm transition-colors"
									:class="selectedTypes.includes(type) ? 'font-bold text-zinc-900' : 'text-zinc-500 group-hover:text-zinc-800'">
									{{ type }}
								</span>
							</label>
						</li>
					</ul>
				</div>

				<!-- ── Mecanismo ── -->
				<div>
					<p class="text-[0.6rem] font-black tracking-[0.18em] uppercase text-zinc-700 mb-3">Mecanismo</p>
					<ul class="space-y-2.5">
						<li v-for="kind in meta.kinds" :key="kind">
							<label class="flex items-center gap-2.5 cursor-pointer group">
								<input type="checkbox" :checked="selectedKinds.includes(kind)" @change="toggleKind(kind)" class="hidden" />
								<span class="w-4 h-4 rounded border-2 flex items-center justify-center flex-shrink-0 transition-all"
									:class="selectedKinds.includes(kind) ? 'bg-[#FFC700] border-[#FFC700]' : 'border-zinc-400 group-hover:border-zinc-700'">
									<svg v-if="selectedKinds.includes(kind)" class="w-2.5 h-2.5 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
										<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
									</svg>
								</span>
								<span class="text-sm transition-colors"
									:class="selectedKinds.includes(kind) ? 'font-bold text-zinc-900' : 'text-zinc-500 group-hover:text-zinc-800'">
									{{ kind }}
								</span>
							</label>
						</li>
					</ul>
				</div>

			</aside>

			<!-- ── Product Area ──────────────────────────────────── -->
			<div class="flex-1 min-w-0">

				<!-- Filter chips -->
				<div class="flex items-center justify-between mb-6 flex-wrap gap-3 min-h-[2rem]">
					<div class="flex flex-wrap gap-2">
						<template v-if="hasActiveFilters">
							<span v-for="brand in selectedBrands" :key="'b-'+brand"
								class="flex items-center gap-1.5 bg-zinc-800 text-white text-xs font-bold px-3 py-1.5 rounded-full">
								{{ brand }} <button @click="toggleBrand(brand)" class="opacity-60 hover:opacity-100">×</button>
							</span>
							<span v-for="type in selectedTypes" :key="'t-'+type"
								class="flex items-center gap-1.5 bg-zinc-800 text-white text-xs font-bold px-3 py-1.5 rounded-full">
								{{ type }} <button @click="toggleType(type)" class="opacity-60 hover:opacity-100">×</button>
							</span>
							<span v-for="kind in selectedKinds" :key="'k-'+kind"
								class="flex items-center gap-1.5 bg-zinc-800 text-white text-xs font-bold px-3 py-1.5 rounded-full">
								{{ kind }} <button @click="toggleKind(kind)" class="opacity-60 hover:opacity-100">×</button>
							</span>
							<span v-if="selectedPriceRange"
								class="flex items-center gap-1.5 bg-zinc-800 text-white text-xs font-bold px-3 py-1.5 rounded-full">
								{{ selectedPriceRange.label }} <button @click="setPriceRange(null)" class="opacity-60 hover:opacity-100">×</button>
							</span>
							<span v-for="color in selectedColors" :key="'c-'+color"
								class="flex items-center gap-1.5 bg-zinc-800 text-white text-xs font-bold px-3 py-1.5 rounded-full">
								{{ color }} <button @click="toggleColor(color)" class="opacity-60 hover:opacity-100">×</button>
							</span>
						</template>
					</div>
					<span class="text-zinc-400 text-xs ml-auto">
						{{ filteredProducts.length }} resultado{{ filteredProducts.length !== 1 ? 's' : '' }}
					</span>
				</div>

				<!-- Grid -->
				<div v-if="paginatedProducts.length > 0" class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
					<ProductCard
						v-for="(product, index) in paginatedProducts"
						:item="product"
						:category="props.category"
						:data-test="`product-card-${product.category}-${product.id}`"
						:key="index"
					/>
				</div>

				<!-- Empty state -->
				<div v-else class="flex flex-col items-center justify-center py-32 text-center">
					<div class="w-14 h-14 rounded-full bg-zinc-200 flex items-center justify-center mb-4">
						<svg class="w-6 h-6 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
						</svg>
					</div>
					<p class="text-zinc-700 text-sm font-bold uppercase tracking-wider">Sem resultados</p>
					<p class="text-zinc-400 text-xs mt-1">Experimenta ajustar os filtros</p>
					<button @click="clearAllFilters" class="mt-5 text-xs font-bold uppercase tracking-wider text-zinc-800 underline hover:text-[#FFC700] transition-colors">
						Limpar Filtros
					</button>
				</div>

				<!-- Pagination -->
				<div v-if="totalPages > 1" class="flex items-center justify-center gap-2 mt-12">
					<button @click="currentPage--" :disabled="currentPage === 1"
						class="w-9 h-9 rounded-lg border border-zinc-300 bg-white flex items-center justify-center text-zinc-400 hover:border-zinc-700 hover:text-zinc-700 disabled:opacity-25 disabled:cursor-not-allowed transition-all">
						<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
					</button>
					<button v-for="page in totalPages" :key="page" @click="currentPage = page"
						class="w-9 h-9 rounded-lg text-sm font-bold transition-all"
						:class="currentPage === page ? 'bg-zinc-900 text-white' : 'bg-white border border-zinc-300 text-zinc-400 hover:border-zinc-700 hover:text-zinc-700'">
						{{ page }}
					</button>
					<button @click="currentPage++" :disabled="currentPage === totalPages"
						class="w-9 h-9 rounded-lg border border-zinc-300 bg-white flex items-center justify-center text-zinc-400 hover:border-zinc-700 hover:text-zinc-700 disabled:opacity-25 disabled:cursor-not-allowed transition-all">
						<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
					</button>
				</div>

			</div>
		</div>

		<Footer />
	</div>
</template>
