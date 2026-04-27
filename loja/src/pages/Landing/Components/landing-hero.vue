<script lang="ts" setup>
import ButtonSolid from '../../../components/Buttons/button-solid.vue'
import watchImage from '/products/keyboards/relogio2.png'
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
					nova coleção
				</p>
				<h1
					class="relative mt-4 text-5xl font-semibold uppercase text-white md:text-6xl"
				>
					CASIO MTP-1274 <br class="hidden md:block lg:hidden" />
					DARK EDITION
					<br />
					COLEÇÃO PREMIUM
				</h1>
				<p class="mb-10 mt-5 md:opacity-90">
					Na RELOGIOS.inc selecionamos apenas o que resiste ao tempo.
					<br class="hidden md:inline" />
					O Casio MTP-1274 combina aço inoxidável, precisão e um
					<br class="hidden md:inline" />
					design atemporal que se adapta a qualquer ocasião.
				</p>
				<ButtonSolid
					:to="{ name: 'homens', params: { id: 2 } }"
					content="ver relógio"
					color="light"
					add="font-bold mb-20"
				/>
			</div>
			<div
				class="absolute bottom-0 z-0 aspect-auto w-full opacity-30 md:relative md:z-10 md:opacity-100"
			>
				<img
					class="relative top-12 scale-[175%] md:top-20 md:scale-[175%] lg:top-12 lg:scale-150"
					:src="watchImage"
					alt="Casio MTP-1274 Dark Edition"
				/>
			</div>
		</div>
	</section>
</template>
