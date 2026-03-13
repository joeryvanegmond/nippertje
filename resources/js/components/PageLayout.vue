<script setup>
import { ref } from 'vue'

const menuVisible = ref(false)
let hideTimeout = null

function showMenu() {
  menuVisible.value = true
  clearTimeout(hideTimeout)
  hideTimeout = setTimeout(() => { menuVisible.value = false }, 3000)
}

function hideMenu() {
  clearTimeout(hideTimeout)
  menuVisible.value = false
}
</script>

<template>
  <div class="position-relative overflow-hidden" style="height: 100vh" @mouseenter="showMenu" @mousemove="showMenu" @mouseleave="hideMenu">
    <!-- Achtergrond -->
    <div class="w-full" style="height: 50%; background-color:#E5E5E5;"></div>
    <div class="w-full bg-primary" style="height: 50%"></div>

    <!-- Vervoersmiddel (veerpont, bus, metro, trein...) -->
    <slot />

    <!-- Overlay slot (bijv. LoginOverlay) -->
    <Transition name="fade">
      <slot v-if="true" name="overlay" />
    </Transition>
  </div>
</template>

<style>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>