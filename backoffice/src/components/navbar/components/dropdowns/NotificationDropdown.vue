<template>
  <VaDropdown
    v-model="isOpen"
    :offset="[13, 0]"
    class="notification-dropdown"
    stick-to-edges
    :close-on-content-click="false"
  >
    <template #anchor>
      <VaButton preset="secondary" color="textPrimary" class="notification-dropdown__trigger">
        <span class="notification-dropdown__bell-wrapper">
          <VaIconNotification class="notification-dropdown__icon" />
          <span v-if="openTicketsCount > 0" class="notification-dropdown__count">
            {{ openTicketsCount > 9 ? '9+' : openTicketsCount }}
          </span>
        </span>
      </VaButton>
    </template>
    <VaDropdownContent class="notification-dropdown__content">
      <!-- Header -->
      <div class="notification-dropdown__header">
        <div class="notification-dropdown__header-left">
          <VaIcon name="forum" size="18px" color="#fff" />
          <span class="notification-dropdown__header-title">Tickets Abertos</span>
        </div>
        <span class="notification-dropdown__header-count">
          {{ openTicketsCount }}
        </span>
      </div>

      <!-- Resumo: só o número de tickets em aberto -->
      <div class="notification-dropdown__body">
        <div v-if="openTicketsCount > 0" class="notification-dropdown__summary">
          <span class="notification-dropdown__summary-number">{{ openTicketsCount }}</span>
          <span class="notification-dropdown__summary-label">
            {{ openTicketsCount === 1 ? 'ticket em aberto' : 'tickets em aberto' }}
          </span>
        </div>

        <div v-else class="notification-dropdown__empty">
          <VaIcon name="check_circle" size="28px" color="#a3e635" />
          <span>Sem tickets abertos</span>
        </div>
      </div>

      <!-- Footer -->
      <div class="notification-dropdown__footer">
        <a class="notification-dropdown__footer-link" @click="goToTicket">
          Ver todos os tickets
          <VaIcon name="arrow_forward" size="14px" />
        </a>
      </div>
    </VaDropdownContent>
  </VaDropdown>
</template>

<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import VaIconNotification from '../../../icons/VaIconNotification.vue'
import { ticketsApi } from '../../../../services/api'

const router = useRouter()

const openTicketsCount = ref(0)
const isOpen = ref(false)

onMounted(async () => {
  await fetchOpenTickets()
})

// Atualiza a contagem sempre que o dropdown é aberto
watch(isOpen, (open) => {
  if (open) fetchOpenTickets()
})

const fetchOpenTickets = async () => {
  try {
    const res = await ticketsApi.list({ status: 'open', per_page: 1 })
    openTicketsCount.value = res.data.meta.total
  } catch (e) {
    console.error('Erro ao buscar tickets abertos:', e)
  }
}

const goToTicket = () => {
  router.push({ name: 'tickets' })
}
</script>

<style lang="scss" scoped>
.notification-dropdown {
  cursor: pointer;

  &__trigger {
    position: relative;
  }

  &__bell-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  &__icon {
    display: flex;
    align-items: center;
  }

  &__count {
    position: absolute;
    top: -6px;
    right: -8px;
    min-width: 18px;
    height: 18px;
    background: #e53e3e;
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 4px;
    line-height: 1;
    box-shadow: 0 2px 6px rgba(229, 62, 62, 0.4);
  }

  &__content {
    width: 340px;
    padding: 0 !important;
    border-radius: 14px !important;
    overflow: hidden;
    box-shadow:
      0 10px 40px rgba(0, 0, 0, 0.12),
      0 2px 10px rgba(0, 0, 0, 0.06) !important;
    border: 1px solid rgba(0, 0, 0, 0.06) !important;
  }

  &__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 18px;
    background: linear-gradient(135deg, #0f0f11 0%, #1a1a2e 100%);
    color: #fff;
  }

  &__header-left {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  &__header-title {
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0.02em;
  }

  &__header-count {
    min-width: 22px;
    height: 22px;
    background: #e53e3e;
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 6px;
  }

  &__body {
    max-height: 280px;
    overflow-y: auto;
  }

  &__list {
    padding: 6px;
  }

  &__ticket {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 10px 12px;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.15s ease;
    text-decoration: none;

    &:hover {
      background: rgba(0, 0, 0, 0.04);
    }
  }

  &__ticket-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--va-primary);
    flex-shrink: 0;
    margin-top: 5px;
  }

  &__ticket-info {
    flex: 1;
    min-width: 0;
  }

  &__ticket-subject {
    font-size: 13px;
    font-weight: 600;
    color: #1a1a1a;
    line-height: 1.3;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  &__ticket-meta {
    font-size: 11px;
    font-weight: 500;
    color: #888;
    margin-top: 2px;
  }

  &__ticket-time {
    color: #aaa;
  }

  &__empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 32px 18px;
    color: #999;
    font-size: 13px;
    font-weight: 500;
  }

  &__summary {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
    padding: 28px 18px;
  }

  &__summary-number {
    font-size: 34px;
    font-weight: 800;
    line-height: 1;
    color: #e53e3e;
  }

  &__summary-label {
    font-size: 13px;
    font-weight: 600;
    color: #666;
  }

  &__footer {
    padding: 10px 18px;
    border-top: 1px solid rgba(0, 0, 0, 0.06);
    display: flex;
    justify-content: center;
  }

  &__footer-link {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 600;
    color: var(--va-primary);
    cursor: pointer;
    transition: opacity 0.15s ease;
    text-decoration: none;
    letter-spacing: 0.02em;

    &:hover {
      opacity: 0.75;
    }
  }
}
</style>
