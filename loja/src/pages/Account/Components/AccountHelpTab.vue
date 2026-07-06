<script setup lang="ts">
import { ref } from 'vue'

const openFaq = ref<number | null>(null)
const contactSubject = ref('devolucao')
const contactMessage = ref('')
const isSubmitted = ref(false)

const faqs = [
  {
    q: 'Como inicio o processo de devolução?',
    a: 'Pode iniciar o processo enviando um email para devolucoes@relogios.inc ou preenchendo o formulário abaixo. Indique o número da sua encomenda (ex: ORD-XXXX) e o motivo da devolução. Responderemos com as instruções e a etiqueta de envio gratuito no prazo de 24h úteis.',
  },
  {
    q: 'Qual é o prazo para solicitar um reembolso?',
    a: 'Em conformidade com a legislação portuguesa e europeia (Direito de Livre Resolução), tem o direito de devolver qualquer compra num prazo de 14 dias de calendário a contar da data de receção, sem necessidade de justificação. O reembolso será efetuado pelo mesmo método de pagamento no prazo de 14 dias após validarmos o estado do produto.',
  },
  {
    q: 'Qual é a política de garantia dos relógios?',
    a: 'Todos os nossos relógios têm uma garantia legal de 3 anos contra defeitos de fabrico. A garantia cobre o mecanismo do relógio. Danos causados por uso inadequado, acidentes ou desgaste natural do bracelete/vidro não estão cobertos.',
  },
  {
    q: 'Posso efetuar uma troca em vez de reembolso?',
    a: 'Sim, pode trocar o seu artigo por qualquer outro modelo disponível. O processo de devolução do artigo original é idêntico e, assim que o recebermos, processamos o envio do novo relógio.',
  },
]

function toggleFaq(index: number) {
  openFaq.value = openFaq.value === index ? null : index
}

async function handleContactSubmit() {
  if (!contactMessage.value.trim()) return

  const token = localStorage.getItem('auth_token')
  if (!token) return

  try {
    const res = await fetch('/api/support', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        Authorization: `Bearer ${token}`,
      },
      body: JSON.stringify({
        subject: contactSubject.value,
        message: contactMessage.value,
      }),
    })

    if (res.ok) {
      isSubmitted.value = true
      contactMessage.value = ''
      setTimeout(() => {
        isSubmitted.value = false
      }, 5000)
    }
  } catch (e) {
    console.error('Erro ao enviar mensagem de apoio:', e)
  }
}
</script>

<template>
	<div class="space-y-8">
		<!-- Políticas Principais -->
		<div class="grid gap-6 md:grid-cols-2">
			<!-- Devoluções e Reembolsos -->
			<div class="rounded-xl border border-white/10 bg-white/[0.03] p-6 md:p-8">
				<div class="mb-4 flex items-center gap-3">
					<div
						class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#FFC700]/10 text-[#FFC700]"
					>
						<svg
							class="h-5 w-5"
							fill="none"
							viewBox="0 0 24 24"
							stroke="currentColor"
						>
							<path
								stroke-linecap="round"
								stroke-linejoin="round"
								stroke-width="2"
								d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"
							/>
						</svg>
					</div>
					<h3 class="text-sm font-bold uppercase tracking-wider text-white">
						Devoluções e Reembolsos
					</h3>
				</div>
				<p class="text-xs leading-relaxed text-white/50">
					Garantimos o direito de devolução de <strong>14 dias</strong> após a
					receção dos artigos. O produto deve ser devolvido na sua embalagem
					original, sem sinais de uso, completo e acompanhado da respetiva
					fatura. O reembolso é feito no prazo de 14 dias pelo mesmo método de
					pagamento.
				</p>
			</div>

			<!-- Garantia Legal -->
			<div class="rounded-xl border border-white/10 bg-white/[0.03] p-6 md:p-8">
				<div class="mb-4 flex items-center gap-3">
					<div
						class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#FFC700]/10 text-[#FFC700]"
					>
						<svg
							class="h-5 w-5"
							fill="none"
							viewBox="0 0 24 24"
							stroke="currentColor"
						>
							<path
								stroke-linecap="round"
								stroke-linejoin="round"
								stroke-width="2"
								d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
							/>
						</svg>
					</div>
					<h3 class="text-sm font-bold uppercase tracking-wider text-white">
						Garantia de 3 Anos
					</h3>
				</div>
				<p class="text-xs leading-relaxed text-white/50">
					Todos os nossos relógios dispõem de uma garantia legal de
					<strong>3 anos</strong> contra defeitos de fabrico e anomalias do
					mecanismo, contados a partir da data de entrega, em conformidade com o
					Decreto-Lei n.º 84/2021 em Portugal.
				</p>
			</div>
		</div>

		<!-- FAQs -->
		<div class="rounded-xl border border-white/10 bg-white/[0.03] p-6 md:p-8">
			<h3
				class="mb-6 text-base font-bold uppercase tracking-wider text-[#FFC700]"
			>
				Perguntas Frequentes (FAQ)
			</h3>
			<div class="divide-y divide-white/10">
				<div
					v-for="(faq, index) in faqs"
					:key="index"
					class="py-4 first:pt-0 last:pb-0"
				>
					<button
						class="flex w-full items-center justify-between text-left font-semibold text-white/80 transition hover:text-white"
						@click="toggleFaq(index)"
					>
						<span class="text-sm">{{ faq.q }}</span>
						<svg
							class="h-4 w-4 text-white/40 transition-transform duration-200"
							:class="openFaq === index ? 'rotate-180 text-[#FFC700]' : ''"
							fill="none"
							viewBox="0 0 24 24"
							stroke="currentColor"
						>
							<path
								stroke-linecap="round"
								stroke-linejoin="round"
								stroke-width="2.5"
								d="M19 9l-7 7-7-7"
							/>
						</svg>
					</button>
					<div
						v-show="openFaq === index"
						class="mt-2 text-xs leading-relaxed text-white/50 transition-all duration-300"
					>
						{{ faq.a }}
					</div>
				</div>
			</div>
		</div>

		<!-- Formulário de Contacto -->
		<div class="rounded-xl border border-white/10 bg-white/[0.03] p-6 md:p-8">
			<h3
				class="mb-2 text-base font-bold uppercase tracking-wider text-[#FFC700]"
			>
				Contactar o Apoio ao Cliente
			</h3>
			<p class="mb-6 text-xs text-white/40">
				Precisa de ajuda com uma devolução, reembolso ou garantia? Envie-nos uma
				mensagem direta.
			</p>

			<form class="space-y-4" @submit.prevent="handleContactSubmit">
				<div class="grid gap-4 sm:grid-cols-2">
					<div>
						<label
							class="text-white/45 mb-1.5 block text-[0.65rem] font-bold uppercase tracking-[0.15em]"
						>
							Assunto do Contacto
						</label>
						<select
							v-model="contactSubject"
							class="w-full rounded-lg border border-white/10 bg-[#151515] px-4 py-3 text-sm text-white/70 outline-none focus:border-[#FFC700]/50"
						>
							<option value="devolucao">Devolução de Artigo</option>
							<option value="reembolso">Reembolso em Atraso</option>
							<option value="garantia">Acionar Garantia</option>
							<option value="duvida">Dúvida sobre Produto</option>
						</select>
					</div>
					<div>
						<label
							class="text-white/45 mb-1.5 block text-[0.65rem] font-bold uppercase tracking-[0.15em]"
						>
							Email para Resposta
						</label>
						<p
							class="select-none rounded-lg border border-white/10 bg-white/[0.01] px-4 py-3 text-sm text-white/30"
						>
							Será enviado para o seu email de conta
						</p>
					</div>
				</div>

				<div>
					<label
						class="text-white/45 mb-1.5 block text-[0.65rem] font-bold uppercase tracking-[0.15em]"
					>
						Mensagem / Detalhes do Pedido
					</label>
					<textarea
						v-model="contactMessage"
						rows="4"
						placeholder="Descreva aqui o seu pedido de ajuda, indicando o número da encomenda se aplicável..."
						class="w-full rounded-lg border border-white/10 bg-[#151515] px-4 py-3 text-sm text-white/70 placeholder-white/20 outline-none focus:border-[#FFC700]/50"
						required
					></textarea>
				</div>

				<div class="flex items-center justify-between gap-4">
					<button
						type="submit"
						class="rounded-full bg-[#FFC700] px-6 py-2.5 text-xs font-bold uppercase tracking-wider text-black transition duration-200 hover:bg-yellow-400 active:scale-95"
					>
						Enviar Pedido
					</button>

					<Transition name="fade">
						<span
							v-if="isSubmitted"
							class="flex items-center gap-1.5 text-xs font-semibold text-green-400"
						>
							<svg
								class="h-4 w-4"
								fill="none"
								viewBox="0 0 24 24"
								stroke="currentColor"
							>
								<path
									stroke-linecap="round"
									stroke-linejoin="round"
									stroke-width="2.5"
									d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
								/>
							</svg>
							Mensagem enviada com sucesso! Responderemos em 24h úteis.
						</span>
					</Transition>
				</div>
			</form>
		</div>
	</div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
	transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
	opacity: 0;
}
</style>
