<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import Navigation from '../../components/navigation-global.vue'
import Footer from '../../components/footer-global.vue'

const visible = ref<Set<string>>(new Set())

let obs: IntersectionObserver
onMounted(() => {
	obs = new IntersectionObserver(
		(entries) => entries.forEach((e) => {
			if (e.isIntersecting) {
				const id = (e.target as HTMLElement).dataset.reveal!
				visible.value.add(id)
			}
		}),
		{ threshold: 0.12 }
	)
	document.querySelectorAll('[data-reveal]').forEach((el) => obs.observe(el))
})
onUnmounted(() => obs?.disconnect())

const values = [
	{ title: 'Precisão', desc: 'Cada mecanismo escolhido pelo seu rigor milimétrico. O tempo não mente — e os nossos relógios também não.', icon: '◎' },
	{ title: 'Elegância', desc: 'Design atemporal que atravessa décadas. Uma peça RELOGIOS.inc nunca sai de moda.', icon: '◈' },
	{ title: 'Identidade', desc: 'O relógio que usas fala por ti antes de dizeres uma palavra. Curámos cada peça para que essa mensagem seja forte.', icon: '◇' },
	{ title: 'Durabilidade', desc: 'Safira, titânio, aço 316L. Materiais que resistem à vida real porque o que vale não se descarta.', icon: '◆' },
]
</script>

<template>
  <Navigation color="black" />

  <main class="about">

    <!-- ── HERO ──────────────────────────────────────────────────── -->
    <section class="hero">
      <div class="hero-inner">
        <div class="hero-text">
          <p class="eyebrow">Sobre Nós</p>
          <h1 class="hero-h1">
            Trazemos os<br />
            <span class="yellow">melhores</span><br />
            relógios.
          </h1>
          <p class="hero-body">
            No coração de Portugal, a RELOGIOS.inc nasceu para servir quem recusa o ordinário.
            Desde 2013 que curámos cada peça com foco em precisão, conforto e design atemporal.
          </p>
          <a href="/relogios" class="btn-yellow">Ver Coleção</a>
        </div>

        <div class="hero-badge">
          <div class="badge-ring ring-a" />
          <div class="badge-ring ring-b" />
          <div class="badge-ring ring-c" />
          <div class="badge-core">
            <span class="badge-year">EST.</span>
            <span class="badge-num">2013</span>
          </div>
        </div>
      </div>
    </section>

    <!-- ── STORY ─────────────────────────────────────────────────── -->
    <section class="story-section" data-reveal="story">
      <div class="story-inner" :class="{ visible: visible.has('story') }">
        <div class="story-text">
          <p class="eyebrow">A Nossa História</p>
          <h2 class="story-h2">
            DOZE ANOS A SERVIR<br />
            <span class="yellow">QUEM EXIGE MAIS.</span>
          </h2>
          <p class="story-body">
            Começámos em 2013 com 14 referências e uma obsessão partilhada por relojoaria.
            Hoje temos mais de 340 modelos — todos seleccionados à mão pela nossa equipa.
          </p>
          <p class="story-body">
            Crescemos sem comprometer o que nos define: rigor na curadoria, honestidade
            nos preços e respeito pelo teu tempo. Literalmente.
          </p>
        </div>

        <div class="timeline">
          <div class="tl-item" v-for="(item, i) in [
            { year: '2013', event: 'Fundação em Braga' },
            { year: '2016', event: 'Primeiras exportações para a Europa' },
            { year: '2019', event: 'Parceria com marcas suíças independentes' },
            { year: '2022', event: 'Lançamento da linha BRACELETES' },
            { year: '2024', event: '28 000 clientes activos' },
          ]" :key="i">
            <div class="tl-year">{{ item.year }}</div>
            <div class="tl-dot" />
            <div class="tl-event">{{ item.event }}</div>
          </div>
        </div>
      </div>
    </section>

    <!-- ── VALUES GRID ───────────────────────────────────────────── -->
    <section class="values-section" data-reveal="values">
      <div class="values-header" :class="{ visible: visible.has('values') }">
        <p class="eyebrow">Os Nossos Pilares</p>
        <h2 class="values-h2">QUATRO PRINCÍPIOS.<br />UMA IDENTIDADE.</h2>
      </div>

      <div class="values-grid" :class="{ visible: visible.has('values') }">
        <div
          class="val-card"
          v-for="(v, i) in values"
          :key="i"
          :style="{ transitionDelay: `${i * 90}ms` }"
        >
          <div class="val-top">
            <span class="val-icon">{{ v.icon }}</span>
            <span class="val-num">0{{ i + 1 }}</span>
          </div>
          <h3 class="val-title">{{ v.title.toUpperCase() }}</h3>
          <p class="val-desc">{{ v.desc }}</p>
          <div class="val-accent" />
        </div>
      </div>
    </section>

    <!-- ── CLOSING CTA ───────────────────────────────────────────── -->
    <section class="cta-section" data-reveal="cta">
      <div class="cta-inner" :class="{ visible: visible.has('cta') }">
        <p class="eyebrow">Pronto para começar?</p>
        <h2 class="cta-h2">ENCONTRA O TEU RELÓGIO.</h2>
        <div class="cta-btns">
          <a href="/relogios" class="btn-yellow">Ver Relógios</a>
          <a href="/braceletes" class="btn-outline">Ver Braceletes</a>
        </div>
      </div>
    </section>

  </main>

  <Footer />
</template>

<style scoped>
/* ── TOKENS ──────────────────────────────────────────────────────── */
.about {
  --yellow: #f5c518;
  --yellow-dark: #d4a800;
  --black: #0d0d0d;
  --dark: #111;
  --mid: #1a1a1a;
  --card: #161616;
  --border: rgba(255,255,255,0.07);
  --text-muted: rgba(255,255,255,0.5);
  --text-body: rgba(255,255,255,0.75);

  background: var(--black);
  color: #fff;
  font-family: 'Arial', sans-serif;
  overflow-x: hidden;
}

.yellow { color: var(--yellow); }

.eyebrow {
  font-size: 0.68rem;
  font-weight: 700;
  letter-spacing: 0.22em;
  text-transform: uppercase;
  color: var(--yellow);
  margin: 0 0 1rem;
}

.eyebrow.dark { color: #0d0d0d; }

/* ── BUTTONS ─────────────────────────────────────────────────────── */
.btn-yellow {
  display: inline-block;
  padding: 0.85rem 2.2rem;
  background: var(--yellow);
  color: #000;
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.15em;
  text-transform: uppercase;
  text-decoration: none;
  transition: background 0.2s, transform 0.2s;
  margin-top: 2rem;
}
.btn-yellow:hover { background: var(--yellow-dark); transform: translateY(-2px); }

.btn-outline {
  display: inline-block;
  padding: 0.85rem 2.2rem;
  border: 2px solid #fff;
  color: #fff;
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.15em;
  text-transform: uppercase;
  text-decoration: none;
  transition: all 0.2s;
  margin-top: 2rem;
}
.btn-outline:hover { background: #fff; color: #000; }

/* ── HERO ────────────────────────────────────────────────────────── */
.hero {
  background: var(--dark);
  border-bottom: 1px solid var(--border);
  padding: 7rem 6vw 6rem;
}

.hero-inner {
  max-width: 1200px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 4rem;
  align-items: center;
}

.hero-h1 {
  font-size: clamp(2.6rem, 5vw, 5rem);
  font-weight: 900;
  line-height: 1.05;
  letter-spacing: -0.02em;
  text-transform: uppercase;
  margin: 0 0 1.5rem;
  animation: fadeUp 0.7s ease both;
}

.hero-body {
  color: var(--text-body);
  font-size: 1rem;
  line-height: 1.8;
  max-width: 480px;
  animation: fadeUp 0.7s 0.15s ease both;
}

@keyframes fadeUp {
  from { opacity: 0; transform: translateY(24px); }
  to   { opacity: 1; transform: none; }
}

.hero-badge {
  position: relative;
  width: 220px;
  height: 220px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  animation: fadeUp 0.7s 0.3s ease both;
}

.badge-ring {
  position: absolute;
  border-radius: 50%;
  border: 1px solid rgba(245, 197, 24, 0.2);
}

.ring-a { width: 100%; height: 100%; animation: spin 20s linear infinite; border-style: dashed; border-color: rgba(245,197,24,0.35); }
.ring-b { width: 78%;  height: 78%;  animation: spin 35s linear infinite reverse; }
.ring-c { width: 56%;  height: 56%;  border-color: rgba(245,197,24,0.5); }

@keyframes spin { to { transform: rotate(360deg); } }

.badge-core {
  position: relative;
  z-index: 2;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  width: 90px;
  height: 90px;
  border-radius: 50%;
  background: var(--yellow);
}

.badge-year {
  font-size: 0.55rem;
  font-weight: 700;
  letter-spacing: 0.2em;
  color: #000;
}

.badge-num {
  font-size: 1.5rem;
  font-weight: 900;
  color: #000;
  line-height: 1;
}

/* ── STORY ───────────────────────────────────────────────────────── */
.story-section {
  background: var(--dark);
  border-top: 1px solid var(--border);
  padding: 7rem 6vw;
}

.story-inner {
  max-width: 1200px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8vw;
  align-items: start;
  opacity: 0;
  transform: translateY(30px);
  transition: all 0.9s ease;
}

.story-inner.visible { opacity: 1; transform: none; }

.story-h2 {
  font-size: clamp(1.6rem, 2.8vw, 2.6rem);
  font-weight: 900;
  line-height: 1.1;
  margin: 0 0 1.5rem;
}

.story-body {
  color: var(--text-body);
  font-size: 0.98rem;
  line-height: 1.85;
  margin-bottom: 1rem;
}

.timeline { display: flex; flex-direction: column; }

.tl-item {
  display: grid;
  grid-template-columns: 52px 10px 1fr;
  align-items: center;
  gap: 1rem;
  padding: 1.2rem 0;
  border-bottom: 1px solid var(--border);
  transition: background 0.2s;
}

.tl-item:last-child { border-bottom: none; }
.tl-item:hover { background: rgba(245,197,24,0.04); }

.tl-year {
  font-size: 0.65rem;
  font-weight: 700;
  letter-spacing: 0.1em;
  color: var(--yellow);
}

.tl-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--yellow);
  justify-self: center;
}

.tl-event {
  font-size: 0.9rem;
  color: var(--text-body);
}

/* ── VALUES ──────────────────────────────────────────────────────── */
.values-section {
  background: var(--black);
  padding: 7rem 6vw;
  border-top: 1px solid var(--border);
}

.values-header {
  max-width: 1200px;
  margin: 0 auto 3.5rem;
  opacity: 0;
  transform: translateY(20px);
  transition: all 0.7s ease;
}

.values-header.visible { opacity: 1; transform: none; }

.values-h2 {
  font-size: clamp(1.8rem, 3vw, 2.8rem);
  font-weight: 900;
  letter-spacing: -0.01em;
  line-height: 1.1;
  margin: 0;
}

.values-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1px;
  background: var(--border);
  border: 1px solid var(--border);
  max-width: 1200px;
  margin: 0 auto;
}

.val-card {
  background: var(--card);
  padding: 2.5rem 2rem;
  position: relative;
  overflow: hidden;
  opacity: 0;
  transform: translateY(18px);
  transition: all 0.6s ease, background 0.25s;
}

.values-grid.visible .val-card { opacity: 1; transform: none; }
.val-card:hover { background: var(--mid); }
.val-card:hover .val-accent { width: 100%; }

.val-top {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.2rem;
}

.val-icon { font-size: 1.4rem; color: var(--yellow); }

.val-num {
  font-size: 0.62rem;
  font-weight: 700;
  letter-spacing: 0.15em;
  color: var(--text-muted);
}

.val-title {
  font-size: 1rem;
  font-weight: 900;
  letter-spacing: 0.05em;
  margin: 0 0 0.9rem;
}

.val-desc {
  font-size: 0.85rem;
  line-height: 1.75;
  color: var(--text-muted);
  margin: 0;
}

.val-accent {
  position: absolute;
  bottom: 0;
  left: 0;
  height: 3px;
  width: 0;
  background: var(--yellow);
  transition: width 0.4s ease;
}

/* ── CTA ─────────────────────────────────────────────────────────── */
.cta-section {
  background: var(--dark);
  border-top: 1px solid var(--border);
  padding: 8rem 6vw;
  text-align: center;
}

.cta-inner {
  opacity: 0;
  transform: translateY(24px);
  transition: all 0.9s ease;
}

.cta-inner.visible { opacity: 1; transform: none; }

.cta-h2 {
  font-size: clamp(2rem, 4.5vw, 4.5rem);
  font-weight: 900;
  letter-spacing: -0.02em;
  line-height: 1.05;
  margin: 0;
}

.cta-btns {
  display: flex;
  gap: 1rem;
  justify-content: center;
  flex-wrap: wrap;
}

/* ── RESPONSIVE ──────────────────────────────────────────────────── */
@media (max-width: 1024px) {
  .hero-inner, .story-inner { grid-template-columns: 1fr; }
  .hero-badge { display: none; }
  .values-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 600px) {
  .values-grid { grid-template-columns: 1fr; }
}
</style>