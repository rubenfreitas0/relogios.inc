<template>
  <div class="profile-dropdown-wrapper">
    <VaDropdown v-model="isShown" :offset="[9, 0]" class="profile-dropdown" stick-to-edges>
      <template #anchor>
        <VaButton preset="secondary" color="textPrimary">
          <span class="profile-dropdown__anchor min-w-max flex items-center gap-2">
            <VaAvatar :size="32" color="primary">
              {{ authStore.initials }}
            </VaAvatar>
            <span v-if="!isMobile" class="text-sm font-semibold">{{ authStore.fullName }}</span>
          </span>
        </VaButton>
      </template>
      <VaDropdownContent
        class="profile-dropdown__content md:w-60 px-0 py-4 w-full"
        :style="{ '--hover-color': hoverColor }"
      >
        <!-- Info do utilizador -->
        <div class="px-4 pb-3 border-b border-[var(--va-background-border)]">
          <p class="font-semibold text-sm">{{ authStore.fullName }}</p>
          <p class="text-xs text-[var(--va-secondary)]">{{ authStore.user?.email }}</p>
        </div>

        <!-- Logout -->
        <VaList>
          <VaListItem class="menu-item px-4 text-base cursor-pointer h-10 mt-2" @click="handleLogout">
            <VaIcon name="mso-logout" class="pr-2" color="danger" />
            <span class="text-[var(--va-danger)]">Terminar sessão</span>
          </VaListItem>
        </VaList>
      </VaDropdownContent>
    </VaDropdown>
  </div>
</template>

<script lang="ts" setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useColors } from 'vuestic-ui'
import { useAuthStore } from '../../../../stores/auth-store'

defineProps({
  isMobile: { type: Boolean, default: false },
})

const { colors, setHSLAColor } = useColors()
const hoverColor = computed(() => setHSLAColor(colors.focus, { a: 0.1 }))

const authStore = useAuthStore()
const router = useRouter()
const isShown = ref(false)

const handleLogout = async () => {
  isShown.value = false
  await authStore.logout()
  router.push({ name: 'login' })
}
</script>

<style lang="scss">
.profile-dropdown {
  cursor: pointer;

  &__content {
    .menu-item:hover {
      background: var(--hover-color);
    }
  }

  &__anchor {
    display: inline-flex;
  }
}
</style>
