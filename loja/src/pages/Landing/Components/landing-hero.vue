<script setup lang="ts">
import { ref, onMounted } from 'vue'
import ButtonSolid from '../../../components/Buttons/button-solid.vue'
const defaultWatchImage = '/products/keyboards/relogio2.png'

const heroData = ref({
  hero_subtitle: 'nova coleção',
  hero_title: 'CASIO MTP-1274 \n DARK EDITION \n COLEÇÃO PREMIUM',
  hero_description: 'Na RELOGIOS.inc selecionamos apenas o que resiste ao tempo.\nO Casio MTP-1274 combina aço inoxidável, precisão e um\ndesign atemporal que se adapta a qualquer ocasião.',
  hero_image: defaultWatchImage,
  hero_link: '/homens',
  hero_button_text: 'ver relógio',
})

onMounted(async () => {
  try {
    const res = await fetch('/api/site-settings/hero')
    if (res.ok) {
      const data = await res.json()
      if (data.hero_subtitle) heroData.value.hero_subtitle = data.hero_subtitle
      if (data.hero_title) heroData.value.hero_title = data.hero_title
      if (data.hero_description) heroData.value.hero_description = data.hero_description
      if (data.hero_image) {
        // Se a imagem for caminho do Laravel (ex: site/imagem.png), formatar a url
        if (!data.hero_image.startsWith('http') && !data.hero_image.startsWith('/')) {
          heroData.value.hero_image = `/api/storage/${data.hero_image}`
        } else {
          heroData.value.hero_image = data.hero_image
        }
      }
      if (data.hero_link) heroData.value.hero_link = data.hero_link
      if (data.hero_button_text) heroData.value.hero_button_text = data.hero_button_text
    }
  } catch (e) {
    console.error('Erro ao buscar definições do Hero:', e)
  }
})
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
					{{ heroData.hero_subtitle }}
				</p>
				<h1
					class="whitespace-pre-line relative mt-4 text-5xl font-semibold uppercase text-white md:text-6xl"
				>
					{{ heroData.hero_title }}
				</h1>
				<p 
					class="whitespace-pre-line mb-10 mt-5 md:opacity-90"
				>
					{{ heroData.hero_description }}
				</p>
				<ButtonSolid
					:to="heroData.hero_link"
					:content="heroData.hero_button_text"
					color="light"
					add="font-bold mb-20"
				/>
			</div>
			<div
				class="absolute bottom-0 z-0 aspect-auto w-full opacity-30 md:relative md:z-10 md:opacity-100"
			>
				<img
					class="relative top-12 scale-[175%] md:top-20 md:scale-[175%] lg:top-12 lg:scale-150"
					:src="heroData.hero_image"
					alt="Casio MTP-1274 Dark Edition"
				/>
			</div>
		</div>
	</section>
</template>
