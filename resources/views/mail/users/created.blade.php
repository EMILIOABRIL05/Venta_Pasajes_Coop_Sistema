<x-mail::message>
# ¡Bienvenido a Cooperativa Ambato!

Hola **{{ $user->name }}**,

Te damos la bienvenida al sistema de gestión y venta de pasajes de la **Cooperativa de Transportes Ambato**. Tu cuenta ha sido configurada exitosamente.

A continuación, tus datos de acceso:
- **Usuario/Email:** {{ $user->email }}
- **Tipo de Perfil:** {{ $user->tipo_usuario ?? 'Estándar' }}

<x-mail::button :url="config('app.url') . '/login'" color="primary">
Iniciar Sesión
</x-mail::button>

<x-mail::panel>
Por favor, asegúrate de cambiar tu contraseña temporal después de tu primer inicio de sesión para mantener tu cuenta segura.
</x-mail::panel>

Si tienes algún inconveniente, contacta a soporte técnico.

Gracias por formar parte de nuestro equipo,<br>
**{{ config('app.name') }}**
</x-mail::message>
