<script setup lang="ts">
import { computed, ref } from 'vue'
import type { Product } from '../../../data/product-types.ts'
import {
  resolveProductImageUrl,
  getProductImageStyle,
} from '../../../utils/utilities'

const props = defineProps<{
  item: Product
}>()

const activeIndex = ref(0)

const categoryNames = computed(() => {
  const list = props.item.categories
  if (list && list.length > 0) {
    return list.map((c) => c.name).join(', ')
  }
  return props.item.category?.name || 'Geral'
})

const allImages = computed(() => {
  const imgs: string[] = []
  if (props.item.images && props.item.images.length > 0) {
    // Ordena por ordem de ordenação para respeitar a galeria
    const sorted = [...props.item.images].sort(
      (a, b) => a.sort_order - b.sort_order,
    )
    sorted.forEach((img) => {
      // Usamos props.item.id para carregar o mesmo modelo de relógio
      // Usamos img.sort_order para carregar ângulos diferentes (frente, lado, detalhe)
      const resolved = resolveProductImageUrl(
        img.url,
        props.item.id,
        img.sort_order,
      )
      if (!imgs.includes(resolved)) {
        imgs.push(resolved)
      }
    })
  } else if (props.item.primary_image?.url) {
    imgs.push(
      resolveProductImageUrl(props.item.primary_image.url, props.item.id, 1),
    )
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
  activeIndex.value =
    (activeIndex.value - 1 + allImages.value.length) % allImages.value.length
}

function selectImage(index: number) {
  activeIndex.value = index
}
</script>

<template>
	<section
		class="mt-16 flex w-4/5 max-w-6xl flex-col gap-12 border-b border-white/10 pb-12 lg:grid lg:grid-cols-2 lg:gap-16"
	>
		<!-- Esquerda: Carrossel de Imagens -->
		<div class="flex w-full flex-col items-center">
			<h2
				class="mb-6 self-start text-xl font-bold uppercase tracking-wider text-white"
			>
				Galeria
			</h2>

			<!-- Moldura Principal do Carrossel -->
			<div
				class="relative flex aspect-square max-h-[400px] w-full items-center justify-center overflow-hidden rounded-lg border border-white/10 bg-black"
			>
				<button
					v-if="allImages.length > 1"
					class="absolute left-4 z-30 flex h-10 w-10 items-center justify-center rounded-full border border-white/10 bg-black/40 text-lg font-bold text-white transition duration-200 hover:bg-k-main hover:text-k-black"
					@click="prevImage"
				>
					&larr;
				</button>

				<img
					:src="allImages[activeIndex]"
					:alt="props.item.name"
					class="h-full max-h-[360px] w-full select-none object-contain p-4"
					:style="getProductImageStyle(props.item.id)"
				/>

				<button
					v-if="allImages.length > 1"
					class="absolute right-4 z-30 flex h-10 w-10 items-center justify-center rounded-full border border-white/10 bg-black/40 text-lg font-bold text-white transition duration-200 hover:bg-k-main hover:text-k-black"
					@click="nextImage"
				>
					&rarr;
				</button>

				<!-- Indicador de Posição -->
				<div
					v-if="allImages.length > 1"
					class="absolute bottom-4 rounded-full border border-white/5 bg-black/60 px-3 py-1 text-xs font-semibold text-white/50"
				>
					{{ activeIndex + 1 }} / {{ allImages.length }}
				</div>
			</div>

			<!-- Miniaturas (Thumbnails) -->
			<div
				v-if="allImages.length > 1"
				class="mt-4 flex w-full flex-row flex-wrap justify-center gap-3"
			>
				<button
					v-for="(img, idx) in allImages"
					:key="idx"
					class="flex h-16 w-16 items-center justify-center overflow-hidden rounded-md border-2 bg-black p-1 transition duration-200"
					:class="
						activeIndex === idx
							? 'scale-105 border-k-main'
							: 'border-white/10 hover:border-white/30'
					"
					@click="selectImage(idx)"
				>
					<img
						:src="img"
						class="h-full w-full object-contain"
						:style="getProductImageStyle(props.item.id)"
					/>
				</button>
			</div>
		</div>

		<!-- Direita: Especificações Escritas -->
		<div class="flex w-full flex-col">
			<h2 class="mb-6 text-xl font-bold uppercase tracking-wider text-white">
				Especificações Técnicas
			</h2>

			<!-- Texto de Detalhes -->
			<div class="mb-8">
				<p
					class="whitespace-pre-line text-justify text-sm leading-relaxed text-white/70"
				>
					{{
						props.item.features ||
						props.item.description ||
						'Especificações detalhadas do relógio.'
					}}
				</p>
			</div>

			<!-- Tabela de Especificações -->
			<div
				class="flex w-full flex-col overflow-hidden rounded-lg border border-white/10 bg-black text-sm"
			>
				<div
					class="flex flex-row justify-between border-b border-white/5 px-4 py-3"
				>
					<span class="font-bold text-white/50">Marca</span>
					<span class="font-semibold text-white">{{
						props.item.brand?.name || 'N/D'
					}}</span>
				</div>
				<div
					class="flex flex-row justify-between border-b border-white/5 px-4 py-3"
				>
					<span class="font-bold text-white/50">Coleção / Categorias</span>
					<span class="font-semibold capitalize text-white">{{
						categoryNames
					}}</span>
				</div>
				<div
					class="flex flex-row justify-between border-b border-white/5 px-4 py-3"
				>
					<span class="font-bold text-white/50">Gênero</span>
					<span class="font-semibold capitalize text-white">{{
						props.item.gender || 'Unisexo'
					}}</span>
				</div>
				<div
					class="flex flex-row justify-between border-b border-white/5 px-4 py-3"
				>
					<span class="font-bold text-white/50">Peso</span>
					<span class="font-semibold text-white">{{
						props.item.weight ? Number(props.item.weight) * 1000 + ' g' : 'N/D'
					}}</span>
				</div>
				<div
					v-if="props.item.in_the_box && props.item.in_the_box.length > 0"
					class="flex flex-col gap-2 px-4 py-3"
				>
					<span class="font-bold text-white/50">Conteúdo da Embalagem</span>
					<ul class="list-inside list-disc space-y-1 pl-1 text-white/80">
						<li v-for="(boxItem, bIdx) in props.item.in_the_box" :key="bIdx">
							{{ typeof boxItem === 'object' ? boxItem.content : boxItem }}
						</li>
					</ul>
				</div>
			</div>
		</div>
	</section>
</template>
