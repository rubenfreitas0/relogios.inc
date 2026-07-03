<template>
  <div class="profile-header">
    <div class="profile-header__avatar">
      {{ authStore.initials || 'A' }}
    </div>
    <div class="profile-header__info">
      <h2 class="profile-header__name">{{ authStore.fullName }}</h2>
      <p class="profile-header__email">{{ authStore.user?.email }}</p>
      <div class="profile-header__meta">
        <span class="profile-header__badge">Administrador</span>
        <span v-if="memberSince" class="profile-header__since">Membro desde {{ memberSince }}</span>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { computed } from 'vue'
import { useAuthStore } from '../../../stores/auth-store'

const authStore = useAuthStore()

const memberSince = computed(() => {
  if (!authStore.user?.email_verified_at) return ''
  return new Date(authStore.user.email_verified_at).toLocaleDateString('pt-PT', {
    year: 'numeric',
    month: 'long',
  })
})
</script>

<style lang="scss" scoped>
.profile-header {
  display: flex;
  align-items: center;
  gap: 20px;

  &__avatar {
    width: 64px;
    height: 64px;
    border-radius: 16px;
    background: #111;
    color: var(--va-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    font-weight: 700;
    letter-spacing: 0.05em;
    flex-shrink: 0;
    border: 2px solid rgba(255, 199, 0, 0.25);
  }

  &__info {
    display: flex;
    flex-direction: column;
    gap: 2px;
  }

  &__name {
    font-size: 24px;
    font-weight: 700;
    line-height: 1.2;
    color: var(--va-text-primary);
  }

  &__email {
    font-size: 13px;
    color: #888;
    font-weight: 500;
  }

  &__meta {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-top: 4px;
  }

  &__badge {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--va-primary);
    background: rgba(255, 199, 0, 0.1);
    border: 1px solid rgba(255, 199, 0, 0.2);
    padding: 2px 8px;
    border-radius: 6px;
  }

  &__since {
    font-size: 12px;
    color: #aaa;
    font-weight: 500;
  }
}
</style>
