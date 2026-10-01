<x-mail::message>
# Nueva Solicitud de Acceso

Un nuevo usuario ha solicitado unirse a **{{ $companyName }}**.

**Detalles del solicitante:**
* **Nombre:** {{ $name }}
* **Correo:** {{ $email }}
@if($message)
* **Mensaje:** "{{ $message }}"
@endif

Puedes revisar, aprobar o rechazar esta solicitud directamente desde el panel de administración:

<x-mail::button :url="$adminUrl">
Ir al Panel de Administración
</x-mail::button>

Gracias,<br>
El equipo de {{ config('app.name') }}
</x-mail::message>