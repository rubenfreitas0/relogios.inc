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
</script>

<template>
	<div class="min-h-screen bg-[#0d0d0d] text-white flex flex-col">
		<Navigation color="k-black" />

		<main class="flex-1 w-full max-w-5xl mx-auto px-6 py-20 space-y-20">

			<!-- Cabeçalho -->
			<section>
				<p class="text-[0.65rem] font-bold tracking-[0.22em] uppercase text-[#FFC700] mb-3">Sobre Nós</p>
				<h1 class="text-4xl md:text-5xl font-black uppercase tracking-tight leading-tight mb-6">
					RELOGIOS<span class="text-[#FFC700]">.inc</span>
				</h1>
				<p class="text-white/60 text-base leading-relaxed max-w-2xl">
					Somos uma empresa portuguesa especializada em relojoaria de qualidade. Desde 2013 que selecionamos,
					a dedo, cada peça com foco em precisão, conforto e design atemporal — para quem recusa o ordinário.
				</p>
			</section>

			<hr class="border-white/10" />

			<!-- Fundação -->
			<section class="grid md:grid-cols-2 gap-12 items-center">
				<div>
					<p class="text-[0.65rem] font-bold tracking-[0.22em] uppercase text-[#FFC700] mb-3">A Nossa História</p>
					<h2 class="text-2xl font-black uppercase tracking-tight mb-5">Fundados em 2013, em Braga.</h2>
					<p class="text-white/60 text-sm leading-relaxed mb-4">
						Começámos com 14 referências cuidadosamente escolhidas e uma obsessão partilhada por relojoaria.
						Hoje contamos com mais de 340 modelos, 3 lojas físicas em Portugal e mais de 28 000 clientes ativos.
					</p>
					<p class="text-white/60 text-sm leading-relaxed">
						Crescemos sem comprometer o que nos define: rigor na curadoria, honestidade nos preços
						e respeito genuíno pelo tempo de cada cliente.
					</p>
				</div>
				<div class="grid grid-cols-2 gap-4">
					<div v-for="s in [['2013', 'Fundação'], ['340+', 'Modelos'], ['28K+', 'Clientes'], ['3', 'Lojas']]" :key="s[0]"
						class="bg-[#161616] border border-white/8 rounded-lg p-5 text-center">
						<span class="block text-3xl font-black text-[#FFC700]">{{ s[0] }}</span>
						<span class="block text-[0.7rem] font-semibold uppercase tracking-widest text-white/40 mt-1">{{ s[1] }}</span>
					</div>
				</div>
			</section>

			<hr class="border-white/10" />

			<!-- Princípios -->
			<section>
				<p class="text-[0.65rem] font-bold tracking-[0.22em] uppercase text-[#FFC700] mb-3">Os Nossos Princípios</p>
				<h2 class="text-2xl font-black uppercase tracking-tight mb-8">O que nos guia.</h2>
				<div class="grid sm:grid-cols-2 gap-5">
					<div
						v-for="(p, i) in principles" :key="i"
						class="bg-[#161616] border border-white/8 rounded-lg p-6 hover:border-[#FFC700]/30 transition-colors duration-200"
					>
						<span class="text-[0.6rem] font-bold tracking-widest text-white/20 uppercase">0{{ i + 1 }}</span>
						<h3 class="text-sm font-bold uppercase tracking-wider mt-2 mb-2">{{ p.title }}</h3>
						<p class="text-white/50 text-sm leading-relaxed">{{ p.desc }}</p>
					</div>
				</div>
			</section>

			<hr class="border-white/10" />

			<!-- Lojas Físicas -->
			<section>
				<p class="text-[0.65rem] font-bold tracking-[0.22em] uppercase text-[#FFC700] mb-3">Lojas Físicas</p>
				<h2 class="text-2xl font-black uppercase tracking-tight mb-8">Encontra-nos.</h2>
				<div class="grid sm:grid-cols-3 gap-5">
					<div
						v-for="store in stores" :key="store.city"
						class="bg-[#161616] border border-white/8 rounded-lg p-6"
					>
						<div class="flex items-center gap-2 mb-3">
							<svg class="w-4 h-4 text-[#FFC700] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
							</svg>
							<h3 class="font-bold uppercase tracking-wider text-sm">{{ store.city }}</h3>
						</div>
						<p class="text-white/60 text-sm mb-1">{{ store.address }}</p>
						<p class="text-white/35 text-xs leading-relaxed">{{ store.hours }}</p>
					</div>
				</div>
			</section>

			<hr class="border-white/10" />

			<!-- Contactos -->
			<section>
				<p class="text-[0.65rem] font-bold tracking-[0.22em] uppercase text-[#FFC700] mb-3">Contactos</p>
				<h2 class="text-2xl font-black uppercase tracking-tight mb-8">Fala connosco.</h2>
				<div class="grid sm:grid-cols-3 gap-5">
					<a href="mailto:geral@relogios.inc"
						class="flex items-start gap-4 bg-[#161616] border border-white/8 rounded-lg p-6 hover:border-[#FFC700]/30 transition-colors duration-200 group">
						<svg class="w-5 h-5 text-[#FFC700] flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
						</svg>
						<div>
							<p class="text-[0.65rem] font-bold tracking-widest uppercase text-white/30 mb-1">Email</p>
							<p class="text-sm text-white/70 group-hover:text-white transition-colors">geral@relogios.inc</p>
						</div>
					</a>
					<a href="tel:+351253000000"
						class="flex items-start gap-4 bg-[#161616] border border-white/8 rounded-lg p-6 hover:border-[#FFC700]/30 transition-colors duration-200 group">
						<svg class="w-5 h-5 text-[#FFC700] flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
						</svg>
						<div>
							<p class="text-[0.65rem] font-bold tracking-widest uppercase text-white/30 mb-1">Telefone</p>
							<p class="text-sm text-white/70 group-hover:text-white transition-colors">+351 253 000 000</p>
						</div>
					</a>
					<div class="flex items-start gap-4 bg-[#161616] border border-white/8 rounded-lg p-6">
						<svg class="w-5 h-5 text-[#FFC700] flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
						</svg>
						<div>
							<p class="text-[0.65rem] font-bold tracking-widest uppercase text-white/30 mb-1">Horário de Apoio</p>
							<p class="text-sm text-white/70">Seg–Sex, 9h–18h</p>
						</div>
					</div>
				</div>
			</section>

		</main>

		<Footer />
	</div>
</template>