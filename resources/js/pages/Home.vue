<script setup>
import Veerpont from '@/components/Veerpont.vue';
import { ref } from 'vue'

defineProps({
  line: String,
  destination: String,
  timer: String,
})

const menuVisible = ref(false);
let hideTimeout = null
function showMenu() {
  menuVisible.value = true

  // Reset de timer bij elke beweging
  clearTimeout(hideTimeout)
  hideTimeout = setTimeout(() => {
    menuVisible.value = false
  }, 3000)
}

function hideMenu() {
  clearTimeout(hideTimeout)
  menuVisible.value = false
}
</script>

<template>
  <div style="height: 100vh" @mouseenter="showMenu" @mousemove="showMenu" @mouseleave="hideMenu">
    <!-- Bovenste helft wit -->
    <div class="w-100 bg-white" style="height: 50%"></div>
    <!-- Onderste helft blauw -->
    <div class="w-100 bg-primary" style="height: 50%"></div>

    <!-- Veerpont gecentreerd over beide divs heen -->
    <Veerpont class="main-svg" :line="line" :destination="destination" :timer="timer"/>

    <Transition name="fade">
      <nav v-if="menuVisible" class="position-fixed top-0 end-0 p-4">
        <ul class="list-unstyled bg-dark bg-opacity-75 rounded p-3">
          <li><a href="#" class="text-white text-decoration-none">Instellingen</a></li>
          <li><a href="#" class="text-white text-decoration-none">Beheer</a></li>
        </ul>
      </nav>
    </Transition>
  </div>
</template>

<style>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>