<script setup lang="ts">
import Navigation from '../../components/navigation-global.vue'
import Footer from '../../components/footer-global.vue'

const principles = [
	{ title: 'Precisão', desc: 'Cada relógio é selecionado pelo rigor do seu mecanismo. O tempo não mente — e nós também não.' },
	{ title: 'Elegância', desc: 'Design atemporal que atravessa décadas sem perder relevância.' },
	{ title: 'Honestidade', desc: 'Preços justos, sem intermediários. Transparência total do início ao fim.' },
	{ title: 'Durabilidade', desc: 'Só trabalhamos com materiais que resistem à vida real: safira, aço 316L, titânio.' },
]

const stores = [
	{ city: 'Braga', address: 'Rua do Souto, 142', hours: 'Seg–Sex 10h–19h · Sáb 10h–17h' },
	{ city: 'Porto', address: 'Rua de Santa Catarina, 87', hours: 'Seg–Sex 10h–19h · Sáb 10h–17h' },
	{ city: 'Lisboa', address: 'Av. da Liberdade, 230', hours: 'Seg–Sex 10h–20h · Sáb 10h–18h' },
]

const stats = [
	['2013', 'Fundação'],
	['340+', 'Modelos'],
	['28K+', 'Clientes'],
	['3', 'Lojas'],
]
</script>

<template>
	<div class="flex min-h-screen flex-col bg-k-black">
		<Navigation color="k-black" />

		<!-- ── Hero Banner (matches Category page pattern) ──────── -->
		<header class="border-b border-white/5 bg-k-black">
			<div class="mx-auto flex max-w-6xl items-end gap-6 px-6 py-14">
				<div class="flex items-start gap-5">
					<div class="mt-1.5 h-14 w-[3px] flex-shrink-0 rounded-full bg-k-main"></div>
					<div>
						<p class="mb-2 text-[0.6rem] font-bold uppercase tracking-[0.22em] text-k-main">
							Sobre Nós
						</p>
						<h1 class="text-5xl font-black uppercase leading-none tracking-tight text-white">
							RELOGIOS<span class="text-k-main">.inc</span>
						</h1>
						<p class="mt-3 max-w-lg text-sm text-white/50">
							Empresa portuguesa especializada em relojoaria de qualidade desde 2013.
						</p>
					</div>
				</div>
			</div>
		</header>

		<!-- ── Content ──────────────────────────────────────────── -->
		<main class="mx-auto w-full max-w-6xl flex-1 px-6 py-14">

			<!-- Stats bar -->
			<div class="mb-14 grid grid-cols-2 gap-4 sm:grid-cols-4">
				<div
					v-for="s in stats" :key="s[0]"
					class="flex flex-col items-center rounded-lg border border-white/[0.06] bg-k-dark-grey py-6"
				>
					<span class="text-3xl font-black text-k-main">{{ s[0] }}</span>
					<span class="mt-1 text-[0.65rem] font-semibold uppercase tracking-widest text-white/35">{{ s[1] }}</span>
				</div>
			</div>

			<!-- A Nossa História -->
			<section class="mb-14">
				<p class="mb-2 text-[0.6rem] font-bold uppercase tracking-[0.22em] text-k-main">A Nossa História</p>
				<h2 class="mb-5 text-2xl font-black uppercase tracking-tight text-white">Fundados em 2013, em Braga.</h2>
				<div class="max-w-2xl space-y-4 text-sm leading-relaxed text-white/55">
					<p>
						Começámos com 14 referências cuidadosamente escolhidas e uma obsessão partilhada por relojoaria.
						Hoje contamos com mais de 340 modelos, 3 lojas físicas em Portugal e mais de 28 000 clientes ativos.
					</p>
					<p>
						Crescemos sem comprometer o que nos define: rigor na curadoria, honestidade nos preços
						e respeito genuíno pelo tempo de cada cliente.
					</p>
				</div>
			</section>

			<!-- Princípios -->
			<section class="mb-14">
				<p class="mb-2 text-[0.6rem] font-bold uppercase tracking-[0.22em] text-k-main">Os Nossos Princípios</p>
				<h2 class="mb-6 text-2xl font-black uppercase tracking-tight text-white">O que nos guia.</h2>
				<div class="grid gap-4 sm:grid-cols-2">
					<div
						v-for="(p, i) in principles" :key="i"
						class="rounded-lg border border-white/[0.06] bg-k-dark-grey p-6 transition-colors duration-300 hover:border-k-main/25"
					>
						<span class="text-[0.6rem] font-bold uppercase tracking-widest text-white/15">0{{ i + 1 }}</span>
						<h3 class="mb-2 mt-2 text-sm font-bold uppercase tracking-wider text-white">{{ p.title }}</h3>
						<p class="text-sm leading-relaxed text-white/45">{{ p.desc }}</p>
					</div>
				</div>
			</section>

			<!-- Lojas Físicas -->
			<section class="mb-14">
				<p class="mb-2 text-[0.6rem] font-bold uppercase tracking-[0.22em] text-k-main">Lojas Físicas</p>
				<h2 class="mb-6 text-2xl font-black uppercase tracking-tight text-white">Encontra-nos.</h2>
				<div class="grid gap-4 sm:grid-cols-3">
					<div
						v-for="store in stores" :key="store.city"
						class="rounded-lg border border-white/[0.06] bg-k-dark-grey p-6"
					>
						<div class="mb-3 flex items-center gap-2">
							<svg class="h-4 w-4 flex-shrink-0 text-k-main" fill="none" viewBox="0 0 24 24" stroke="currentColor">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
							</svg>
							<h3 class="text-sm font-bold uppercase tracking-wider text-white">{{ store.city }}</h3>
						</div>
						<p class="text-sm text-white/55">{{ store.address }}</p>
						<p class="mt-1 text-xs text-white/30">{{ store.hours }}</p>
					</div>
				</div>
			</section>

			<!-- Contactos -->
			<section>
				<p class="mb-2 text-[0.6rem] font-bold uppercase tracking-[0.22em] text-k-main">Contactos</p>
				<h2 class="mb-6 text-2xl font-black uppercase tracking-tight text-white">Fala connosco.</h2>
				<div class="grid gap-4 sm:grid-cols-3">
					<a
						href="mailto:geral@relogios.inc"
						class="group flex items-start gap-4 rounded-lg border border-white/[0.06] bg-k-dark-grey p-6 transition-colors duration-300 hover:border-k-main/25"
					>
						<svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-k-main" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
						</svg>
						<div>
							<p class="mb-1 text-[0.6rem] font-bold uppercase tracking-widest text-white/30">Email</p>
							<p class="text-sm text-white/60 transition-colors group-hover:text-white">geral@relogios.inc</p>
						</div>
					</a>
					<a
						href="tel:+351253000000"
						class="group flex items-start gap-4 rounded-lg border border-white/[0.06] bg-k-dark-grey p-6 transition-colors duration-300 hover:border-k-main/25"
					>
						<svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-k-main" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
						</svg>
						<div>
							<p class="mb-1 text-[0.6rem] font-bold uppercase tracking-widest text-white/30">Telefone</p>
							<p class="text-sm text-white/60 transition-colors group-hover:text-white">+351 253 000 000</p>
						</div>
					</a>
					<div class="flex items-start gap-4 rounded-lg border border-white/[0.06] bg-k-dark-grey p-6">
						<svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-k-main" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
						</svg>
						<div>
							<p class="mb-1 text-[0.6rem] font-bold uppercase tracking-widest text-white/30">Horário de Apoio</p>
							<p class="text-sm text-white/60">Seg–Sex, 9h–18h</p>
						</div>
					</div>
				</div>
			</section>

		</main>

		<Footer />
	</div>
</template>