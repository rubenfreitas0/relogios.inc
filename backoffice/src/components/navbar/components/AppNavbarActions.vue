<template>
  <div class="app-navbar-actions">
    <NotificationDropdown class="app-navbar-actions__item" />
    <div class="app-navbar-actions__divider"></div>
    <ProfileDropdown class="app-navbar-actions__item app-navbar-actions__item--profile" />
  </div>
</template>

<script lang="ts" setup>
import ProfileDropdown from './dropdowns/ProfileDropdown.vue'
import NotificationDropdown from './dropdowns/NotificationDropdown.vue'

defineProps({
  isMobile: { type: Boolean, default: false },
})
</script>

<style lang="scss">
.app-navbar-actions {
  display: flex;
  align-items: center;
  gap: 4px;
  margin-right: 8px;

  .va-dropdown__anchor {
    color: var(--va-text-primary);
    fill: var(--va-text-primary);
  }

  &__divider {
    width: 1px;
    height: 20px;
    background: rgba(0, 0, 0, 0.08);
    margin: 0 4px;
  }

  &__item {
    padding: 0;

    svg {
      height: 20px;
    }

    &--profile {
      display: flex;
      justify-content: center;
    }

    .va-dropdown-content {
      background-color: var(--va-white);
    }
  }

  .fa-github {
    color: var(--va-on-background-primary);
  }
}
</style>
