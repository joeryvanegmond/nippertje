<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue'
import { router } from '@inertiajs/vue3'
const props = defineProps({
    line: String,
    destination: String,
    timer: String,
})

const svgContainer = ref(null);
const timerDisplay = ref('');
let interval = null;
let refreshInterval = null;

function updateSvg() {
    // Elementen op ID aanpassen
    const lineEl = svgContainer.value.querySelector('#line-text tspan tspan')
    const destinationEl = svgContainer.value.querySelector('#destination-text tspan')
    const timerEl = svgContainer.value.querySelector('#timer-text tspan')
    if (lineEl) lineEl.textContent = props.line
    if (destinationEl) destinationEl.textContent = props.destination
    if (timerEl) timerEl.textContent = timerDisplay.value
}

function calculateTimer() {
    const now = new Date()
    const today = now.toISOString().split('T')[0]
    const departure = new Date(`${today}T${props.timer}:00`)
    // Als tijd al voorbij is, morgen gebruiken
    if (departure < now) {
        departure.setDate(departure.getDate() + 1)
    }
    const diffMs = departure - now

    if (diffMs <= 0) {
        timerDisplay.value = '00:00'
        router.reload({ only: ['line', 'destination', 'timer'] })
        clearInterval(interval)
        return
    }

    const minutes = Math.floor(diffMs / 1000 / 60)
    const seconds = Math.floor((diffMs / 1000) % 60)

    timerDisplay.value = `${minutes}:${String(seconds).padStart(2, '0')}`
}

onMounted(async () => {
    const response = await fetch('/images/veerpont.svg')
    const svgText = await response.text()
    svgContainer.value.innerHTML = svgText

    updateSvg()
    calculateTimer();
    interval = setInterval(calculateTimer, 1000);

    refreshInterval = setInterval(() => {
        router.reload({ only: ['line', 'destination', 'timer'] })
    }, 30000)
});

onUnmounted(() => {
    clearInterval(interval)
});

watch([props, timerDisplay], updateSvg)
</script>

<template>
    <div ref="svgContainer" style="height: 600px;"></div>
</template>