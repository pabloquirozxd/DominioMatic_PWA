<script setup>
import { ref, watch, nextTick } from 'vue'
import { useForm } from '@inertiajs/vue3'

import Modal from '@/Components/UI/Modal/Modal.vue'
import CloseButton from '@/Components/UI/CloseButton.vue'

import {
    BuildingOffice2Icon,
    GlobeAltIcon,
    LanguageIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({

    show:Boolean,

    client:{
        type:Object,
        default:null
    }

})

const emit = defineEmits([
    'close'
])

const firstInput = ref()

const form = useForm({

    type:'company',

    company_name:'',

    language:'Español',

    website:'',

    portal_enabled:false

})

watch(()=>props.show,async(value)=>{

    if(!value) return

    if(props.client){

        form.type=props.client.type

        form.company_name=props.client.company_name

        form.language=props.client.language

        form.website=props.client.website

        form.portal_enabled=props.client.portal_enabled

    }

    else{

        form.reset()

    }

    await nextTick()

    firstInput.value.focus()

})

function submit(){

    if(props.client){

        form.put(route('clients.update',props.client.id),{

            preserveScroll:true,

            onSuccess:()=>emit('close')

        })

    }

    else{

        form.post(route('clients.store'),{

            preserveScroll:true,

            onSuccess:()=>emit('close')

        })

    }

}
</script>

<template>

<Modal
    :show="show"
    max-width="xl"
    @close="emit('close')"
>

<div class="p-8">

<div class="flex items-start justify-between">

<div>

<h2
class="text-2xl font-semibold text-gray-900 dark:text-white"
>

{{ client ? 'Editar cliente' : 'Nuevo cliente' }}

</h2>

<p
class="mt-2 text-sm text-gray-500 dark:text-gray-400"
>

Registra una empresa o un cliente individual.

</p>

</div>

<CloseButton
@click="emit('close')"
/>

</div>

<form
class="mt-8 space-y-6"
@submit.prevent="submit"
>

<!-- Empresa -->

<div>

<label
class="mb-2 flex items-center gap-2 text-sm font-medium"
>

<BuildingOffice2Icon
class="h-5 w-5 text-gray-400"
/>

Empresa

</label>

<input

ref="firstInput"

v-model="form.company_name"

type="text"

placeholder="Nombre de la empresa"

class="w-full rounded-2xl border border-gray-200 bg-white/80 px-5 py-3 outline-none transition focus:border-[#007AFF] dark:border-white/10 dark:bg-[#1b1b1d]"

>

<p
v-if="form.errors.company_name"
class="mt-2 text-sm text-red-500"
>

{{ form.errors.company_name }}

</p>

</div>

<!-- Sitio web -->

<div>

<label
class="mb-2 flex items-center gap-2 text-sm font-medium"
>

<GlobeAltIcon
class="h-5 w-5 text-gray-400"
/>

Sitio web

</label>

<input

v-model="form.website"

type="url"

placeholder="https://"

class="w-full rounded-2xl border border-gray-200 bg-white/80 px-5 py-3 outline-none transition focus:border-[#007AFF] dark:border-white/10 dark:bg-[#1b1b1d]"

>

</div>

<!-- Idioma -->

<div>

<label
class="mb-2 flex items-center gap-2 text-sm font-medium"
>

<LanguageIcon
class="h-5 w-5 text-gray-400"
/>

Idioma

</label>

<select

v-model="form.language"

class="w-full rounded-2xl border border-gray-200 bg-white/80 px-5 py-3 outline-none transition focus:border-[#007AFF] dark:border-white/10 dark:bg-[#1b1b1d]"

>

<option>Español</option>

<option>English</option>

<option>Português</option>

</select>

</div>

<!-- Portal -->

<div
class="flex items-center justify-between rounded-2xl border border-gray-200 p-5 dark:border-white/10"
>

<div>

<h3
class="font-medium"
>

Portal del cliente

</h3>

<p
class="text-sm text-gray-500"
>

Permite que el cliente acceda a su panel.

</p>

</div>

<button

type="button"

@click="form.portal_enabled=!form.portal_enabled"

:class="[

'w-14 rounded-full transition h-8 relative',

form.portal_enabled

?'bg-[#007AFF]'

:'bg-gray-300'

]"

>

<div

:class="[

'absolute top-1 h-6 w-6 rounded-full bg-white transition',

form.portal_enabled

?'left-7'

:'left-1'

]"

></div>

</button>

</div>

<div
class="flex justify-end gap-3 pt-4"
>

<button

type="button"

@click="emit('close')"

class="rounded-2xl border border-gray-200 px-6 py-3"

>

Cancelar

</button>

<button

type="submit"

:disabled="form.processing"

class="rounded-2xl bg-[#007AFF] px-6 py-3 font-semibold text-white"

>

{{ client ? 'Guardar cambios' : 'Crear cliente' }}

</button>

</div>

</form>

</div>

</Modal>

</template>