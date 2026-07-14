<script setup lang="ts">
import Navigation from '../../components/navigation-global.vue'
import Footer from '../../components/footer-global.vue'
import ProductCard from './Components/product-card.vue'
import { useCatalogStore } from '../../pinia/catalogStore'
import type { Product } from '../../data/product-types'
import { computed, ref, watch, onMounted, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const props = defineProps<{ category: string }>()
const catalogStore = useCatalogStore()
const route = useRoute()
const router = useRouter()

interface PriceRange {
  label: string
  min: number
  max: number
}

interface CategoryFilterMeta {
  label: string
  subtitle: string
  brands: string[]
  priceRanges: PriceRange[]
  categories: { slug: string; name: string }[]
  colors: { name: string; hex: string }[]
}

// ── Static filter data (future: fetch from API) ──────────────────
// Estes intervalos têm de corresponder aos definidos no mega menu
// (navigation-global.vue) para cada género, para que os chips de filtro
// e o URL fiquem consistentes entre a sidebar e o mega menu.
const homensPriceRanges: PriceRange[] = [
  { label: 'Até €100', min: 0, max: 100 },
  { label: '€100 – €250', min: 100, max: 250 },
  { label: '€250 – €500', min: 250, max: 500 },
  { label: 'Acima de €500', min: 500, max: 999999 },
]

const mulheresPriceRanges: PriceRange[] = [
  { label: 'Até €80', min: 0, max: 80 },
  { label: '€80 – €200', min: 80, max: 200 },
  { label: '€200 – €450', min: 200, max: 450 },
  { label: 'Acima de €450', min: 450, max: 999999 },
]

const unisexoPriceRanges: PriceRange[] = [
  { label: 'Até €80', min: 0, max: 80 },
  { label: '€80 – €200', min: 80, max: 200 },
  { label: '€200 – €500', min: 200, max: 500 },
  { label: 'Acima de €500', min: 500, max: 999999 },
]

const filterData: Record<string, CategoryFilterMeta> = {
  homens: {
    label: 'Homens',
    subtitle: 'Elegância e precisão para ele.',
    brands: ['Rolex', 'Casio', 'Seiko', 'Omega', 'Tag Heuer'],
    priceRanges: homensPriceRanges,
    categories: [
      { slug: 'classicos', name: 'Clássico' },
      { slug: 'desporto', name: 'Desportivo' },
      { slug: 'mergulho', name: 'Mergulho' },
      { slug: 'cronografos', name: 'Cronógrafo' },
      { slug: 'automaticos', name: 'Automático' },
      { slug: 'digital', name: 'Digital' },
      { slug: 'smartwatch', name: 'Smartwatch' },
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
    brands: ['Rolex', 'Casio', 'Seiko', 'Omega', 'Tag Heuer'],
    priceRanges: mulheresPriceRanges,
    categories: [
      { slug: 'classicos', name: 'Clássico' },
      { slug: 'desporto', name: 'Desportivo' },
      { slug: 'automaticos', name: 'Automático' },
      { slug: 'analogico', name: 'Analógico' },
      { slug: 'smartwatch', name: 'Smartwatch' },
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
    brands: ['Rolex', 'Casio', 'Seiko', 'Omega', 'Tag Heuer'],
    priceRanges: unisexoPriceRanges,
    categories: [
      { slug: 'classicos', name: 'Clássico' },
      { slug: 'desporto', name: 'Desportivo' },
      { slug: 'automaticos', name: 'Automático' },
      { slug: 'digital', name: 'Digital' },
      { slug: 'smartwatch', name: 'Smartwatch' },
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
const selectedPriceRange = ref<{
  label: string
  min: number
  max: number
} | null>(null)
const selectedCategory = ref<string | null>(null)
const selectedColor = ref<string | null>(null)

const categoryNames: Record<string, string> = {
  classicos: 'Clássico',
  desporto: 'Desportivo',
  casual: 'Casual',
  mergulho: 'Mergulho',
  aviador: 'Aviador',
  cronografos: 'Cronógrafo',
  militar: 'Militar',
  automaticos: 'Automático',
  analogico: 'Analógico',
  digital: 'Digital',
  'analogico-digital': 'Analógico-Digital',
  smartwatch: 'Smartwatch',
}

// Flag to prevent circular updates between URL ↔ filters
let syncing = false

// ── Read query params and apply to filters ─────────────────────────
function syncFiltersFromQuery() {
  syncing = true
  const q = route.query

  // Brand: query has slug (lowercase), sidebar has display name
  if (q.brand && typeof q.brand === 'string') {
    const brandSlug = q.brand.toLowerCase()
    const matched = meta.value.brands.find((b) => b.toLowerCase() === brandSlug)
    selectedBrands.value = matched ? [matched] : []
  } else {
    selectedBrands.value = []
  }

  // Price range: from min_price / max_price query params
  if (q.min_price || q.max_price) {
    const minP = q.min_price ? Number(q.min_price) : 0
    const maxP = q.max_price ? Number(q.max_price) : 999999
    const matched = meta.value.priceRanges.find(
      (r) => r.min === minP && r.max === maxP,
    )
    selectedPriceRange.value = matched ?? {
      label: `€${minP} – €${maxP}`,
      min: minP,
      max: maxP,
    }
  } else {
    selectedPriceRange.value = null
  }

  // Category filter
  if (q.category && typeof q.category === 'string') {
    selectedCategory.value = q.category
  } else {
    selectedCategory.value = null
  }

  // Color filter
  if (q.color && typeof q.color === 'string') {
    selectedColor.value = q.color
  } else {
    selectedColor.value = null
  }

  nextTick(() => {
    syncing = false
  })
}

// ── Update URL query params when filters change ─────────────────────
function syncQueryFromFilters() {
  if (syncing) return

  const query: Record<string, string> = {}

  if (selectedBrands.value.length > 0) {
    query.brand = selectedBrands.value[0].toLowerCase()
  }

  if (selectedPriceRange.value) {
    query.min_price = String(selectedPriceRange.value.min)
    query.max_price = String(selectedPriceRange.value.max)
  }

  if (selectedCategory.value) {
    query.category = selectedCategory.value
  }

  if (selectedColor.value) {
    query.color = selectedColor.value
  }

  // Replace URL without triggering navigation (silent update)
  router.replace({ path: `/${props.category}`, query }).catch(() => {})
}

// ── Pagination & Data ──────────────────────────────────────────────
const currentPage = ref(1)
const products = ref<Product[]>([])
const totalProducts = ref(0)
const totalPages = ref(1)

const loadProducts = async () => {
  const params: Record<string, string | number> = {
    gender: props.category,
    page: currentPage.value,
    per_page: 20,
  }

  if (selectedBrands.value.length > 0) {
    params.brand = selectedBrands.value[0].toLowerCase()
  }

  if (selectedPriceRange.value) {
    params.min_price = selectedPriceRange.value.min
    params.max_price = selectedPriceRange.value.max
  }

  if (selectedCategory.value) {
    params.category = selectedCategory.value
  }

  if (selectedColor.value) {
    params.color = selectedColor.value
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

// ── Init: read URL query params on mount ──────────────────────────
onMounted(() => {
  syncFiltersFromQuery()
  nextTick(() => loadProducts())
})

// ── React to URL query changes (e.g. mega menu clicks) ────────────
watch(
  () => route.query,
  () => {
    if (!syncing) {
      syncFiltersFromQuery()
      currentPage.value = 1
      nextTick(() => loadProducts())
    }
  },
  { deep: true },
)

// ── React to category changes (different gender page) ─────────────
watch(
  () => props.category,
  () => {
    syncFiltersFromQuery()
    currentPage.value = 1
    products.value = []
    nextTick(() => loadProducts())
  },
)

// ── React to sidebar filter changes → update URL + reload ─────────
watch(
  [selectedBrands, selectedPriceRange, selectedCategory, selectedColor],
  () => {
    if (!syncing) {
      syncQueryFromFilters()
      currentPage.value = 1
      products.value = []
      loadProducts()
    }
  },
  { deep: true },
)

watch(currentPage, () => {
  loadProducts()
})

function toggleBrand(b: string) {
  const i = selectedBrands.value.indexOf(b)
  const newBrands = [...selectedBrands.value]
  if (i === -1) {
    newBrands.push(b)
  } else {
    newBrands.splice(i, 1)
  }
  selectedBrands.value = newBrands
}
function setPriceRange(r: { label: string; min: number; max: number } | null) {
  selectedPriceRange.value = r
}
function clearAllFilters() {
  selectedBrands.value = []
  selectedPriceRange.value = null
  selectedCategory.value = null
  selectedColor.value = null
}

const hasActiveFilters = computed(
  () =>
    selectedBrands.value.length > 0 ||
    selectedPriceRange.value !== null ||
    selectedCategory.value !== null ||
    selectedColor.value !== null,
)
</script>

<template>
	<div class="flex min-h-screen flex-col bg-k-black">
		<Navigation color="k-black" />

		<!-- ── Hero Banner ─────────────────────────────────────────── -->
		<header class="border-b border-white/5 bg-k-black">
			<div class="mx-auto flex max-w-6xl items-end gap-6 px-6 py-14">
				<div class="flex items-start gap-5">
					<div
						class="mt-1.5 h-14 w-[3px] flex-shrink-0 rounded-full bg-[#FFC700]"
					></div>
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
						class="text-[0.6rem] font-bold uppercase tracking-wider text-[#FFC700] transition-colors hover:text-yellow-300"
						@click="clearAllFilters"
					>
						Limpar
					</button>
				</div>

				<!-- Marca -->
				<div class="border-t border-white/10 pt-5">
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
									class="hidden"
									@change="toggleBrand(brand)"
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
				<div class="border-t border-white/10 pt-5">
					<p
						class="mb-3 text-[0.6rem] font-bold uppercase tracking-[0.18em] text-[#FFC700]"
					>
						Gama de Preço
					</p>
					<ul class="space-y-2">
						<li v-for="range in meta.priceRanges" :key="range.label">
							<button
								class="group flex w-full items-center gap-2.5 text-left"
								@click="
									setPriceRange(
										selectedPriceRange?.label === range.label ? null : range,
									)
								"
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

				<!-- Tipo / Mecanismo -->
				<div
					v-if="meta.categories && meta.categories.length > 0"
					class="border-t border-white/10 pt-5"
				>
					<p
						class="mb-3 text-[0.6rem] font-bold uppercase tracking-[0.18em] text-[#FFC700]"
					>
						Categoria
					</p>
					<ul class="space-y-2">
						<li v-for="cat in meta.categories" :key="cat.slug">
							<button
								class="group flex w-full items-center gap-2.5 text-left"
								@click="
									selectedCategory =
										selectedCategory === cat.slug ? null : cat.slug
								"
							>
								<span
									class="flex h-4 w-4 flex-shrink-0 items-center justify-center rounded border transition-all duration-300"
									:class="
										selectedCategory === cat.slug
											? 'border-[#FFC700] bg-[#FFC700]'
											: 'border-white/20 group-hover:border-[#FFC700]'
									"
								>
									<svg
										v-if="selectedCategory === cat.slug"
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
										selectedCategory === cat.slug
											? 'font-semibold text-white'
											: 'text-white/60 group-hover:text-[#FFC700]'
									"
								>
									{{ cat.name }}
								</span>
							</button>
						</li>
					</ul>
				</div>

				<!-- Cores -->
				<div
					v-if="meta.colors && meta.colors.length > 0"
					class="border-t border-white/10 pt-5"
				>
					<p
						class="mb-3 text-[0.6rem] font-bold uppercase tracking-[0.18em] text-[#FFC700]"
					>
						Cores
					</p>
					<div class="flex flex-wrap gap-2.5 pt-1">
						<button
							v-for="color in meta.colors"
							:key="color.name"
							:title="color.name"
							class="relative flex h-7 w-7 items-center justify-center rounded-full border transition-all duration-200"
							:class="
								selectedColor === color.name
									? 'scale-110 border-[#FFC700]'
									: 'border-white/10 hover:border-white/30'
							"
							:style="{ backgroundColor: color.hex }"
							@click="
								selectedColor = selectedColor === color.name ? null : color.name
							"
						>
							<span
								v-if="selectedColor === color.name"
								class="h-1.5 w-1.5 rounded-full"
								:class="
									color.name === 'Branco' || color.name === 'Prata'
										? 'bg-black'
										: 'bg-white'
								"
							></span>
						</button>
					</div>
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
									class="transition-colors hover:text-white"
									@click="toggleBrand(brand)"
								>
									×
								</button>
							</span>
							<span
								v-if="selectedPriceRange"
								class="border-white/15 flex items-center gap-1.5 rounded-full border bg-white/5 px-3 py-1 text-xs font-semibold text-white/70"
							>
								{{ selectedPriceRange.label }}
								<button
									class="transition-colors hover:text-[#FFC700]"
									@click="setPriceRange(null)"
								>
									×
								</button>
							</span>
							<span
								v-if="selectedCategory"
								class="border-white/15 flex items-center gap-1.5 rounded-full border bg-white/5 px-3 py-1 text-xs font-semibold text-white/70"
							>
								Categoria:
								{{ categoryNames[selectedCategory] || selectedCategory }}
								<button
									class="transition-colors hover:text-[#FFC700]"
									@click="selectedCategory = null"
								>
									×
								</button>
							</span>
							<span
								v-if="selectedColor"
								class="border-white/15 flex items-center gap-1.5 rounded-full border bg-white/5 px-3 py-1 text-xs font-semibold text-white/70"
							>
								Cor: {{ selectedColor }}
								<button
									class="transition-colors hover:text-[#FFC700]"
									@click="selectedColor = null"
								>
									×
								</button>
							</span>
						</template>
						<span v-else class="text-xs text-white/25">Sem filtros ativos</span>
					</div>
					<span class="flex-shrink-0 text-xs text-white/40">
						{{ totalProducts }} resultado{{ totalProducts !== 1 ? 's' : '' }}
					</span>
				</div>

				<!-- Grid -->
				<div
					v-if="products.length > 0"
					class="grid grid-cols-2 gap-5 lg:grid-cols-3 xl:grid-cols-4"
				>
					<ProductCard
						v-for="(product, index) in products"
						:key="product.id"
						:item="product"
						:category="props.category"
						:index="index"
						:data-test="`product-card-${props.category}-${product.id}`"
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
						class="mt-5 text-xs font-bold uppercase tracking-wider text-[#FFC700] transition-colors hover:text-yellow-300"
						@click="clearAllFilters"
					>
						Limpar Filtros
					</button>
				</div>

				<div
					v-if="totalPages > 1"
					class="mt-12 flex items-center justify-center gap-2"
				>
					<button
						:disabled="currentPage === 1"
						class="border-white/15 disabled:hover:border-white/15 flex h-9 w-9 items-center justify-center rounded-lg border text-white/40 transition-all duration-300 hover:border-[#FFC700] hover:text-[#FFC700] disabled:cursor-not-allowed disabled:opacity-25 disabled:hover:text-white/40"
						@click="currentPage--"
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
						class="h-9 w-9 rounded-lg text-sm font-bold transition-all duration-300"
						:class="
							currentPage === page
								? 'bg-[#FFC700] text-black'
								: 'border-white/15 border text-white/40 hover:border-[#FFC700] hover:text-[#FFC700]'
						"
						@click="currentPage = page"
					>
						{{ page }}
					</button>

					<button
						:disabled="currentPage === totalPages"
						class="border-white/15 disabled:hover:border-white/15 flex h-9 w-9 items-center justify-center rounded-lg border text-white/40 transition-all duration-300 hover:border-[#FFC700] hover:text-[#FFC700] disabled:cursor-not-allowed disabled:opacity-25 disabled:hover:text-white/40"
						@click="currentPage++"
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
