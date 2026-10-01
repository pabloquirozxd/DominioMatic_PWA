<script setup>
import { useForm } from '@inertiajs/vue3'
import Modal from '@/Components/UI/Modal/Modal.vue'
import PrimaryButton from '@/Components/UI/Buttons/PrimaryButton.vue'
import SecondaryButton from '@/Components/UI/Buttons/SecondaryButton.vue'
import FormField from '@/Components/UI/Forms/FormField.vue'

defineProps({
  show: Boolean,
})

const emit = defineEmits(['close'])

const importForm = useForm({
  file: null,
})

function submitImport() {
  if (!importForm.file) return

  importForm.post(route('contacts.import'), {
    preserveScroll: true,
    onSuccess: () => {
      importForm.reset()
      emit('close')
    },
  })
}
</script>

<template>
  <Modal :show="show" max-width="lg" @close="emit('close')">
    <div class="p-6">
      <div>
        <h3 class="text-xl font-bold text-gray-900 dark:text-white">
          Importar contactos
        </h3>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
          Sube un archivo .xlsx, .xls o .csv para registrar contactos de forma masiva.
        </p>
      </div>

      <form class="mt-6 space-y-4" @submit.prevent="submitImport">
        <FormField label="Archivo Excel o CSV" :error="importForm.errors.file">
          <input
            type="file"
            accept=".csv, .xlsx, .xls"
            class="w-full rounded-2xl border border-gray-200/80 bg-gray-50/50 px-4 py-3 text-xs text-gray-600 file:mr-4 file:rounded-xl file:border-0 file:bg-[#0047BA] file:px-3.5 file:py-1.5 file:text-xs file:font-bold file:text-white hover:file:bg-[#003893] dark:border-white/10 dark:bg-white/5 dark:text-gray-300 dark:file:bg-[#3B82F6]"
            @change="importForm.file = $event.target.files[0]"
          />
        </FormField>

        <div class="mt-6 flex justify-end gap-3">
          <SecondaryButton type="button" @click="emit('close')">
            Cancelar
          </SecondaryButton>
          <PrimaryButton type="submit" :disabled="importForm.processing || !importForm.file">
            {{ importForm.processing ? 'Importando...' : 'Subir e importar' }}
          </PrimaryButton>
        </div>
      </form>
    </div>
  </Modal>
</template>