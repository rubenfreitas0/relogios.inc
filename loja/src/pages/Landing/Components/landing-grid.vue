<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useCatalogStore } from '../../../pinia/catalogStore'
import type { Product } from '../../../data/product-types'
import ButtonEmpty from '../../../components/Buttons/button-empty.vue'
import ButtonSolid from '../../../components/Buttons/button-solid.vue'
import watchDetailImage from '/display/watch-detail.png'
import watchBraceletsImage from '/display/watch-bracelets.png'
import watchBraceletsFlatImage from '/display/watch-bracelets-flat.png'

const catalogStore = useCatalogStore()
const featuredProduct = ref<Product | null>(null)

onMounted(async () => {
	const featured = await catalogStore.fetchFeatured()
	if (featured.length > 0) {
		featuredProduct.value = featured[0]
	}
})
</script>

<template>
	<section
		class="mt-20 flex h-full w-full flex-col items-center md:-mb-10 md:h-[70rem]"
	>
		<div
			class="md:grid-rows-7 flex h-full w-4/5 max-w-6xl flex-col items-center gap-4 md:grid md:w-11/12 md:grid-cols-6 md:gap-8 lg:w-4/5"
		>
			<!-- Bloco principal dourado — Destaque do relógio -->
			<div
				id="yellowBox"
				class="flex flex-col items-center overflow-hidden rounded-md bg-k-main text-black md:col-span-full md:row-span-3 md:h-[31rem]"
			>
				<div
					v-if="featuredProduct"
					class="group order-1 flex h-full w-full flex-col items-center justify-center md:order-none md:grid md:grid-cols-7"
				>
					<router-link
						:to="`/${featuredProduct.category?.slug || 'homens'}/${featuredProduct.slug}`"
						class="relative flex h-full w-full flex-col items-center justify-center overflow-hidden md:col-span-4 md:px-10"
					>
						<!-- Glow dourado que aparece no hover -->
						<div class="absolute z-10 h-48 w-48 rounded-full bg-black opacity-0 blur-3xl transition duration-700 group-hover:opacity-30 md:h-72 md:w-72"></div>

						<!-- Relógio: escala suave + ligeiro float no hover -->
						<img
							:src="featuredProduct.primary_image?.url || '/images/placeholder.png'"
							:alt="featuredProduct.name"
							class="relative z-20 my-5 aspect-auto drop-shadow-2xl md:my-0 md:scale-110 md:transition md:duration-700 md:group-hover:scale-125 md:group-hover:-translate-y-3 max-h-80 object-contain"
						/>

						<!-- Anéis decorativos — respiram no hover -->
						<div class="absolute h-[12rem] w-[12rem] rounded-full border border-black opacity-30 transition duration-700 group-hover:scale-110 group-hover:opacity-50 md:h-[18rem] md:w-[18rem]"></div>
						<div class="absolute h-[20rem] w-[20rem] rounded-full border border-black opacity-20 transition duration-1000 group-hover:scale-105 group-hover:opacity-40 md:h-[26rem] md:w-[26rem]"></div>
						<div class="absolute h-[26rem] w-[26rem] rounded-full border border-black opacity-10 transition duration-1000 group-hover:scale-100 group-hover:opacity-30 md:h-[36rem] md:w-[36rem]"></div>
						<div class="absolute h-[32rem] w-[32rem] rounded-full border border-black opacity-10 transition duration-700 group-hover:scale-95 md:h-[46rem] md:w-[46rem]"></div>
					</router-link>

					<div
						class="relative z-10 col-span-3 flex h-full w-full flex-col justify-center bg-k-main md:pl-4 md:pr-20 lg:pl-6 lg:pr-24"
					>
						<h2
							class="text-center text-5xl font-semibold uppercase md:text-start lg:text-6xl"
						>
							{{ featuredProduct.brand?.name || 'Marca' }} <br />
							{{ featuredProduct.name }}
						</h2>
						<p
							class="mb-8 mt-4 text-center tracking-wide md:mb-10 md:text-start line-clamp-3"
						>
							{{ featuredProduct.short_description || featuredProduct.description || 'Um relógio incrível para todas as ocasiões.' }}
						</p>
						<ButtonSolid
							:to="`/${featuredProduct.category?.slug || 'homens'}/${featuredProduct.slug}`"
							content="VER RELÓGIO"
							add="font-semibold"
							class="mb-10 self-center md:mb-0 md:self-start"
						/>
					</div>
				</div>
				<div v-else class="flex h-full items-center justify-center">
					<p class="text-black/50">A carregar destaque...</p>
				</div>
			</div>

			<!-- Bloco texto — Braceletes -->
			<div
				class="order-3 flex w-full flex-col items-center overflow-hidden rounded-md bg-k-grey text-black md:order-none md:col-span-2 md:row-span-2 md:h-full"
			>
				<!-- Imagem de braceletes ao fundo do card -->
				<img
					:src="watchBraceletsFlatImage"
					alt="Braceletes e pulseiras"
					class="h-48 w-full object-cover md:h-56"
				/>
				<div class="flex flex-col items-center gap-4 p-6 md:items-start">
					<h2 class="text-3xl font-semibold uppercase">Braceletes & Pulseiras</h2>
					<ButtonEmpty
						:to="{ path: '/mulheres' }"
						content="ver coleção"
					/>
				</div>
			</div>

			<!-- Bloco imagem — detalhe do relógio (grayscale estilo editorial) -->
			<router-link
				:to="{ path: '/homens' }"
				class="order-2 flex h-64 flex-col items-center justify-center overflow-hidden rounded-md bg-k-grey text-black md:order-none md:col-span-4 md:row-span-2 md:block md:h-full"
			>
				<img
					class="h-full w-full scale-110 object-cover grayscale duration-300 hover:grayscale-0 hover:scale-105 transition"
					:src="watchDetailImage"
					alt="Detalhe de relógio premium"
				/>
			</router-link>

			<!-- Bloco imagem — braceletes -->
			<div
				class="order-4 col-span-3 row-span-2 overflow-hidden rounded-md bg-k-grey text-black md:order-none md:h-full"
			>
				<img
					class="h-full w-full scale-110 object-cover transition duration-300 hover:scale-105"
					:src="watchBraceletsImage"
					alt="Coleção de braceletes e pulseiras"
				/>
			</div>

			<!-- Bloco texto — Ver todos os relógios -->
			<div
				class="order-5 col-span-3 row-span-2 flex w-full flex-col items-center justify-center gap-6 rounded-md bg-k-grey p-6 text-black md:order-none md:h-full md:items-start md:px-16"
			>
				<h2 class="text-3xl font-semibold uppercase">Relógios</h2>
				<ButtonEmpty :to="{ path: '/homens' }" content="ver todos" />
			</div>
		</div>
	</section>
</template>
