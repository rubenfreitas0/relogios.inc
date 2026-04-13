<script setup lang="ts">
import facebookIcon from '/icons/facebook.svg'
import twitterIcon from '/icons/twitter.svg'
import instagramIcon from '/icons/instagram.svg'
</script>

<template>
	<footer
		class="relative mt-20 flex h-full w-screen flex-col items-center bg-black md:mt-32 lg:mt-40"
	>
		<div
			class="flex w-4/5 flex-row justify-center md:w-11/12 md:max-w-6xl md:justify-start lg:w-4/5"
		>
			<div
				id="colorStrip"
				class="absolute top-0 h-1 w-20 max-w-6xl bg-k-main"
			></div>
		</div>
		<div
			id="container"
			class="my-10 flex h-full w-4/5 max-w-6xl flex-col justify-center md:w-11/12 lg:w-4/5"
		>
			<div
				id="titleNav"
				class="mb-10 flex flex-col items-center justify-between md:flex-row"
			>
				<router-link
					to="/"
					class="mb-8 cursor-pointer text-4xl font-bold text-white subpixel-antialiased transition duration-300 hover:scale-110 hover:text-k-main md:mb-0 md:text-3xl"
				>
					RELOGIOS.inc
				</router-link>
				<nav
					class="flex flex-col items-center justify-between gap-6 md:flex-row md:items-start md:gap-10"
				>
					<router-link
						class="font-semibold uppercase tracking-wider text-white transition duration-300 hover:text-k-main"
						to="/"
						>Home
					</router-link>
					<router-link
						class="font-semibold uppercase tracking-wider text-white transition duration-300 hover:text-k-main"
						to="/keyboards"
						>RELÓGIOS
					</router-link>
					<router-link
						class="font-semibold uppercase tracking-wider text-white transition duration-300 hover:text-k-main"
						to="/keycaps"
						>BRACELETES
					</router-link>
					<router-link
						class="font-semibold uppercase tracking-wider text-white transition duration-300 hover:text-k-main"
						to="/sobre-nos"
						>SOBRE NÓS
					</router-link>
				</nav>
			</div>
			<div
				id="textSocials"
				class="flex flex-col items-center justify-between md:flex-row md:items-start"
			>
				<p
					class="mb-10 text-center tracking-wide opacity-70 md:mb-0 md:w-2/3 md:text-left">
					
					A RELOGIOS.inc representa o mais alto padrão em relojoaria. Cada peça <p />
					da nossa coleção é criteriosamente selecionada para quem exige precisão, <p />
					sofisticação e durabilidade. O tempo é o teu bem mais precioso — usa-o com estilo.
				</p>
				<nav class="flex flex-row gap-4 md:self-end">
					<a class="transition duration-300 hover:-translate-y-0.5" href="">
						<img
							loading="lazy"
							class="aspect-square h-6"
							:src="facebookIcon"
							alt=""
						/>
					</a>
					<a class="transition duration-300 hover:-translate-y-0.5" href="">
						<img
							loading="lazy"
							class="aspect-square h-6"
							:src="twitterIcon"
							alt=""
						/>
					</a>
					<a class="transition duration-300 hover:-translate-y-0.5" href="">
						<img
							loading="lazy"
							class="aspect-square h-6"
							:src="instagramIcon"
							alt=""
						/>
					</a>
				</nav>
			</div>
			<p
				class="mt-16 text-center text-sm font-semibold tracking-wide text-white opacity-60 md:text-start"
			>
				Copyright 2026. Todos os Direitos Reservados.
			</p>
		</div>
	</footer>
</template>
