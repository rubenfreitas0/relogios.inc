<script setup lang="ts">
import { computed, ref } from 'vue'
import type { Product } from '../../../data/product-types.ts'
import { resolveProductImageUrl, getProductImageStyle } from '../../../utils/utilities'

const props = defineProps<{
	item: Product
}>()

const activeIndex = ref(0)

const allImages = computed(() => {
	const imgs: string[] = []
	if (props.item.images && props.item.images.length > 0) {
		// Ordena por ordem de ordenação para respeitar a galeria
		const sorted = [...props.item.images].sort((a, b) => a.sort_order - b.sort_order)
		sorted.forEach(img => {
			// Usamos props.item.id para carregar o mesmo modelo de relógio
			// Usamos img.sort_order para carregar ângulos diferentes (frente, lado, detalhe)
			const resolved = resolveProductImageUrl(img.url, props.item.id, img.sort_order)
			if (!imgs.includes(resolved)) {
				imgs.push(resolved)
			}
		})
	} else if (props.item.primary_image?.url) {
		imgs.push(resolveProductImageUrl(props.item.primary_image.url, props.item.id, 1))
	}
	if (imgs.length === 0) {
		imgs.push('/images/placeholder.png')
	}
	return imgs
})

function nextImage() {
	activeIndex.value = (activeIndex.value + 1) % allImages.value.length
}

function prevImage() {
	activeIndex.value = (activeIndex.value - 1 + allImages.value.length) % allImages.value.length
}

function selectImage(index: number) {
	activeIndex.value = index
}
</script>

<template>
	<section
		class="mt-16 flex w-4/5 max-w-6xl flex-col gap-12 lg:grid lg:grid-cols-2 lg:gap-16 pb-12 border-b border-white/10"
	>
		<!-- Esquerda: Carrossel de Imagens -->
		<div class="flex flex-col items-center w-full">
			<h2 class="mb-6 text-xl font-bold tracking-wider text-white uppercase self-start">Galeria</h2>
			
			<!-- Moldura Principal do Carrossel -->
			<div class="relative flex aspect-square w-full items-center justify-center overflow-hidden rounded-lg bg-black border border-white/10 max-h-[400px]">
				<button 
					v-if="allImages.length > 1"
					@click="prevImage" 
					class="absolute left-4 z-30 flex h-10 w-10 items-center justify-center rounded-full bg-black/40 text-white border border-white/10 hover:bg-k-main hover:text-k-black transition duration-200 text-lg font-bold"
				>
					&larr;
				</button>
				
				<img 
					:src="allImages[activeIndex]" 
					:alt="props.item.name"
					class="h-full w-full object-contain max-h-[360px] p-4 select-none"
					:style="getProductImageStyle(props.item.id)"
				/>
				
				<button 
					v-if="allImages.length > 1"
					@click="nextImage" 
					class="absolute right-4 z-30 flex h-10 w-10 items-center justify-center rounded-full bg-black/40 text-white border border-white/10 hover:bg-k-main hover:text-k-black transition duration-200 text-lg font-bold"
				>
					&rarr;
				</button>
 
				<!-- Indicador de Posição -->
				<div v-if="allImages.length > 1" class="absolute bottom-4 text-xs font-semibold text-white/50 bg-black/60 px-3 py-1 rounded-full border border-white/5">
					{{ activeIndex + 1 }} / {{ allImages.length }}
				</div>
			</div>
			
			<!-- Miniaturas (Thumbnails) -->
			<div v-if="allImages.length > 1" class="mt-4 flex flex-row flex-wrap justify-center gap-3 w-full">
				<button
					v-for="(img, idx) in allImages"
					:key="idx"
					@click="selectImage(idx)"
					class="h-16 w-16 overflow-hidden rounded-md border-2 bg-black transition duration-200 flex items-center justify-center p-1"
					:class="activeIndex === idx ? 'border-k-main scale-105' : 'border-white/10 hover:border-white/30'"
				>
					<img :src="img" class="h-full w-full object-contain" :style="getProductImageStyle(props.item.id)" />
				</button>
			</div>
		</div>

		<!-- Direita: Especificações Escritas -->
		<div class="flex flex-col w-full">
			<h2 class="mb-6 text-xl font-bold tracking-wider text-white uppercase">Especificações Técnicas</h2>
			
			<!-- Texto de Detalhes -->
			<div class="mb-8">
				<p class="text-sm text-white/70 leading-relaxed text-justify whitespace-pre-line">
					{{ props.item.features || props.item.description || 'Especificações detalhadas do relógio.' }}
				</p>
			</div>
			
			<!-- Tabela de Especificações -->
			<div class="flex flex-col w-full border border-white/10 rounded-lg overflow-hidden bg-black text-sm">
				<div class="flex flex-row border-b border-white/5 px-4 py-3 justify-between">
					<span class="font-bold text-white/50">Marca</span>
					<span class="text-white font-semibold">{{ props.item.brand?.name || 'N/D' }}</span>
				</div>
				<div class="flex flex-row border-b border-white/5 px-4 py-3 justify-between">
					<span class="font-bold text-white/50">Coleção / Categoria</span>
					<span class="text-white font-semibold capitalize">{{ props.item.category?.name || 'Geral' }}</span>
				</div>
				<div class="flex flex-row border-b border-white/5 px-4 py-3 justify-between">
					<span class="font-bold text-white/50">Gênero</span>
					<span class="text-white font-semibold capitalize">{{ props.item.gender || 'Unisexo' }}</span>
				</div>
				<div class="flex flex-row border-b border-white/5 px-4 py-3 justify-between">
					<span class="font-bold text-white/50">Peso</span>
					<span class="text-white font-semibold">{{ props.item.weight ? (Number(props.item.weight) * 1000) + ' g' : 'N/D' }}</span>
				</div>
				<div v-if="props.item.in_the_box && props.item.in_the_box.length > 0" class="flex flex-col px-4 py-3 gap-2">
					<span class="font-bold text-white/50">Conteúdo da Embalagem</span>
					<ul class="list-disc list-inside text-white/80 space-y-1 pl-1">
						<li v-for="(boxItem, bIdx) in props.item.in_the_box" :key="bIdx">
							{{ typeof boxItem === 'object' ? boxItem.content : boxItem }}
						</li>
					</ul>
				</div>
			</div>
		</div>
	</section>
</template>
