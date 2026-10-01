<!-- resources/js/Pages/Contacts/Partials/ContactFormModal.vue -->
<script setup>
import { computed, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'

import Modal from '@/Components/UI/Modal/Modal.vue'
import PrimaryButton from '@/Components/UI/Buttons/PrimaryButton.vue'
import SecondaryButton from '@/Components/UI/Buttons/SecondaryButton.vue'

import {
  UserIcon,
  EnvelopeIcon,
  PhoneIcon,
  BriefcaseIcon,
  UserGroupIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  show: Boolean,
  contact: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['close', 'saved'])

const isEditing = computed(() => !!props.contact)

const form = useForm({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  position: '',
})

watch(
  () => [props.contact, props.show],
  ([newContact, isVisible]) => {
    if (!isVisible) return

    form.clearErrors()

    if (newContact) {
      form.first_name = newContact.first_name ?? ''
      form.last_name = newContact.last_name ?? ''
      form.email = newContact.email ?? ''
      form.phone = newContact.phone ?? ''
      form.position = newContact.position ?? ''
    } else {
      form.reset()
    }
  },
  { immediate: true }
)

function submit() {
  if (isEditing.value) {
    form.put(route('contacts.update', props.contact.id), {
      preserveScroll: true,
      onSuccess: () => {
        emit('saved')
        emit('close')
      },
    })
  } else {
    form.post(route('contacts.store'), {
      preserveScroll: true,
      onSuccess: () => {
        emit('saved')
        emit('close')
      },
    })
  }
}
</script>

<template>
  <Modal :show="show" max-width="xl" @close="emit('close')">
    <div class="p-6">
      <div class="flex items-center gap-3">
        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#0047BA]/10 text-[#0047BA] dark:bg-[#3B82F6]/10 dark:text-[#3B82F6]">
          <UserGroupIcon class="h-5 w-5" />
        </div>
        <div>
          <h3 class="text-lg font-bold text-gray-900 dark:text-white">
            {{ isEditing ? 'Editar contacto' : 'Nuevo contacto' }}
          </h3>
          <p class="text-xs text-gray-500 dark:text-gray-400">
            {{ isEditing ? 'Actualiza los datos personales y cargo del contacto.' : 'Registra la información de un contacto general.' }}
          </p>
        </div>
      </div>

      <form class="mt-6 space-y-4" @submit.prevent="submit">
        <!-- Fila 1: Nombre y Apellido -->
        <div class="grid gap-4 sm:grid-cols-2">
          <div>
            <label class="mb-2 flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
              <UserIcon class="h-3.5 w-3.5" />
              <span>Nombre</span>
              <span class="text-[#007AFF]">*</span>
            </label>
            <input
              v-model="form.first_name"
              type="text"
              required
              placeholder="ej. Pablo"
              class="w-full rounded-2xl border border-black/10 bg-black/5 px-4 py-3 text-sm font-medium text-gray-900 outline-none transition focus:border-[#007AFF] focus:bg-white dark:border-white/10 dark:bg-[#222226]/80 dark:text-white dark:focus:border-[#007AFF] dark:focus:bg-[#28282d]"
            />
            <p v-if="form.errors.first_name" class="mt-1.5 text-xs text-red-500 dark:text-red-400">{{ form.errors.first_name }}</p>
          </div>

          <div>
            <label class="mb-2 flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
              <UserIcon class="h-3.5 w-3.5" />
              <span>Apellido</span>
            </label>
            <input
              v-model="form.last_name"
              type="text"
              placeholder="ej. Quiroz"
              class="w-full rounded-2xl border border-black/10 bg-black/5 px-4 py-3 text-sm font-medium text-gray-900 outline-none transition focus:border-[#007AFF] focus:bg-white dark:border-white/10 dark:bg-[#222226]/80 dark:text-white dark:focus:border-[#007AFF] dark:focus:bg-[#28282d]"
            />
            <p v-if="form.errors.last_name" class="mt-1.5 text-xs text-red-500 dark:text-red-400">{{ form.errors.last_name }}</p>
          </div>
        </div>

        <!-- Fila 2: Correo y Teléfono -->
        <div class="grid gap-4 sm:grid-cols-2">
          <div>
            <label class="mb-2 flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
              <EnvelopeIcon class="h-3.5 w-3.5" />
              <span>Correo electrónico</span>
            </label>
            <input
              v-model="form.email"
              type="email"
              placeholder="pablo@quiroz.me"
              class="w-full rounded-2xl border border-black/10 bg-black/5 px-4 py-3 text-sm font-medium text-gray-900 outline-none transition focus:border-[#007AFF] focus:bg-white dark:border-white/10 dark:bg-[#222226]/80 dark:text-white dark:focus:border-[#007AFF] dark:focus:bg-[#28282d]"
            />
            <p v-if="form.errors.email" class="mt-1.5 text-xs text-red-500 dark:text-red-400">{{ form.errors.email }}</p>
          </div>

          <div>
            <label class="mb-2 flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
              <PhoneIcon class="h-3.5 w-3.5" />
              <span>Teléfono</span>
            </label>
            <input
              v-model="form.phone"
              type="text"
              placeholder="+59177072256"
              class="w-full rounded-2xl border border-black/10 bg-black/5 px-4 py-3 text-sm font-medium text-gray-900 outline-none transition focus:border-[#007AFF] focus:bg-white dark:border-white/10 dark:bg-[#222226]/80 dark:text-white dark:focus:border-[#007AFF] dark:focus:bg-[#28282d]"
            />
            <p v-if="form.errors.phone" class="mt-1.5 text-xs text-red-500 dark:text-red-400">{{ form.errors.phone }}</p>
          </div>
        </div>

        <!-- Fila 3: Cargo -->
        <div>
          <label class="mb-2 flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
            <BriefcaseIcon class="h-3.5 w-3.5" />
            <span>Cargo general / Ocupación</span>
          </label>
          <input
            v-model="form.position"
            type="text"
            placeholder="Ej. Consultor Independiente, Ingeniero, etc."
            class="w-full rounded-2xl border border-black/10 bg-black/5 px-4 py-3 text-sm font-medium text-gray-900 outline-none transition focus:border-[#007AFF] focus:bg-white dark:border-white/10 dark:bg-[#222226]/80 dark:text-white dark:focus:border-[#007AFF] dark:focus:bg-[#28282d]"
          />
          <p v-if="form.errors.position" class="mt-1.5 text-xs text-red-500 dark:text-red-400">{{ form.errors.position }}</p>
        </div>
        
        <!-- Acciones -->
        <div class="pt-4 flex items-center justify-end gap-3 border-t border-black/5 dark:border-white/10">
          <SecondaryButton type="button" @click="emit('close')">
            Cancelar
          </SecondaryButton>
          <PrimaryButton type="submit" :disabled="form.processing">
            {{ isEditing ? 'Guardar cambios' : 'Guardar contacto' }}
          </PrimaryButton>
        </div>
      </form>
    </div>
  </Modal>
</template>