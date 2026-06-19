<script setup lang="ts">
import Navigation from '../../components/navigation-global.vue'
import Core from './Components/product-core.vue'
import Ymal from '../../components/ymal-boxes.vue'
import DetailsExtra from './Components/product-details-extra.vue'
import RecentlyViewed from '../../components/recently-viewed.vue'
import Footer from '../../components/footer-global.vue'

import { useCatalogStore } from '../../pinia/catalogStore'
import type { Product } from '../../data/product-types'
import { ref, onMounted, watch } from 'vue'

const props = defineProps<{
	category: string
	productSlug: string
}>()

const catalogStore = useCatalogStore()
const item = ref<Product | null>(null)

const loadProduct = async () => {
	const data = await catalogStore.fetchProductDetail(props.productSlug)
	if (data) {
		item.value = data
		catalogStore.addToRecentlyViewed(data)
	}
}

onMounted(() => {
	loadProduct()
})

watch(() => props.productSlug, () => {
	loadProduct()
})
</script>

<template>
	<main v-if="item" class="flex h-full w-screen flex-col items-center bg-k-black text-white">
		<Navigation color="black" />
		<Core :item="item" />
		
		<DetailsExtra :item="item" />
		
		<!-- Dark section for Recently Viewed & You May Also Like at the bottom -->
		<div class="w-full bg-k-black text-white flex flex-col items-center py-12 border-t border-white/5">
			<RecentlyViewed :currentSlug="item.slug" />
			<Ymal :productSlug="item.slug" />
		</div>
		
		<Footer />
	</main>
	<div v-else class="flex h-screen w-screen items-center justify-center bg-k-black text-white">
		<p>A carregar produto...</p>
	</div>
</template>
