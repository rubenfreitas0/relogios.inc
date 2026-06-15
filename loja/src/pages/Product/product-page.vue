<script setup lang="ts">
import Navigation from '../../components/navigation-global.vue'
import Core from './Components/product-core.vue'
import Ymal from '../../components/ymal-boxes.vue'
import Grid from './Components/product-image-grid.vue'
import Info from '../../components/info-section.vue'
import Footer from '../../components/footer-global.vue'
import Features from './Components/product-features.vue'

import { useCatalogStore } from '../../pinia/catalogStore'
import type { Product } from '../../data/product-types'
import { ref, onMounted, watch } from 'vue'
import { resolveProductImageUrl } from '../../utils/utilities'

const props = defineProps<{
	category: string
	productSlug: string
}>()

const catalogStore = useCatalogStore()
const item = ref<Product | null>(null)

const loadProduct = async () => {
	item.value = await catalogStore.fetchProductDetail(props.productSlug)
}

onMounted(() => {
	loadProduct()
})

watch(() => props.productSlug, () => {
	loadProduct()
})
</script>

<template>
	<main v-if="item" class="flex h-full w-screen flex-col items-center bg-white">
		<Navigation color="black" />
		<Core :item="item" />
		<Features :features="item.features || ''" :inthebox="item.inthebox || []" />
		<Grid
			:topSrc="resolveProductImageUrl(item.images?.[1]?.url || item.primary_image?.url || '', item.id + 1)"
			:botSrc="resolveProductImageUrl(item.images?.[2]?.url || item.primary_image?.url || '', item.id + 2)"
			:rightSrc="resolveProductImageUrl(item.images?.[3]?.url || item.primary_image?.url || '', item.id + 3)"
		/>
		<Ymal :productSlug="item.slug" />
		<Info />
		<Footer />
	</main>
	<div v-else class="flex h-screen w-screen items-center justify-center bg-white text-black">
		<p>A carregar produto...</p>
	</div>
</template>
