<script lang="ts" setup>
import ButtonSolid from '../../../components/Buttons/button-solid.vue'
import { useCatalogStore } from '../../../pinia/catalogStore'
import type { Product } from '../../../data/product-types'
import { ref, onMounted } from 'vue'
import fallbackImage from '/products/keyboards/relogio2.png'

const catalogStore = useCatalogStore()
const hero = ref<Product | null>(null)

onMounted(async () => {
	const featured = await catalogStore.fetchFeatured()
	if (featured.length > 0) {
		hero.value = featured[0]
	}
})

const heroImage = ref('')
import { watch } from 'vue'
watch(hero, (p) => {
	heroImage.value = p?.primary_image?.url || fallbackImage
}, { immediate: true })
</script>

<template>
	<section
		class="flex w-full flex-col items-center overflow-hidden rounded-b-md bg-k-black"
	>
		<div
			class="relative mt-20 flex max-w-6xl flex-col text-center transition-transform duration-200 sm:w-4/5 md:grid md:w-11/12 md:grid-cols-2 md:text-start lg:w-4/5"
		>
			<div
				class="relative z-10 flex flex-col items-center justify-center pb-6 sm:ml-0 md:ml-10 md:items-start lg:ml-0"
			>
				<p class="md:text-md text-sm font-light uppercase tracking-broad">
					{{ hero ? 'destaque' : 'nova coleção' }}
				</p>
				<h1
					class="relative mt-4 text-5xl font-semibold uppercase text-white md:text-6xl"
				>
					<template v-if="hero">
						{{ hero.brand?.name || '' }} <br class="hidden md:block lg:hidden" />
						{{ hero.name }}
					</template>
					<template v-else>
						RELOGIOS.inc <br />
						COLEÇÃO PREMIUM
					</template>
				</h1>
				<p class="mb-10 mt-5 md:opacity-90">
					<template v-if="hero">
						{{ hero.short_description || hero.description || 'Na RELOGIOS.inc selecionamos apenas o que resiste ao tempo.' }}
					</template>
					<template v-else>
						Na RELOGIOS.inc selecionamos apenas o que resiste ao tempo.
						<br class="hidden md:inline" />
						Precisão, design e confiança no coração de Portugal.
					</template>
				</p>
				<ButtonSolid
					:to="hero ? `/${hero.category?.slug || 'homens'}/${hero.slug}` : '/homens'"
					:content="hero ? 'ver relógio' : 'explorar'"
					color="light"
					add="font-bold mb-20"
				/>
			</div>
			<div
				class="absolute bottom-0 z-0 aspect-auto w-full opacity-30 md:relative md:z-10 md:opacity-100"
			>
				<img
					class="relative top-12 scale-[175%] md:top-20 md:scale-[175%] lg:top-12 lg:scale-150 transition-opacity duration-500"
					:class="hero ? 'opacity-100' : 'opacity-70'"
					:src="heroImage"
					:alt="hero?.name || 'Relógio em destaque'"
				/>
			</div>
		</div>
	</section>
</template>

