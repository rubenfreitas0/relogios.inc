<script setup lang="ts">
import Navigation from '../../components/navigation-global.vue'
import Footer from '../../components/footer-global.vue'
import ButtonSolid from '../../components/Buttons/button-solid.vue'
import watchHeroImage from '/display/watch-hero.png'
import watchDetailImage from '/display/watch-detail.png'
import watchBraceletsImage from '/display/watch-bracelets.png'

const principles = [
  {
    title: 'Precisão',
    desc: 'Cada relógio é selecionado pelo rigor do seu mecanismo. O tempo não mente — e nós também não.',
    icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
  },
  {
    title: 'Elegância',
    desc: 'Design atemporal que atravessa décadas sem perder relevância.',
    icon: 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z',
  },
  {
    title: 'Honestidade',
    desc: 'Preços justos, sem intermediários. Transparência total do início ao fim.',
    icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
  },
  {
    title: 'Durabilidade',
    desc: 'Só trabalhamos com materiais que resistem à vida real: safira, aço 316L, titânio.',
    icon: 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z',
  },
]

const stores = [
  {
    city: 'Braga',
    address: 'Rua do Souto, 142',
    hours: 'Seg–Sex 10h–19h · Sáb 10h–17h',
  },
  {
    city: 'Porto',
    address: 'Rua de Santa Catarina, 87',
    hours: 'Seg–Sex 10h–19h · Sáb 10h–17h',
  },
  {
    city: 'Lisboa',
    address: 'Av. da Liberdade, 230',
    hours: 'Seg–Sex 10h–20h · Sáb 10h–18h',
  },
]

const stats = [
  { value: '2013', label: 'Fundação' },
  { value: '340+', label: 'Modelos' },
  { value: '28K+', label: 'Clientes' },
  { value: '3', label: 'Lojas' },
]

const timeline = [
  {
    year: '2013',
    text: 'Fundação em Braga com 14 referências cuidadosamente escolhidas.',
  },
  {
    year: '2016',
    text: 'Abertura da segunda loja no Porto, na icónica Rua de Santa Catarina.',
  },
  { year: '2019', text: 'Expansão para Lisboa e lançamento da loja online.' },
  {
    year: '2023',
    text: 'Marco de 28 000 clientes ativos e parcerias com mais de 40 marcas.',
  },
  {
    year: '2025',
    text: 'Renovação da plataforma digital com foco em experiência premium.',
  },
]
</script>

<template>
	<div class="flex min-h-screen flex-col bg-k-black">
		<Navigation color="k-black" />

		<!-- ── Hero Banner ──────────────────────────────────────── -->
		<header class="relative overflow-hidden border-b border-white/5 bg-k-black">
			<!-- Background image with overlay -->
			<div class="absolute inset-0">
				<img
					:src="watchDetailImage"
					alt=""
					class="opacity-15 h-full w-full scale-110 object-cover"
				/>
				<div
					class="absolute inset-0 bg-gradient-to-r from-k-black via-k-black/95 to-k-black/70"
				></div>
			</div>

			<div
				class="relative mx-auto flex max-w-6xl flex-col gap-6 px-6 py-20 md:py-28"
			>
				<div class="flex items-start gap-5">
					<div
						class="mt-1.5 h-20 w-[3px] flex-shrink-0 rounded-full bg-k-main"
					></div>
					<div>
						<p
							class="mb-3 text-[0.6rem] font-bold uppercase tracking-[0.22em] text-k-main"
						>
							Sobre Nós
						</p>
						<h1
							class="text-5xl font-black uppercase leading-none tracking-tight text-white md:text-7xl"
						>
							RELOGIOS<span class="text-k-main">.inc</span>
						</h1>
						<p class="mt-4 max-w-lg text-sm leading-relaxed text-white/50">
							Empresa portuguesa especializada em relojoaria de qualidade desde
							2013. Precisão, design e confiança no coração de Portugal.
						</p>
					</div>
				</div>
			</div>
		</header>

		<!-- ── Content ──────────────────────────────────────────── -->
		<main class="mx-auto w-full max-w-6xl flex-1 px-6 py-16">
			<!-- Stats bar -->
			<div class="mb-16 grid grid-cols-2 gap-4 sm:grid-cols-4">
				<div
					v-for="s in stats"
					:key="s.value"
					class="group flex flex-col items-center rounded-lg border border-white/[0.06] bg-k-dark-grey py-7 transition-all duration-300 hover:border-k-main/25"
				>
					<span
						class="text-4xl font-black text-k-main transition-transform duration-300 group-hover:scale-110"
						>{{ s.value }}</span
					>
					<span
						class="text-white/35 mt-1.5 text-[0.65rem] font-semibold uppercase tracking-widest"
						>{{ s.label }}</span
					>
				</div>
			</div>

			<!-- ── A Nossa História + Image ──────────────────────── -->
			<section class="mb-16 grid gap-10 md:grid-cols-2 md:items-center">
				<div>
					<p
						class="mb-2 text-[0.6rem] font-bold uppercase tracking-[0.22em] text-k-main"
					>
						A Nossa História
					</p>
					<h2
						class="mb-5 text-3xl font-black uppercase tracking-tight text-white"
					>
						Fundados em 2013,<br />em Braga.
					</h2>
					<div class="text-white/55 space-y-4 text-sm leading-relaxed">
						<p>
							Começámos com 14 referências cuidadosamente escolhidas e uma
							obsessão partilhada por relojoaria. Hoje contamos com mais de 340
							modelos, 3 lojas físicas em Portugal e mais de 28 000 clientes
							ativos.
						</p>
						<p>
							Crescemos sem comprometer o que nos define: rigor na curadoria,
							honestidade nos preços e respeito genuíno pelo tempo de cada
							cliente.
						</p>
					</div>
					<ButtonSolid
						to="/homens"
						content="explorar coleção"
						color="light"
						add="font-bold mt-8"
					/>
				</div>
				<div class="relative overflow-hidden rounded-xl">
					<img
						:src="watchHeroImage"
						alt="RELOGIOS.inc — tradição e inovação"
						class="aspect-[4/3] h-full w-full rounded-xl object-cover"
					/>
					<!-- Subtle gold corner accent -->
					<div class="absolute bottom-0 left-0 h-1 w-20 bg-k-main"></div>
				</div>
			</section>

			<!-- ── Timeline ──────────────────────────────────────── -->
			<section class="mb-16">
				<p
					class="mb-2 text-[0.6rem] font-bold uppercase tracking-[0.22em] text-k-main"
				>
					A Viagem
				</p>
				<h2
					class="mb-8 text-2xl font-black uppercase tracking-tight text-white"
				>
					O nosso percurso.
				</h2>
				<div class="relative border-l-2 border-white/10 pl-8 md:pl-10">
					<div
						v-for="(event, i) in timeline"
						:key="i"
						class="group relative mb-8 last:mb-0"
					>
						<!-- Dot on the timeline -->
						<div
							class="absolute -left-[2.55rem] top-1 flex h-5 w-5 items-center justify-center rounded-full border-2 border-white/10 bg-k-black transition-colors duration-300 group-hover:border-k-main md:-left-[2.8rem]"
						>
							<div
								class="h-2 w-2 rounded-full bg-white/20 transition-colors duration-300 group-hover:bg-k-main"
							></div>
						</div>
						<span
							class="text-[0.6rem] font-bold uppercase tracking-widest text-k-main"
							>{{ event.year }}</span
						>
						<p class="text-white/55 mt-1 text-sm leading-relaxed">
							{{ event.text }}
						</p>
					</div>
				</div>
			</section>

			<!-- ── Princípios ────────────────────────────────────── -->
			<section class="mb-16">
				<p
					class="mb-2 text-[0.6rem] font-bold uppercase tracking-[0.22em] text-k-main"
				>
					Os Nossos Princípios
				</p>
				<h2
					class="mb-8 text-2xl font-black uppercase tracking-tight text-white"
				>
					O que nos guia.
				</h2>
				<div class="grid gap-4 sm:grid-cols-2">
					<div
						v-for="(p, i) in principles"
						:key="i"
						class="group rounded-lg border border-white/[0.06] bg-k-dark-grey p-6 transition-all duration-300 hover:border-k-main/25"
					>
						<div class="mb-4 flex items-center gap-3">
							<div
								class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-k-main/10 transition-colors duration-300 group-hover:bg-k-main/20"
							>
								<svg
									class="h-5 w-5 text-k-main"
									fill="none"
									viewBox="0 0 24 24"
									stroke="currentColor"
									stroke-width="1.5"
								>
									<path
										stroke-linecap="round"
										stroke-linejoin="round"
										:d="p.icon"
									/>
								</svg>
							</div>
							<div>
								<span
									class="text-white/15 text-[0.55rem] font-bold uppercase tracking-widest"
									>0{{ i + 1 }}</span
								>
								<h3
									class="text-sm font-bold uppercase tracking-wider text-white"
								>
									{{ p.title }}
								</h3>
							</div>
						</div>
						<p class="text-white/45 text-sm leading-relaxed">{{ p.desc }}</p>
					</div>
				</div>
			</section>

			<!-- ── Banner visual intermédio ──────────────────────── -->
			<section class="mb-16 overflow-hidden rounded-xl">
				<div
					class="relative flex h-56 items-center justify-center overflow-hidden md:h-72"
				>
					<img
						:src="watchBraceletsImage"
						alt="Coleção de braceletes"
						class="absolute inset-0 h-full w-full scale-110 object-cover grayscale"
					/>
					<div class="absolute inset-0 bg-black/60"></div>
					<div
						class="relative z-10 flex flex-col items-center gap-4 px-6 text-center"
					>
						<h2
							class="text-3xl font-black uppercase tracking-tight text-white md:text-4xl"
						>
							O tempo é o teu bem mais <span class="text-k-main">precioso</span>
						</h2>
						<p class="max-w-md text-sm text-white/50">
							Usa-o com estilo. Na RELOGIOS.inc selecionamos apenas o que
							resiste ao tempo.
						</p>
					</div>
				</div>
			</section>

			<!-- ── Lojas Físicas ──────────────────────────────────── -->
			<section class="mb-16">
				<p
					class="mb-2 text-[0.6rem] font-bold uppercase tracking-[0.22em] text-k-main"
				>
					Lojas Físicas
				</p>
				<h2
					class="mb-8 text-2xl font-black uppercase tracking-tight text-white"
				>
					Encontra-nos.
				</h2>
				<div class="grid gap-4 sm:grid-cols-3">
					<div
						v-for="store in stores"
						:key="store.city"
						class="group rounded-lg border border-white/[0.06] bg-k-dark-grey p-6 transition-all duration-300 hover:border-k-main/25"
					>
						<div class="mb-4 flex items-center gap-3">
							<div
								class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-k-main/10 transition-colors duration-300 group-hover:bg-k-main/20"
							>
								<svg
									class="h-5 w-5 text-k-main"
									fill="none"
									viewBox="0 0 24 24"
									stroke="currentColor"
									stroke-width="1.5"
								>
									<path
										stroke-linecap="round"
										stroke-linejoin="round"
										d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
									/>
									<path
										stroke-linecap="round"
										stroke-linejoin="round"
										d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
									/>
								</svg>
							</div>
							<h3 class="text-sm font-bold uppercase tracking-wider text-white">
								{{ store.city }}
							</h3>
						</div>
						<p class="text-white/55 text-sm">{{ store.address }}</p>
						<p class="mt-1 text-xs text-white/30">{{ store.hours }}</p>
					</div>
				</div>
			</section>

			<!-- ── Contactos ──────────────────────────────────────── -->
			<section>
				<p
					class="mb-2 text-[0.6rem] font-bold uppercase tracking-[0.22em] text-k-main"
				>
					Contactos
				</p>
				<h2
					class="mb-8 text-2xl font-black uppercase tracking-tight text-white"
				>
					Fala connosco.
				</h2>
				<div class="grid gap-4 sm:grid-cols-3">
					<a
						href="mailto:geral@relogios.inc"
						class="group flex items-start gap-4 rounded-lg border border-white/[0.06] bg-k-dark-grey p-6 transition-all duration-300 hover:border-k-main/25"
					>
						<div
							class="mt-0.5 flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-k-main/10 transition-colors duration-300 group-hover:bg-k-main/20"
						>
							<svg
								class="h-5 w-5 text-k-main"
								fill="none"
								viewBox="0 0 24 24"
								stroke="currentColor"
								stroke-width="1.5"
							>
								<path
									stroke-linecap="round"
									stroke-linejoin="round"
									d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
								/>
							</svg>
						</div>
						<div>
							<p
								class="mb-1 text-[0.6rem] font-bold uppercase tracking-widest text-white/30"
							>
								Email
							</p>
							<p
								class="text-sm text-white/60 transition-colors group-hover:text-white"
							>
								geral@relogios.inc
							</p>
						</div>
					</a>
					<a
						href="tel:+351253000000"
						class="group flex items-start gap-4 rounded-lg border border-white/[0.06] bg-k-dark-grey p-6 transition-all duration-300 hover:border-k-main/25"
					>
						<div
							class="mt-0.5 flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-k-main/10 transition-colors duration-300 group-hover:bg-k-main/20"
						>
							<svg
								class="h-5 w-5 text-k-main"
								fill="none"
								viewBox="0 0 24 24"
								stroke="currentColor"
								stroke-width="1.5"
							>
								<path
									stroke-linecap="round"
									stroke-linejoin="round"
									d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"
								/>
							</svg>
						</div>
						<div>
							<p
								class="mb-1 text-[0.6rem] font-bold uppercase tracking-widest text-white/30"
							>
								Telefone
							</p>
							<p
								class="text-sm text-white/60 transition-colors group-hover:text-white"
							>
								+351 253 000 000
							</p>
						</div>
					</a>
					<div
						class="group flex items-start gap-4 rounded-lg border border-white/[0.06] bg-k-dark-grey p-6 transition-all duration-300 hover:border-k-main/25"
					>
						<div
							class="mt-0.5 flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-k-main/10 transition-colors duration-300 group-hover:bg-k-main/20"
						>
							<svg
								class="h-5 w-5 text-k-main"
								fill="none"
								viewBox="0 0 24 24"
								stroke="currentColor"
								stroke-width="1.5"
							>
								<path
									stroke-linecap="round"
									stroke-linejoin="round"
									d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
								/>
							</svg>
						</div>
						<div>
							<p
								class="mb-1 text-[0.6rem] font-bold uppercase tracking-widest text-white/30"
							>
								Horário de Apoio
							</p>
							<p class="text-sm text-white/60">Seg–Sex, 9h–18h</p>
						</div>
					</div>
				</div>
			</section>
		</main>

		<Footer />
	</div>
</template>
