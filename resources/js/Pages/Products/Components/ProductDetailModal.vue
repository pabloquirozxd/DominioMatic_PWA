<!-- resources/js/Pages/Products/Components/ProductDetailModal.vue -->
<script setup>
import Modal from '@/Components/UI/Modal/Modal.vue'
import SecondaryButton from '@/Components/UI/Buttons/SecondaryButton.vue'
import { SparklesIcon, CubeIcon } from '@heroicons/vue/24/outline'

defineProps({
  show: Boolean,
  product: Object,
})

defineEmits(['close'])

function formatMoney(value, currency) {
  const number = Number(value || 0)
  const rawCurrency = String(currency || '').trim().toUpperCase()

  if (rawCurrency === 'BOB' || rawCurrency === 'BS' || rawCurrency === 'BOLIVIANOS') {
    return `Bs. ${number.toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
  }

  return `$${number.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
}
</script>

<template>
  <Modal :show="show" max-width="2xl" @close="$emit('close')">
    <template #header>
      <div v-if="product" class="flex items-center gap-4 py-1">
        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#007AFF]/10 dark:bg-[#007AFF]/20">
          <component
            :is="product.type === 'service' ? SparklesIcon : CubeIcon"
            class="h-7 w-7 text-[#007AFF]"
          />
        </div>
        <div>
          <h2 class="text-2xl font-extrabold tracking-tight text-gray-900 dark:text-white">
            {{ product.name }}
          </h2>
        </div>
      </div>
    </template>

    <div v-if="product" class="space-y-6 py-2">
      <!-- Tarjeta Resumen Tipo / Precio -->
      <div class="grid grid-cols-2 gap-4 rounded-3xl border border-black/5 bg-black/[0.02] p-5 dark:border-white/10 dark:bg-white/[0.02]">
        <div>
          <p class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Tipo de Ítem</p>
          <p class="mt-1 text-base font-extrabold capitalize text-gray-900 dark:text-white">
            {{ product.type === 'service' ? 'Servicio Digital' : 'Producto Físico' }}
          </p>
        </div>

        <div>
          <p class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Precio Lista</p>
          <p class="mt-1 text-base font-extrabold text-gray-900 dark:text-white">
            {{ formatMoney(product.price_list || product.price, product.currency) }}
          </p>
        </div>
      </div>

      <!-- Descripción Detallada -->
      <div>
        <p class="mb-2 text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">
          Descripción detallada
        </p>
        <div
          v-html="product.description || 'Sin descripción registrada.'"
          class="prose prose-base dark:prose-invert max-w-none rounded-3xl border border-black/5 bg-black/[0.015] p-6 text-sm text-gray-800 dark:border-white/10 dark:bg-white/[0.015] dark:text-gray-200 [&_ul]:list-disc [&_ul]:pl-5"
        ></div>
      </div>
    </div>

    <template #footer>
      <div class="flex justify-end pt-2">
        <SecondaryButton class="px-6 py-2.5 text-sm font-bold" @click="$emit('close')">
          Cerrar
        </SecondaryButton>
      </div>
    </template>
  </Modal>
</template>