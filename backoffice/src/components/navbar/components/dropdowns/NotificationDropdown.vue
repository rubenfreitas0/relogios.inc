<template>
  <VaDropdown :offset="[13, 0]" class="notification-dropdown" stick-to-edges :close-on-content-click="false">
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
          <span class="notification-dropdown__header-title">Tickets Pendentes</span>
        </div>
        <span v-if="openTicketsCount > 0" class="notification-dropdown__header-count">
          {{ openTicketsCount }}
        </span>
      </div>

      <!-- Tickets List -->
      <div class="notification-dropdown__body">
        <div v-if="openTickets.length > 0" class="notification-dropdown__list">
          <a v-for="item in openTickets" :key="item.id" class="notification-dropdown__ticket" @click="goToTicket">
            <div class="notification-dropdown__ticket-dot"></div>
            <div class="notification-dropdown__ticket-info">
              <div class="notification-dropdown__ticket-subject">{{ item.subject }}</div>
              <div class="notification-dropdown__ticket-meta">
                {{ item.user ? item.user.firstname + ' ' + item.user.lastname : 'Cliente' }}
                <span class="notification-dropdown__ticket-time">· {{ formatRelativeTime(item.created_at) }}</span>
              </div>
            </div>
          </a>
        </div>

        <div v-else class="notification-dropdown__empty">
          <VaIcon name="check_circle" size="28px" color="#a3e635" />
          <span>Sem tickets pendentes</span>
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
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'
import VaIconNotification from '../../../icons/VaIconNotification.vue'
import { ticketsApi } from '../../../../services/api'

const { locale } = useI18n()
const router = useRouter()

const rtf = new Intl.RelativeTimeFormat(locale.value, { style: 'short' })

const openTicketsCount = ref(0)
const openTickets = ref<any[]>([])

onMounted(async () => {
  await fetchOpenTickets()
})

const fetchOpenTickets = async () => {
  try {
    const res = await ticketsApi.list({ status: 'open', per_page: 5 })
    openTicketsCount.value = res.data.meta.total
    openTickets.value = res.data.data
  } catch (e) {
    console.error('Erro ao buscar tickets pendentes:', e)
  }
}

const goToTicket = () => {
  router.push({ name: 'tickets' })
}

const TIME_NAMES = {
  second: 1000,
  minute: 1000 * 60,
  hour: 1000 * 60 * 60,
  day: 1000 * 60 * 60 * 24,
  week: 1000 * 60 * 60 * 24 * 7,
  month: 1000 * 60 * 60 * 24 * 30,
  year: 1000 * 60 * 60 * 24 * 365,
}

const getTimeName = (differenceTime: number) => {
  return Object.keys(TIME_NAMES).reduce(
    (acc, key) => (TIME_NAMES[key as keyof typeof TIME_NAMES] < differenceTime ? key : acc),
    'month',
  ) as keyof typeof TIME_NAMES
}

const formatRelativeTime = (dateString: string) => {
  const date = new Date(dateString)
  const timeDifference = Math.round(new Date().getTime() - date.getTime())
  const timeName = getTimeName(timeDifference)
  const value = Math.round(timeDifference / TIME_NAMES[timeName])
  return rtf.format(-1 * (value || 1), timeName)
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
