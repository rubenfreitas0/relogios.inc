<script setup lang="ts">
import type { Product } from '../data/product-types.ts'
import { useCatalogStore } from '../pinia/catalogStore.ts'
import { onMounted, ref, watch } from 'vue'
import { resolveProductImageUrl } from '../utils/utilities'
import ButtonSolid from '../components/Buttons/button-solid.vue'

const props = defineProps<{
	productSlug: string
}>()

const items = ref<Product[]>([])
const catalogStore = useCatalogStore()

const loadRelated = async () => {
	items.value = await catalogStore.fetchRelatedProducts(props.productSlug)
}

onMounted(() => {
	loadRelated()
})

watch(() => props.productSlug, () => {
	loadRelated()
})
</script>

<template>
	<section class="mt-20 flex w-4/5 max-w-6xl flex-col items-center lg:mt-32">
		<h2 class="mb-16 font-Manrope text-3xl font-bold uppercase text-black">
			Também poderá gostar
		</h2>
		<div
			v-if="items.length > 0"
			class="flex flex-col items-center gap-12 lg:grid lg:grid-cols-4 lg:grid-rows-1 lg:gap-6 w-full"
		>
			<div
				class="flex flex-col items-center justify-between gap-8 lg:gap-10"
				v-for="item in items"
				:key="item.id"
			>
				<router-link
					:to="`/${item.category?.slug || 'homens'}/${item.slug}`"
					class="overflow-hidden rounded aspect-[4/5] w-full"
				>
					<img class="object-cover w-full h-full" :src="resolveProductImageUrl(item.primary_image?.url, item.id)" :alt="item.name" loading="lazy" />
				</router-link>
				<h3 class="text-center font-Manrope text-2xl font-semibold text-black">
					{{ item.brand?.name || 'Marca' }} <br class="hidden lg:inline" />
					<span class="capitalize"> {{ item.name }}</span>
				</h3>
				<ButtonSolid
					:to="`/${item.category?.slug || 'homens'}/${item.slug}`"
					color="light"
					content="Ver Relógio"
					size="small"
				/>
			</div>
		</div>
		<div v-else class="text-black/50">
			A procurar produtos semelhantes...
		</div>
	</section>
</template>
