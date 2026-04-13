<script setup lang="ts">
import switchesImage from '/display/watch-hero.png'
</script>

<template>
	<section
		class="mt-20 flex h-full flex-col items-center justify-center md:mt-32 lg:mt-40"
	>
		<div
			class="flex h-full w-4/5 max-w-6xl flex-col items-center gap-10 md:h-[38rem] md:w-11/12 md:flex-row md:items-start md:justify-between md:gap-32 lg:h-[40rem] lg:w-4/5"
		>
			<div
				class="flex h-full flex-col items-center justify-center text-center md:w-4/5 md:items-start md:text-start"
			>
				<h3 class="text-4xl font-semibold uppercase text-k-black md:text-5xl text-justify">
					TRAZEMOS OS <br class="hidden xl:inline" />
					<span class="text-k-main">MELHORES</span> RELÓGIOS
				</h3>
				<p class="mt-10 tracking-wide text-black opacity-70 md:mt-6 text-justify">
					No coração de Portugal, a RELOGIOS.inc é a referência 
					em relojoaria de qualidade. Dispomos de uma vasta seleção 
					de relógios clássicos, digitais e braceletes das melhores 
					marcas do mercado. <br />
					<br />
					Visita a nossa loja online e descobre peças únicas 
					que combinam precisão, durabilidade e estilo. 
					Na RELOGIOS.inc o tempo é levado a sério.
				</p>
			</div>
			<div
				class="order-first aspect-square h-full w-full overflow-hidden rounded-md md:order-last md:aspect-auto"
			>
				<img
					loading="lazy"
					class="h-full w-full object-cover"
					:src="switchesImage"
					alt=""
					srcset=""
				/>
			</div>
		</div>
	</section>
</template>
