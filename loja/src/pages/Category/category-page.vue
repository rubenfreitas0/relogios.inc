<script setup lang="ts">
import Navigation from '../../components/navigation-global.vue'
import Footer from '../../components/footer-global.vue'
import ProductCard from './Components/product-card.vue'
import { getProductsOfType } from '../../data/product-utils.ts'
import { computed, ref, watch } from 'vue'

const props = defineProps<{ category: string }>()

// ── Static filter data (future: fetch from API) ──────────────────
const filterData: Record<
	string,
	{
		label: string
		subtitle: string
		brands: string[]
		types: string[]
		kinds: string[]
		priceRanges: { label: string; min: number; max: number }[]
		colors: { name: string; hex: string }[]
	}
> = {
	homens: {
		label: 'Homens',
		subtitle: 'Elegância e precisão para ele.',
		brands: [
			'Casio',
			'Seiko',
			'Citizen',
			'Orient',
			'Tissot',
			'Festina',
			'G-Shock',
			'Hugo Boss',
		],
		types: [
			'Clássico',
			'Desportivo',
			'Casual',
			'Mergulho',
			'Aviador',
			'Cronógrafo',
			'Militar',
		],
		kinds: ['Analógico', 'Digital', 'Analógico-Digital', 'Smartwatch'],
		priceRanges: [
			{ label: 'Até €100', min: 0, max: 100 },
			{ label: '€100 – €250', min: 100, max: 250 },
			{ label: '€250 – €500', min: 250, max: 500 },
			{ label: 'Acima de €500', min: 500, max: Infinity },
		],
		colors: [
			{ name: 'Preto', hex: '#1a1a1a' },
			{ name: 'Prata', hex: '#c0c0c0' },
			{ name: 'Dourado', hex: '#c8a44a' },
			{ name: 'Azul', hex: '#1e3a5f' },
			{ name: 'Verde', hex: '#2d5a3d' },
			{ name: 'Branco', hex: '#f0f0f0' },
		],
	},
	mulheres: {
		label: 'Mulheres',
		subtitle: 'Sofisticação em cada detalhe.',
		brands: [
			'Casio',
			'Citizen',
			'Michael Kors',
			'Anne Klein',
			'Festina',
			'Tissot',
			'Cluse',
			'Fossil',
		],
		types: [
			'Clássico',
			'Elegante',
			'Casual',
			'Desportivo',
			'Minimalista',
			'Cronógrafo',
		],
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
			{ name: 'Prata', hex: '#c0c0c0' },
			{ name: 'Branco', hex: '#f0f0f0' },
			{ name: 'Preto', hex: '#1a1a1a' },
			{ name: 'Rose', hex: '#e8a0a0' },
		],
	},
	unisexo: {
		label: 'Unisexo',
		subtitle: 'Para quem não segue regras.',
		brands: [
			'Casio',
			'Swatch',
			'Timex',
			'Orient',
			'Seiko',
			'Garmin',
			'Apple',
			'Samsung',
		],
		types: [
			'Casual',
			'Desportivo',
			'Smartwatch',
			'Minimalista',
			'Vintage',
			'Outdoor',
		],
		kinds: ['Analógico', 'Digital', 'Smartwatch', 'Híbrido'],
		priceRanges: [
			{ label: 'Até €80', min: 0, max: 80 },
			{ label: '€80 – €200', min: 80, max: 200 },
			{ label: '€200 – €500', min: 200, max: 500 },
			{ label: 'Acima de €500', min: 500, max: Infinity },
		],
		colors: [
			{ name: 'Preto', hex: '#1a1a1a' },
			{ name: 'Branco', hex: '#f0f0f0' },
			{ name: 'Prata', hex: '#c0c0c0' },
			{ name: 'Laranja', hex: '#d4621a' },
			{ name: 'Verde', hex: '#2d5a3d' },
			{ name: 'Azul', hex: '#1e3a5f' },
		],
	},
}

const meta = computed(() => filterData[props.category] ?? filterData.homens)

// ── Active filters ────────────────────────────────────────────────
const selectedBrands = ref<string[]>([])
const selectedTypes = ref<string[]>([])
const selectedKinds = ref<string[]>([])
const selectedPriceRange = ref<{ label: string; min: number; max: number } | null>(null)
const selectedColors = ref<string[]>([])

// ── Pagination ────────────────────────────────────────────────────
const PAGE_SIZE = 20
const currentPage = ref(1)

const allProducts = computed(() => getProductsOfType(props.category))

const filteredProducts = computed(() => {
	let list = allProducts.value

	if (selectedPriceRange.value) {
		const { min, max } = selectedPriceRange.value
		list = list.filter((p) => (p.price ?? 0) >= min && (p.price ?? 0) <= max)
	}

	return list
})

const totalPages = computed(() =>
	Math.max(1, Math.ceil(filteredProducts.value.length / PAGE_SIZE)),
)

const paginatedProducts = computed(() => {
	const start = (currentPage.value - 1) * PAGE_SIZE
	return filteredProducts.value.slice(start, start + PAGE_SIZE)
})

watch(filteredProducts, () => {
	currentPage.value = 1
})

function toggleBrand(b: string) {
	const i = selectedBrands.value.indexOf(b)
	i === -1 ? selectedBrands.value.push(b) : selectedBrands.value.splice(i, 1)
}
function toggleType(t: string) {
	const i = selectedTypes.value.indexOf(t)
	i === -1 ? selectedTypes.value.push(t) : selectedTypes.value.splice(i, 1)
}
function toggleKind(k: string) {
	const i = selectedKinds.value.indexOf(k)
	i === -1 ? selectedKinds.value.push(k) : selectedKinds.value.splice(i, 1)
}
function toggleColor(c: string) {
	const i = selectedColors.value.indexOf(c)
	i === -1 ? selectedColors.value.push(c) : selectedColors.value.splice(i, 1)
}
function setPriceRange(r: { label: string; min: number; max: number } | null) {
	selectedPriceRange.value = r
}
function clearAllFilters() {
	selectedBrands.value = []
	selectedTypes.value = []
	selectedKinds.value = []
	selectedPriceRange.value = null
	selectedColors.value = []
}

const hasActiveFilters = computed(
	() =>
		selectedBrands.value.length > 0 ||
		selectedTypes.value.length > 0 ||
		selectedKinds.value.length > 0 ||
		selectedPriceRange.value !== null ||
		selectedColors.value.length > 0,
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
						>{{ filteredProducts.length }} relógio{{
							filteredProducts.length !== 1 ? 's' : ''
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

				<!-- Cores -->
				<div class="border-white/10 border-t pt-5">
					<p
						class="mb-3 text-[0.6rem] font-bold uppercase tracking-[0.18em] text-[#FFC700]"
					>
						Cores
					</p>
					<div class="flex flex-wrap gap-2.5">
						<button
							v-for="color in meta.colors"
							:key="color.name"
							:title="color.name"
							@click="toggleColor(color.name)"
							class="relative h-7 w-7 rounded-full border-2 transition-all duration-300 hover:scale-110"
							:class="
								selectedColors.includes(color.name)
									? 'scale-110 border-[#FFC700]'
									: 'border-white/15 hover:border-[#FFC700]'
							"
							:style="{ backgroundColor: color.hex }"
						>
							<span
								v-if="selectedColors.includes(color.name)"
								class="absolute inset-0 rounded-full ring-2 ring-[#FFC700] ring-offset-1 ring-offset-k-black"
							></span>
						</button>
					</div>
				</div>

				<!-- Tipo de Relógio -->
				<div class="border-white/10 border-t pt-5">
					<p
						class="mb-3 text-[0.6rem] font-bold uppercase tracking-[0.18em] text-[#FFC700]"
					>
						Tipo de Relógio
					</p>
					<ul class="space-y-2">
						<li v-for="type in meta.types" :key="type">
							<label class="group flex cursor-pointer items-center gap-2.5">
								<input
									type="checkbox"
									:checked="selectedTypes.includes(type)"
									@change="toggleType(type)"
									class="hidden"
								/>
								<span
									class="flex h-4 w-4 flex-shrink-0 items-center justify-center rounded border transition-all duration-300"
									:class="
										selectedTypes.includes(type)
											? 'border-[#FFC700] bg-[#FFC700]'
											: 'border-white/20 group-hover:border-[#FFC700]'
									"
								>
									<svg
										v-if="selectedTypes.includes(type)"
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
										selectedTypes.includes(type)
											? 'font-semibold text-white'
											: 'text-white/60 group-hover:text-[#FFC700]'
									"
								>
									{{ type }}
								</span>
							</label>
						</li>
					</ul>
				</div>

				<!-- Mecanismo -->
				<div class="border-white/10 border-t pt-5">
					<p
						class="mb-3 text-[0.6rem] font-bold uppercase tracking-[0.18em] text-[#FFC700]"
					>
						Mecanismo
					</p>
					<ul class="space-y-2">
						<li v-for="kind in meta.kinds" :key="kind">
							<label class="group flex cursor-pointer items-center gap-2.5">
								<input
									type="checkbox"
									:checked="selectedKinds.includes(kind)"
									@change="toggleKind(kind)"
									class="hidden"
								/>
								<span
									class="flex h-4 w-4 flex-shrink-0 items-center justify-center rounded border transition-all duration-300"
									:class="
										selectedKinds.includes(kind)
											? 'border-[#FFC700] bg-[#FFC700]'
											: 'border-white/20 group-hover:border-[#FFC700]'
									"
								>
									<svg
										v-if="selectedKinds.includes(kind)"
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
										selectedKinds.includes(kind)
											? 'font-semibold text-white'
											: 'text-white/60 group-hover:text-[#FFC700]'
									"
								>
									{{ kind }}
								</span>
							</label>
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
								v-for="type in selectedTypes"
								:key="'t-' + type"
								class="flex items-center gap-1.5 rounded-full border border-white/15 bg-white/5 px-3 py-1 text-xs font-semibold text-white/70"
							>
								{{ type }}
								<button
									@click="toggleType(type)"
									class="transition-colors hover:text-[#FFC700]"
								>
									×
								</button>
							</span>
							<span
								v-for="kind in selectedKinds"
								:key="'k-' + kind"
								class="flex items-center gap-1.5 rounded-full border border-white/15 bg-white/5 px-3 py-1 text-xs font-semibold text-white/70"
							>
								{{ kind }}
								<button
									@click="toggleKind(kind)"
									class="transition-colors hover:text-[#FFC700]"
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
							<span
								v-for="color in selectedColors"
								:key="'c-' + color"
								class="flex items-center gap-1.5 rounded-full border border-white/15 bg-white/5 px-3 py-1 text-xs font-semibold text-white/70"
							>
								{{ color }}
								<button
									@click="toggleColor(color)"
									class="transition-colors hover:text-[#FFC700]"
								>
									×
								</button>
							</span>
						</template>
						<span v-else class="text-xs text-white/25">Sem filtros ativos</span>
					</div>
					<span class="flex-shrink-0 text-xs text-white/40">
						{{ filteredProducts.length }} resultado{{
							filteredProducts.length !== 1 ? 's' : ''
						}}
					</span>
				</div>

				<!-- Grid -->
				<div
					v-if="paginatedProducts.length > 0"
					class="grid grid-cols-2 gap-5 lg:grid-cols-3 xl:grid-cols-4"
				>
					<ProductCard
						v-for="(product, index) in paginatedProducts"
						:item="product"
						:category="props.category"
						:data-test="`product-card-${product.category}-${product.id}`"
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

