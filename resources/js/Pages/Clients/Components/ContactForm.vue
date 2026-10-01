<!-- resources/js/Pages/Clients/Components/ContactForm.vue -->
<script setup>
import { computed, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'

import {
  UserIcon,
  EnvelopeIcon,
  PhoneIcon,
  BriefcaseIcon,
  StarIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  client: {
    type: Object,
    default: null,
  },
  contact: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['close', 'saved'])

const isEditing = computed(() => !!props.contact)

const form = useForm({
  client_id: null,
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  position: '',
  type: 'secondary',
})

watch(
  () => [props.contact, props.client],
  ([newContact, currentClient]) => {
    form.clearErrors()
    
    // Forzar el ID del cliente actual para enviar en la actualización
    form.client_id = currentClient?.id ?? props.client?.id ?? null

    if (newContact) {
      form.first_name = newContact.first_name ?? ''
      form.last_name = newContact.last_name ?? ''
      form.email = newContact.email ?? ''
      form.phone = newContact.phone ?? ''
      // Carga el cargo de la tabla pivote si existe, de lo contrario el base
      form.position = newContact.pivot?.position ?? newContact.position ?? ''
      form.type = newContact.pivot?.is_primary ? 'primary' : 'secondary'
    } else {
      form.reset()
      form.client_id = currentClient?.id ?? props.client?.id ?? null
      form.type = 'secondary'
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

defineExpose({ submit })
</script>

<template>
  <form id="client-contact-form" class="space-y-4" @submit.prevent="submit">
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

    <!-- Fila 3: Cargo en el Cliente y Tipo -->
    <div class="grid gap-4 sm:grid-cols-2">
      <div>
        <label class="mb-2 flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
          <BriefcaseIcon class="h-3.5 w-3.5" />
          <span>Cargo en este cliente</span>
        </label>
        <input
          v-model="form.position"
          type="text"
          placeholder="Ej. Asesor IT, Gerente..."
          class="w-full rounded-2xl border border-black/10 bg-black/5 px-4 py-3 text-sm font-medium text-gray-900 outline-none transition focus:border-[#007AFF] focus:bg-white dark:border-white/10 dark:bg-[#222226]/80 dark:text-white dark:focus:border-[#007AFF] dark:focus:bg-[#28282d]"
        />
        <p v-if="form.errors.position" class="mt-1.5 text-xs text-red-500 dark:text-red-400">{{ form.errors.position }}</p>
      </div>

      <div>
        <label class="mb-2 flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
          <StarIcon class="h-3.5 w-3.5" />
          <span>Tipo de contacto</span>
        </label>
        <select
          v-model="form.type"
          class="w-full rounded-2xl border border-black/10 bg-black/5 px-4 py-3 text-sm font-medium text-gray-900 outline-none transition focus:border-[#007AFF] focus:bg-white dark:border-white/10 dark:bg-[#222226]/80 dark:text-white dark:focus:border-[#007AFF] dark:focus:bg-[#28282d]"
        >
          <option value="secondary">Contacto Secundario</option>
          <option value="primary">Contacto Principal</option>
        </select>
      </div>
    </div>
  </form>
</template>