<template>
  <div class="tickets-page">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
      <h1 class="page-title font-bold text-2xl">Tickets de Suporte</h1>
      <VaButton preset="secondary" icon="sync" :loading="store.loading" @click="applyFilters"> Atualizar </VaButton>
    </div>

    <!-- Filtros -->
    <VaCard class="mb-4">
      <VaCardContent>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <VaInput
            v-model="filters.search"
            placeholder="Pesquisar por utilizador, assunto..."
            clearable
            @update:modelValue="debouncedFetch"
          >
            <template #prependInner>
              <VaIcon name="search" size="small" color="secondary" />
            </template>
          </VaInput>

          <VaSelect
            v-model="filters.status"
            :options="statusFilterOptions"
            placeholder="Todos os estados"
            clearable
            text-by="text"
            value-by="value"
            @update:modelValue="applyFilters"
          />

          <VaSelect
            v-model="filters.subject"
            :options="subjectFilterOptions"
            placeholder="Todos os assuntos"
            clearable
            text-by="text"
            value-by="value"
            @update:modelValue="applyFilters"
          />
        </div>
      </VaCardContent>
    </VaCard>

    <!-- Tabela / Resultados -->
    <VaCard>
      <VaCardContent>
        <div v-if="store.loading" class="flex justify-center py-12">
          <VaProgressCircle indeterminate size="large" />
        </div>

        <template v-else>
          <!-- Alerta de erro do store -->
          <VaAlert v-if="store.error" color="danger" class="mb-4" closeable @update:modelValue="store.error = null">
            {{ store.error }}
          </VaAlert>

          <!-- Tabela principal -->
          <VaDataTable
            v-if="store.tickets.length > 0"
            :items="store.tickets"
            :columns="columns"
            striped
            hoverable
            class="w-full"
          >
            <!-- Coluna Utilizador -->
            <template #cell(user)="{ rowData }">
              <div class="flex flex-col">
                <span class="font-bold text-sm text-primary">
                  {{ rowData.user.firstname }} {{ rowData.user.lastname }}
                </span>
                <span class="text-xs text-secondary font-mono">{{ rowData.user.email }}</span>
              </div>
            </template>

            <!-- Coluna Assunto -->
            <template #cell(subject)="{ value }">
              <span class="font-semibold text-sm">{{ formatSubject(value) }}</span>
            </template>

            <!-- Coluna Estado -->
            <template #cell(status)="{ value }">
              <VaBadge
                :text="value === 'open' ? 'Aberto' : 'Fechado'"
                :color="value === 'open' ? 'success' : 'secondary'"
              />
            </template>

            <!-- Coluna Criado em -->
            <template #cell(created_at)="{ value }">
              <span class="text-xs font-mono text-secondary">{{ formatDate(value) }}</span>
            </template>

            <!-- Coluna Ações -->
            <template #cell(actions)="{ rowData }">
              <div class="flex items-center gap-2">
                <VaButton
                  preset="plain"
                  icon="visibility"
                  size="small"
                  color="primary"
                  title="Ver mensagem"
                  @click="openDetailsModal(rowData)"
                />
                <VaButton
                  preset="plain"
                  :icon="rowData.status === 'open' ? 'lock' : 'lock_open'"
                  size="small"
                  :color="rowData.status === 'open' ? 'warning' : 'success'"
                  :title="rowData.status === 'open' ? 'Fechar ticket' : 'Reabrir ticket'"
                  @click="toggleStatus(rowData)"
                />
                <VaButton
                  preset="plain"
                  icon="delete"
                  size="small"
                  color="danger"
                  title="Eliminar ticket"
                  @click="confirmDelete(rowData)"
                />
              </div>
            </template>
          </VaDataTable>

          <!-- Sem resultados -->
          <div v-if="store.tickets.length === 0" class="text-center py-8 text-[var(--va-secondary)]">
            <VaIcon name="forum" size="48px" class="mb-2" />
            <p>Nenhum ticket de suporte encontrado.</p>
          </div>

          <!-- Paginação -->
          <div v-if="store.pagination.last_page > 1" class="flex justify-between items-center mt-4">
            <span class="text-sm text-[var(--va-secondary)]">
              {{ store.pagination.total }} ticket(s) encontrado(s)
            </span>
            <VaPagination
              v-model="store.pagination.current_page"
              :pages="store.pagination.last_page"
              :visible-pages="5"
              buttons-preset="secondary"
              active-page-color="primary"
              @update:modelValue="changePage"
            />
          </div>
        </template>
      </VaCardContent>
    </VaCard>

    <!-- Modal Detalhe Mensagem -->
    <VaModal
      v-model="detailsModal.show"
      title="Detalhes do Ticket de Suporte"
      ok-text="Fechar"
      :cancel-text="detailsModal.ticket?.status === 'open' ? 'Marcar como Resolvido' : 'Reabrir Ticket'"
      show-nested-overlay
      size="large"
      @cancel="toggleStatusInModal"
    >
      <div v-if="detailsModal.ticket" class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-4xl min-h-[400px]">
        <!-- Coluna da Esquerda: Dados do Ticket e Mensagem -->
        <div class="space-y-4">
          <div class="border-b border-[var(--va-background-element)] pb-3">
            <h3 class="text-base font-bold text-primary mb-2">Informação do Ticket</h3>
            <div class="grid grid-cols-2 gap-2 text-sm">
              <div>
                <span class="text-secondary text-xs block uppercase">Ticket ID</span>
                <span class="font-mono font-bold">#{{ detailsModal.ticket.id }}</span>
              </div>
              <div>
                <span class="text-secondary text-xs block uppercase">Data de Envio</span>
                <span>{{ formatDate(detailsModal.ticket.created_at) }}</span>
              </div>
              <div class="mt-2">
                <span class="text-secondary text-xs block uppercase">Assunto</span>
                <span class="font-bold text-primary">{{ formatSubject(detailsModal.ticket.subject) }}</span>
              </div>
              <div class="mt-2">
                <span class="text-secondary text-xs block uppercase">Estado</span>
                <VaBadge
                  :text="detailsModal.ticket.status === 'open' ? 'Aberto' : 'Resolvido/Fechado'"
                  :color="detailsModal.ticket.status === 'open' ? 'success' : 'secondary'"
                  class="mt-1"
                />
              </div>
            </div>
          </div>

          <div>
            <span class="text-secondary text-xs block uppercase mb-1 font-bold">Mensagem do Cliente</span>
            <div
              class="p-3 bg-[var(--va-background-element)] rounded whitespace-pre-wrap text-sm leading-relaxed text-primary font-sans max-h-80 overflow-y-auto border border-[var(--va-background-border)]"
            >
              {{ detailsModal.ticket.message }}
            </div>
          </div>
        </div>

        <!-- Coluna da Direita: Dados do Utilizador & Histórico -->
        <div class="space-y-4 md:border-l md:border-[var(--va-background-element)] md:pl-6">
          <!-- Contacto e Cliente -->
          <div class="border-b border-[var(--va-background-element)] pb-3">
            <h3 class="text-base font-bold text-primary mb-2">Ficha do Cliente</h3>
            <div class="space-y-2 text-sm">
              <div class="flex justify-between">
                <span class="text-secondary text-xs">NOME:</span>
                <span class="font-semibold text-primary"
                  >{{ detailsModal.ticket.user.firstname }} {{ detailsModal.ticket.user.lastname }}</span
                >
              </div>
              <div class="flex justify-between">
                <span class="text-secondary text-xs">EMAIL:</span>
                <span class="font-mono text-xs text-primary">{{ detailsModal.ticket.user.email }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-secondary text-xs">TELEFONE:</span>
                <span class="text-primary">{{ detailsModal.ticket.user.phone || 'Não registado' }}</span>
              </div>
            </div>
          </div>

          <!-- Moradas -->
          <div class="border-b border-[var(--va-background-element)] pb-3">
            <span class="text-secondary text-xs block uppercase font-bold mb-1">Moradas Registadas</span>
            <div
              v-if="!detailsModal.ticket.user.addresses || detailsModal.ticket.user.addresses.length === 0"
              class="text-xs text-secondary italic"
            >
              Nenhuma morada associada.
            </div>
            <div v-else class="space-y-1.5 max-h-24 overflow-y-auto">
              <div
                v-for="addr in detailsModal.ticket.user.addresses"
                :key="addr.id"
                class="text-xs p-1.5 bg-[var(--va-background-element)] rounded border border-[var(--va-background-border)]"
              >
                <div class="flex justify-between items-center">
                  <span>{{ addr.street }}, {{ addr.city }}</span>
                  <VaBadge v-if="addr.is_default" text="Padrão" color="info" size="small" />
                </div>
                <div class="text-[10px] text-secondary font-mono">{{ addr.postal_code }} - {{ addr.country }}</div>
              </div>
            </div>
          </div>

          <!-- Histórico de Encomendas -->
          <div>
            <span class="text-secondary text-xs block uppercase font-bold mb-1">Histórico de Encomendas</span>
            <div
              v-if="!detailsModal.ticket.user.orders || detailsModal.ticket.user.orders.length === 0"
              class="text-xs text-secondary italic"
            >
              Nenhuma encomenda efetuada por este cliente.
            </div>
            <div v-else class="space-y-1.5 max-h-36 overflow-y-auto">
              <div
                v-for="ord in detailsModal.ticket.user.orders"
                :key="ord.id"
                class="text-xs p-2 bg-[var(--va-background-element)] rounded border border-[var(--va-background-border)] flex justify-between items-center"
              >
                <div class="flex flex-col">
                  <span class="font-bold text-primary font-mono">{{ ord.order_number }}</span>
                  <span class="text-[9px] text-secondary font-mono">{{ formatDate(ord.created_at) }}</span>
                </div>
                <div class="flex flex-col items-end gap-1">
                  <span class="font-bold text-primary">€{{ parseFloat(ord.total).toFixed(2) }}</span>
                  <div class="flex gap-1">
                    <VaBadge :text="formatOrderStatus(ord.status)" color="primary" size="small" />
                    <VaBadge
                      :text="ord.payment_status === 'paid' ? 'Pago' : 'Pendente'"
                      :color="ord.payment_status === 'paid' ? 'success' : 'warning'"
                      size="small"
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </VaModal>

    <!-- Modal Eliminar -->
    <VaModal
      v-model="deleteModal.show"
      title="Eliminar Ticket de Suporte"
      ok-text="Eliminar"
      cancel-text="Cancelar"
      @ok="executeDelete"
    >
      <p>
        Tens a certeza que queres eliminar o ticket
        <strong>#{{ deleteModal.ticketId }}</strong
        >?
      </p>
      <p class="text-sm text-[var(--va-secondary)] mt-2">
        Esta ação é irreversível e o ticket será removido permanentemente da base de dados.
      </p>
    </VaModal>
  </div>
</template>

<script lang="ts" setup>
import { reactive, onMounted, onBeforeUnmount } from 'vue'
import { useToast } from 'vuestic-ui'
import { useTicketsStore, type Ticket } from '../../stores/tickets-store'

const store = useTicketsStore()
const { init: toast } = useToast()

const columns = [
  { key: 'id', label: 'ID', width: '70px', sortable: true },
  { key: 'user', label: 'Utilizador', sortable: false },
  { key: 'subject', label: 'Assunto do Contacto', sortable: true },
  { key: 'status', label: 'Estado', width: '100px', sortable: true },
  { key: 'created_at', label: 'Criado em', width: '140px', sortable: true },
  { key: 'actions', label: '', width: '120px', sortable: false },
]

const statusFilterOptions = [
  { value: 'open', text: 'Abertos' },
  { value: 'closed', text: 'Fechados' },
]

const subjectFilterOptions = [
  { value: 'devolucao', text: 'Devolução de Artigo' },
  { value: 'reembolso', text: 'Reembolso em Atraso' },
  { value: 'garantia', text: 'Acionar Garantia' },
  { value: 'duvida', text: 'Dúvida sobre Produto' },
]

const filters = reactive({
  search: '',
  status: null as string | null,
  subject: null as string | null,
})

let debounceTimer: ReturnType<typeof setTimeout>
function debouncedFetch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => applyFilters(), 400)
}

function applyFilters() {
  store.setPage(1)
  const params: Record<string, unknown> = {}
  if (filters.search) params.search = filters.search
  if (filters.status) params.status = filters.status
  if (filters.subject) params.subject = filters.subject
  store.fetchTickets(params)
}

function changePage(page: number) {
  store.setPage(page)
  applyFilters()
}

// Helpers formatadores
function formatDate(iso: string): string {
  return new Date(iso).toLocaleDateString('pt-PT', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

function formatSubject(subject: string): string {
  if (!subject) return 'Sem assunto'
  const mapping: Record<string, string> = {
    devolucao: 'Devolução de Artigo',
    reembolso: 'Reembolso em Atraso',
    garantia: 'Acionar Garantia',
    duvida: 'Dúvida sobre Produto',
  }
  return mapping[subject.toLowerCase()] || subject
}

function formatOrderStatus(status: string): string {
  const mapping: Record<string, string> = {
    pending: 'Pendente',
    processing: 'Processamento',
    shipped: 'Enviado',
    delivered: 'Entregue',
    cancelled: 'Cancelado',
    refunded: 'Reembolsado',
  }
  return mapping[status.toLowerCase()] || status
}

// Modais
const detailsModal = reactive({
  show: false,
  ticket: null as Ticket | null,
})

const deleteModal = reactive({
  show: false,
  ticketId: 0,
})

function openDetailsModal(ticket: Ticket) {
  detailsModal.ticket = ticket
  detailsModal.show = true
}

async function toggleStatus(ticket: Ticket) {
  const nextStatus = ticket.status === 'open' ? 'closed' : 'open'
  const success = await store.updateTicketStatus(ticket.id, nextStatus)
  if (success) {
    toast({
      message: nextStatus === 'open' ? 'Ticket reaberto.' : 'Ticket fechado/resolvido.',
      color: 'success',
    })
  } else {
    toast({ message: store.error || 'Erro ao atualizar estado.', color: 'danger' })
  }
}

async function toggleStatusInModal() {
  if (detailsModal.ticket) {
    const ticket = detailsModal.ticket
    const nextStatus = ticket.status === 'open' ? 'closed' : 'open'
    const success = await store.updateTicketStatus(ticket.id, nextStatus)
    if (success) {
      // Atualizar o estado local do modal
      ticket.status = nextStatus
      toast({
        message: nextStatus === 'open' ? 'Ticket reaberto.' : 'Ticket fechado/resolvido.',
        color: 'success',
      })
    } else {
      toast({ message: store.error || 'Erro ao atualizar estado.', color: 'danger' })
    }
  }
}

function confirmDelete(ticket: Ticket) {
  deleteModal.ticketId = ticket.id
  deleteModal.show = true
}

async function executeDelete() {
  const success = await store.deleteTicket(deleteModal.ticketId)
  if (success) {
    toast({ message: 'Ticket de suporte eliminado.', color: 'success' })
    deleteModal.show = false
  } else {
    toast({ message: store.error || 'Erro ao eliminar ticket.', color: 'danger' })
  }
}

let pollingInterval: ReturnType<typeof setInterval>

onMounted(() => {
  store.fetchTickets()
  // Recarregar os tickets automaticamente a cada 20 segundos
  pollingInterval = setInterval(() => {
    applyFilters()
  }, 20000)
})

onBeforeUnmount(() => {
  clearTimeout(debounceTimer)
  if (pollingInterval) {
    clearInterval(pollingInterval)
  }
})
</script>

<style lang="scss" scoped>
.tickets-page {
  padding: 0.5rem;
}
</style>
