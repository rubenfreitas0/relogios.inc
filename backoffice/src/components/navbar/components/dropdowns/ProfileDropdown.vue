<template>
  <div class="profile-dropdown-wrapper">
    <VaDropdown v-model="isShown" :offset="[9, 0]" class="profile-dropdown" stick-to-edges>
      <template #anchor>
        <VaButton preset="secondary" color="textPrimary">
          <span class="profile-dropdown__anchor">
            <span class="profile-dropdown__name">
              {{ authStore.fullName }}
            </span>
            <span class="profile-dropdown__avatar">
              {{ authStore.initials || 'A' }}
            </span>
          </span>
        </VaButton>
      </template>
      <VaDropdownContent class="profile-dropdown__content">
        <!-- Profile Info Header -->
        <div class="profile-dropdown__header">
          <div class="profile-dropdown__header-avatar">
            {{ authStore.initials || 'A' }}
          </div>
          <div class="profile-dropdown__header-info">
            <div class="profile-dropdown__header-name">
              {{ authStore.fullName }}
            </div>
            <div class="profile-dropdown__header-email">
              {{ authStore.user?.email }}
            </div>
          </div>
          <span class="profile-dropdown__badge">Administrador</span>
        </div>

        <!-- Navigation Links -->
        <nav class="profile-dropdown__nav">
          <div class="profile-dropdown__nav-label">Conta</div>
          <a
            v-for="item in menuItems"
            :key="item.name"
            class="profile-dropdown__nav-item"
            @click="handleItemClick(item)"
          >
            <VaIcon :name="item.icon" size="18px" class="profile-dropdown__nav-icon" />
            <span>{{ item.label }}</span>
          </a>
        </nav>

        <!-- Logout -->
        <div class="profile-dropdown__footer">
          <a class="profile-dropdown__nav-item profile-dropdown__nav-item--danger" @click="handleLogout">
            <VaIcon name="mso-logout" size="18px" class="profile-dropdown__nav-icon" />
            <span>Terminar sessão</span>
          </a>
        </div>
      </VaDropdownContent>
    </VaDropdown>
  </div>
</template>

<script lang="ts" setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../../../stores/auth-store'

const router = useRouter()
const authStore = useAuthStore()

const isShown = ref(false)

const menuItems = [
  { name: 'profile', label: 'Perfil', icon: 'mso-account_circle', to: 'preferences' },
  { name: 'settings', label: 'Definições', icon: 'mso-settings', to: 'settings' },
]

const handleItemClick = (item: { to?: string }) => {
  if (item.to) {
    router.push({ name: item.to })
  }
  isShown.value = false
}

const handleLogout = async () => {
  await authStore.logout()
  router.push({ name: 'login' })
}
</script>

<style lang="scss" scoped>
.profile-dropdown {
  cursor: pointer;

  &__anchor {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  &__name {
    font-size: 13px;
    font-weight: 600;
    letter-spacing: 0.02em;
    color: var(--va-text-primary);

    @media screen and (max-width: 768px) {
      display: none;
    }
  }

  &__avatar {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    background: #111;
    color: var(--va-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.05em;
    flex-shrink: 0;
  }

  &__content {
    width: 280px;
    padding: 0 !important;
    border-radius: 14px !important;
    overflow: hidden;
    box-shadow:
      0 10px 40px rgba(0, 0, 0, 0.12),
      0 2px 10px rgba(0, 0, 0, 0.06) !important;
    border: 1px solid rgba(0, 0, 0, 0.06) !important;
  }

  &__header {
    padding: 20px 18px 16px;
    background: linear-gradient(135deg, #0f0f11 0%, #1a1a2e 100%);
    color: #fff;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
  }

  &__header-avatar {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    background: rgba(255, 255, 255, 0.1);
    border: 2px solid var(--va-primary);
    color: var(--va-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    font-weight: 700;
    letter-spacing: 0.05em;
    margin-bottom: 6px;
  }

  &__header-info {
    text-align: center;
    line-height: 1.4;
  }

  &__header-name {
    font-size: 14px;
    font-weight: 700;
    color: #fff;
    letter-spacing: 0.01em;
  }

  &__header-email {
    font-size: 11px;
    font-weight: 500;
    color: rgba(255, 255, 255, 0.55);
    margin-top: 2px;
  }

  &__badge {
    display: inline-block;
    margin-top: 8px;
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--va-primary);
    background: rgba(255, 199, 0, 0.12);
    border: 1px solid rgba(255, 199, 0, 0.25);
    padding: 3px 10px;
    border-radius: 6px;
  }

  &__nav {
    padding: 10px 8px 4px;
  }

  &__nav-label {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: #999;
    padding: 4px 12px 8px;
  }

  &__nav-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 12px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    color: #333;
    cursor: pointer;
    transition: all 0.15s ease;
    text-decoration: none;

    &:hover {
      background: rgba(0, 0, 0, 0.04);
      color: #111;
    }

    &--danger {
      color: #c53030;

      &:hover {
        background: rgba(197, 48, 48, 0.06);
        color: #9b2c2c;
      }

      .profile-dropdown__nav-icon {
        color: #c53030;
      }
    }
  }

  &__nav-icon {
    color: #888;
    flex-shrink: 0;
  }

  &__footer {
    padding: 4px 8px 10px;
    border-top: 1px solid rgba(0, 0, 0, 0.06);
  }
}
</style>
