<x-mail::message>
# ¡Solicitud Aprobada!

Hola **{{ $name }}**,

Nos complace informarte que tu solicitud para unirte a **{{ $companyName }}** ha sido aprobada por el equipo administrador.

Para completar tu registro y acceder a la plataforma, haz clic en el siguiente botón:

<x-mail::button :url="$registerUrl">
Completar Registro
</x-mail::button>

Si no realizaste esta solicitud, puedes ignorar este correo de manera segura.

Saludos,<br>
El equipo de {{ $companyName }}
</x-mail::message>