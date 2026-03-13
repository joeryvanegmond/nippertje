<template>
    <canvas ref="rainCanvas" class="absolute inset-0 w-full h-full pointer-events-none"></canvas>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const rainCanvas = ref(null)
let animFrame = null

onMounted(() => {
  const canvas = rainCanvas.value
  const ctx = canvas.getContext('2d')
  let drops = []

  function resize() {
    canvas.width = canvas.offsetWidth
    canvas.height = canvas.offsetHeight
    drops = Array.from({ length: 120 }, () => ({
      x: Math.random() * canvas.width,
      y: Math.random() * canvas.height,
      len: 10 + Math.random() * 20,
      speed: 6 + Math.random() * 8,
      opacity: 0.2 + Math.random() * 0.4,
    }))
  }

  function draw() {
    ctx.clearRect(0, 0, canvas.width, canvas.height)
    drops.forEach(d => {
      ctx.beginPath()
      ctx.moveTo(d.x, d.y)
      ctx.lineTo(d.x - 1, d.y + d.len)
      ctx.strokeStyle = `rgba(180, 210, 255, ${d.opacity})`
      ctx.lineWidth = 1
      ctx.stroke()
      d.y += d.speed
      d.x -= 0.5
      if (d.y > canvas.height) {
        d.y = -d.len
        d.x = Math.random() * canvas.width
      }
    })
    animFrame = requestAnimationFrame(draw)
  }

  resize()
  window.addEventListener('resize', resize)
  draw()
})

onUnmounted(() => {
  cancelAnimationFrame(animFrame)
})
</script>