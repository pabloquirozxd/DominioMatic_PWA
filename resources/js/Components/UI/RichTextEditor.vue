<!-- resources/js/Components/UI/RichTextEditor.vue -->
<script setup>
import { ref, watch, onMounted } from 'vue'

const props = defineProps({
  modelValue: { type: String, default: '' },
  placeholder: { type: String, default: 'Escriba una descripción...' },
})

const emit = defineEmits(['update:modelValue'])
const editorRef = ref(null)

const execCommand = (command, value = null) => {
  document.execCommand(command, false, value)
  emitChange()
}

const emitChange = () => {
  if (editorRef.value) {
    emit('update:modelValue', editorRef.value.innerHTML)
  }
}

onMounted(() => {
  if (editorRef.value) {
    editorRef.value.innerHTML = props.modelValue || ''
  }
})

watch(() => props.modelValue, (newVal) => {
  if (editorRef.value && editorRef.value.innerHTML !== newVal) {
    editorRef.value.innerHTML = newVal || ''
  }
})
</script>

<template>
  <div class="overflow-hidden rounded-2xl border border-black/10 bg-black/5 dark:border-white/10 dark:bg-[#222226]/80 focus-within:border-[#007AFF] focus-within:bg-white dark:focus-within:border-[#007AFF] dark:focus-within:bg-[#28282d] transition">
    <!-- Toolbar -->
    <div class="flex items-center gap-1 border-b border-black/5 bg-black/[0.02] p-2 dark:border-white/5 dark:bg-white/[0.02]">
      <!-- Negrita -->
      <button
        type="button"
        @click="execCommand('bold')"
        class="flex h-7 w-7 items-center justify-center rounded-lg text-xs font-bold text-gray-700 transition hover:bg-black/10 dark:text-gray-300 dark:hover:bg-white/10"
        title="Negrita"
      >
        B
      </button>

      <!-- Cursiva -->
      <button
        type="button"
        @click="execCommand('italic')"
        class="flex h-7 w-7 items-center justify-center rounded-lg text-xs italic font-serif text-gray-700 transition hover:bg-black/10 dark:text-gray-300 dark:hover:bg-white/10"
        title="Cursiva"
      >
        I
      </button>

      <!-- Subrayado -->
      <button
        type="button"
        @click="execCommand('underline')"
        class="flex h-7 w-7 items-center justify-center rounded-lg text-xs underline text-gray-700 transition hover:bg-black/10 dark:text-gray-300 dark:hover:bg-white/10"
        title="Subrayado"
      >
        U
      </button>

      <div class="mx-1 h-4 w-[1px] bg-black/10 dark:bg-white/10"></div>

      <!-- Tamaño de letra -->
      <select
        @change="(e) => execCommand('fontSize', e.target.value)"
        class="h-7 rounded-lg border-none bg-transparent py-0 pl-2 pr-6 text-xs text-gray-700 outline-none focus:ring-0 dark:text-gray-300 dark:[&>option]:bg-[#28282d]"
      >
        <option value="2">Normal</option>
        <option value="1">Pequeño</option>
        <option value="3">Mediano</option>
        <option value="4">Grande</option>
      </select>
    </div>

    <!-- Área Editable -->
    <div
      ref="editorRef"
      contenteditable="true"
      @input="emitChange"
      class="min-h-[100px] max-h-[220px] overflow-y-auto p-3 text-sm text-gray-900 outline-none dark:text-white [&_p]:mb-2 [&_ul]:list-disc [&_ul]:pl-4"
      :data-placeholder="placeholder"
    ></div>
  </div>
</template>