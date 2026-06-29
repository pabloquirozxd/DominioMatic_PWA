<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
  contacts: {
    type: Array,
    default: () => [],
  },
});

const search = ref('');
const showCreateModal = ref(false);
const showEditModal = ref(false);
const editingContact = ref(null);

const form = useForm({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  type: 'secondary',
  position: '',
});

const deleteForm = useForm({});

const filteredContacts = computed(() => {
  const query = search.value.trim().toLowerCase();
  if (!query) return props.contacts;

  return props.contacts.filter((contact) => {
    return [
      contact.first_name,
      contact.last_name,
      contact.email,
      contact.phone,
      contact.position,
      contact.type,
    ]
      .filter(Boolean)
      .some((value) => String(value).toLowerCase().includes(query));
  });
});

const stats = computed(() => {
  const total = props.contacts.length;
  const primary = props.contacts.filter((c) => c.type === 'primary').length;
  const secondary = total - primary;

  return { total, primary, secondary };
});

function resetForm() {
  form.reset();
  form.clearErrors();

  form.first_name = '';
  form.last_name = '';
  form.email = '';
  form.phone = '';
  form.type = 'secondary';
  form.position = '';
}

function openCreateModal() {
  resetForm();
  showCreateModal.value = true;
}

function closeCreateModal() {
  showCreateModal.value = false;
  resetForm();
}

function openEditModal(contact) {
  editingContact.value = contact;

  form.first_name = contact.first_name ?? '';
  form.last_name = contact.last_name ?? '';
  form.email = contact.email ?? '';
  form.phone = contact.phone ?? '';
  form.type = contact.type ?? 'secondary';
  form.position = contact.position ?? '';

  form.clearErrors();
  showEditModal.value = true;
}

function closeEditModal() {
  showEditModal.value = false;
  editingContact.value = null;
  resetForm();
}

function submit() {
  form.post(route('contacts.store'), {
    preserveScroll: true,
    onSuccess: () => {
      closeCreateModal();
    },
  });
}

function updateContact() {
  if (!editingContact.value) return;

  form.put(route('contacts.update', editingContact.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      closeEditModal();
    },
  });
}

function deleteContact(contact) {
  const fullName = `${contact.first_name ?? ''} ${contact.last_name ?? ''}`.trim();

  if (!confirm(`¿Seguro que deseas eliminar el contacto "${fullName || 'sin nombre'}"?`)) {
    return;
  }

  deleteForm.delete(route('contacts.destroy', contact.id), {
    preserveScroll: true,
  });
}
</script>

<template>
  <Head title="Contactos | DominioMatic" />

  <AuthenticatedLayout>
    <template #header>
      <div class="ty-page-header">
        <div class="ty-page-heading">
          <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
            Directorio inteligente de contactos
          </h2>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Gestiona clientes, responsables y contactos clave dentro de tu empresa.
          </p>
        </div>

        <div class="ty-page-actions">
          <Link
            :href="route('dashboard')"
            class="ty-action-btn ty-action-dark"
          >
            Volver al dashboard
          </Link>

          <button
            type="button"
            @click="openCreateModal"
            class="ty-action-btn ty-action-light"
          >
            Nuevo contacto
          </button>

          <a
            :href="route('reports.contacts.pdf')"
            target="_blank"
            class="ty-report-btn ty-report-pdf"
          >
            Exportar PDF
          </a>

          <a
            :href="route('reports.contacts.excel')"
            class="ty-report-btn ty-report-excel"
          >
            Exportar Excel
          </a>
        </div>
      </div>
    </template>

    <div class="py-10 text-gray-900 transition-colors duration-300 dark:text-gray-100">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        <div class="grid gap-4 md:grid-cols-3">
          <div class="glass-card p-6">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total contactos</p>
            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ stats.total }}</p>
          </div>

          <div class="glass-card p-6">
            <p class="text-sm text-gray-500 dark:text-gray-400">Contactos principales</p>
            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ stats.primary }}</p>
          </div>

          <div class="glass-card p-6">
            <p class="text-sm text-gray-500 dark:text-gray-400">Contactos secundarios</p>
            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ stats.secondary }}</p>
          </div>
        </div>

        <div class="mt-8 glass-card p-6">
          <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                Lista de contactos
              </h3>
              <p class="text-sm text-gray-500 dark:text-gray-400">
                Busca, revisa y administra los registros del directorio.
              </p>
            </div>

            <div class="w-full md:w-80">
              <input
                v-model="search"
                type="text"
                placeholder="Buscar por nombre, correo o cargo..."
                class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-[#0066cc] dark:border-white/10 dark:bg-[#1c1c1f] dark:text-gray-100 dark:placeholder:text-gray-500"
              />
            </div>
          </div>

          <div class="mt-6 overflow-hidden rounded-2xl border border-gray-100 dark:border-white/10">
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-100 dark:divide-white/10">
                <thead class="bg-gray-50 dark:bg-[#1c1c1f]">
                  <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                      Nombre
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                      Correo
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                      Teléfono
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                      Tipo
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                      Cargo
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                      Acciones
                    </th>
                  </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 dark:divide-white/10 dark:bg-transparent">
                  <tr
                    v-for="contact in filteredContacts"
                    :key="contact.id"
                    class="hover:bg-gray-50/80 dark:hover:bg-[#1c1c1f]"
                  >
                    <td class="px-4 py-4 text-sm font-medium text-gray-900 dark:text-white">
                      {{ contact.first_name }} {{ contact.last_name }}
                    </td>

                    <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300">
                      {{ contact.email || '—' }}
                    </td>

                    <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300">
                      {{ contact.phone || '—' }}
                    </td>

                    <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300 capitalize">
                      {{ contact.type }}
                    </td>

                    <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300">
                      {{ contact.position || '—' }}
                    </td>

                    <td class="px-4 py-4 text-right">
                      <div class="ty-row-actions">
                        <button
                          type="button"
                          @click="openEditModal(contact)"
                          class="ty-table-action-btn ty-table-edit"
                        >
                          Editar
                        </button>

                        <button
                          type="button"
                          @click="deleteContact(contact)"
                          class="ty-table-action-btn ty-table-delete"
                        >
                          Eliminar
                        </button>
                      </div>
                    </td>
                  </tr>

                  <tr v-if="filteredContacts.length === 0">
                    <td
                      colspan="6"
                      class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400"
                    >
                      No se encontraron contactos.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal crear -->
    <div
      v-if="showCreateModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
    >
      <div class="glass-card w-full max-w-2xl p-6 shadow-2xl">
        <div class="flex items-start justify-between gap-4">
          <div>
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
              Nuevo contacto
            </h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              Agrega un registro al directorio inteligente.
            </p>
          </div>

          <button
            type="button"
            class="text-gray-400 transition hover:text-gray-700 dark:hover:text-gray-200"
            @click="closeCreateModal"
          >
            ✕
          </button>
        </div>

        <form class="mt-6 grid gap-4 md:grid-cols-2" @submit.prevent="submit">
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
              Nombre
            </label>
            <input
              v-model="form.first_name"
              type="text"
              class="w-full rounded-2xl border border-gray-200 px-4 py-3 outline-none transition focus:border-[#0066cc] dark:border-white/10 dark:bg-[#1c1c1f] dark:text-white"
            />
            <p v-if="form.errors.first_name" class="mt-1 text-sm text-red-500">
              {{ form.errors.first_name }}
            </p>
          </div>

          <div>
            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
              Apellido
            </label>
            <input
              v-model="form.last_name"
              type="text"
              class="w-full rounded-2xl border border-gray-200 px-4 py-3 outline-none transition focus:border-[#0066cc] dark:border-white/10 dark:bg-[#1c1c1f] dark:text-white"
            />
            <p v-if="form.errors.last_name" class="mt-1 text-sm text-red-500">
              {{ form.errors.last_name }}
            </p>
          </div>

          <div>
            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
              Correo
            </label>
            <input
              v-model="form.email"
              type="email"
              class="w-full rounded-2xl border border-gray-200 px-4 py-3 outline-none transition focus:border-[#0066cc] dark:border-white/10 dark:bg-[#1c1c1f] dark:text-white"
            />
            <p v-if="form.errors.email" class="mt-1 text-sm text-red-500">
              {{ form.errors.email }}
            </p>
          </div>

          <div>
            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
              Teléfono
            </label>
            <input
              v-model="form.phone"
              type="text"
              class="w-full rounded-2xl border border-gray-200 px-4 py-3 outline-none transition focus:border-[#0066cc] dark:border-white/10 dark:bg-[#1c1c1f] dark:text-white"
            />
            <p v-if="form.errors.phone" class="mt-1 text-sm text-red-500">
              {{ form.errors.phone }}
            </p>
          </div>

          <div>
            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
              Tipo
            </label>
            <select
              v-model="form.type"
              class="w-full rounded-2xl border border-gray-200 px-4 py-3 outline-none transition focus:border-[#0066cc] dark:border-white/10 dark:bg-[#1c1c1f] dark:text-white"
            >
              <option value="primary">Principal</option>
              <option value="secondary">Secundario</option>
            </select>
            <p v-if="form.errors.type" class="mt-1 text-sm text-red-500">
              {{ form.errors.type }}
            </p>
          </div>

          <div>
            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
              Cargo
            </label>
            <input
              v-model="form.position"
              type="text"
              class="w-full rounded-2xl border border-gray-200 px-4 py-3 outline-none transition focus:border-[#0066cc] dark:border-white/10 dark:bg-[#1c1c1f] dark:text-white"
            />
            <p v-if="form.errors.position" class="mt-1 text-sm text-red-500">
              {{ form.errors.position }}
            </p>
          </div>

          <div class="md:col-span-2 mt-2 flex justify-end gap-3">
            <button
              type="button"
              class="rounded-2xl border border-gray-200 px-5 py-3 text-sm font-semibold text-gray-700 dark:border-white/10 dark:text-gray-200"
              @click="closeCreateModal"
            >
              Cancelar
            </button>

            <button
              type="submit"
              class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white dark:bg-white dark:text-black"
              :disabled="form.processing"
            >
              Guardar contacto
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal editar -->
    <div
      v-if="showEditModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
    >
      <div class="glass-card w-full max-w-2xl p-6 shadow-2xl">
        <div class="flex items-start justify-between gap-4">
          <div>
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
              Editar contacto
            </h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              Actualiza la información del registro seleccionado.
            </p>
          </div>

          <button
            type="button"
            class="text-gray-400 transition hover:text-gray-700 dark:hover:text-gray-200"
            @click="closeEditModal"
          >
            ✕
          </button>
        </div>

        <form class="mt-6 grid gap-4 md:grid-cols-2" @submit.prevent="updateContact">
          <div>
            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
              Nombre
            </label>
            <input
              v-model="form.first_name"
              type="text"
              class="w-full rounded-2xl border border-gray-200 px-4 py-3 outline-none transition focus:border-[#0066cc] dark:border-white/10 dark:bg-[#1c1c1f] dark:text-white"
            />
            <p v-if="form.errors.first_name" class="mt-1 text-sm text-red-500">
              {{ form.errors.first_name }}
            </p>
          </div>

          <div>
            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
              Apellido
            </label>
            <input
              v-model="form.last_name"
              type="text"
              class="w-full rounded-2xl border border-gray-200 px-4 py-3 outline-none transition focus:border-[#0066cc] dark:border-white/10 dark:bg-[#1c1c1f] dark:text-white"
            />
            <p v-if="form.errors.last_name" class="mt-1 text-sm text-red-500">
              {{ form.errors.last_name }}
            </p>
          </div>

          <div>
            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
              Correo
            </label>
            <input
              v-model="form.email"
              type="email"
              class="w-full rounded-2xl border border-gray-200 px-4 py-3 outline-none transition focus:border-[#0066cc] dark:border-white/10 dark:bg-[#1c1c1f] dark:text-white"
            />
            <p v-if="form.errors.email" class="mt-1 text-sm text-red-500">
              {{ form.errors.email }}
            </p>
          </div>

          <div>
            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
              Teléfono
            </label>
            <input
              v-model="form.phone"
              type="text"
              class="w-full rounded-2xl border border-gray-200 px-4 py-3 outline-none transition focus:border-[#0066cc] dark:border-white/10 dark:bg-[#1c1c1f] dark:text-white"
            />
            <p v-if="form.errors.phone" class="mt-1 text-sm text-red-500">
              {{ form.errors.phone }}
            </p>
          </div>

          <div>
            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
              Tipo
            </label>
            <select
              v-model="form.type"
              class="w-full rounded-2xl border border-gray-200 px-4 py-3 outline-none transition focus:border-[#0066cc] dark:border-white/10 dark:bg-[#1c1c1f] dark:text-white"
            >
              <option value="primary">Principal</option>
              <option value="secondary">Secundario</option>
            </select>
            <p v-if="form.errors.type" class="mt-1 text-sm text-red-500">
              {{ form.errors.type }}
            </p>
          </div>

          <div>
            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
              Cargo
            </label>
            <input
              v-model="form.position"
              type="text"
              class="w-full rounded-2xl border border-gray-200 px-4 py-3 outline-none transition focus:border-[#0066cc] dark:border-white/10 dark:bg-[#1c1c1f] dark:text-white"
            />
            <p v-if="form.errors.position" class="mt-1 text-sm text-red-500">
              {{ form.errors.position }}
            </p>
          </div>

          <div class="md:col-span-2 mt-2 flex justify-end gap-3">
            <button
              type="button"
              class="rounded-2xl border border-gray-200 px-5 py-3 text-sm font-semibold text-gray-700 dark:border-white/10 dark:text-gray-200"
              @click="closeEditModal"
            >
              Cancelar
            </button>

            <button
              type="submit"
              class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white dark:bg-white dark:text-black"
              :disabled="form.processing"
            >
              Guardar cambios
            </button>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>